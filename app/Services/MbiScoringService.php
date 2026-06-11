<?php

namespace App\Services;

use App\Models\MbiAssessment;
use App\Models\MbiItem;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class MbiScoringService
{
    public function score(array $responses, ?Collection $items = null): array
    {
        $items ??= MbiItem::query()->where('is_active', true)->orderBy('position')->get();
        $expectedDimensions = config('mbi.instrument.dimensions', ['EX' => 5, 'CY' => 5, 'PE' => 6]);
        $expectedResponses = (int) config('mbi.instrument.expected_item_count', 16);
        $normalizedResponses = $this->normalizeResponses($responses);
        $dimensions = [];

        foreach ($expectedDimensions as $dimension => $expectedCount) {
            $dimensionItems = $items->where('dimension', $dimension)->sortBy('position')->values();
            $dimensions[$dimension] = $this->scoreDimension(
                $dimension,
                (int) $expectedCount,
                $dimensionItems,
                $normalizedResponses
            );
        }

        $responsesCount = (int) collect($dimensions)->sum('answered_count');
        $instrumentHasExpectedItems = $items->count() === $expectedResponses
            && collect($dimensions)->every(
                fn (array $dimension): bool => $dimension['configured_count'] === $dimension['expected_count']
            );

        $isComplete = $instrumentHasExpectedItems
            && collect($dimensions)->every(
                fn (array $dimension): bool => $dimension['status'] === MbiAssessment::STATUS_COMPLETE
            );

        $profile = $isComplete
            ? $this->classifyProfile(
                (float) $dimensions['EX']['mean'],
                (float) $dimensions['CY']['mean'],
                (float) $dimensions['PE']['mean']
            )
            : ['code' => 'INSUFFICIENT_DATA', 'basis' => 'One or more dimensions are incomplete.'];

        return [
            'status' => $isComplete ? MbiAssessment::STATUS_COMPLETE : MbiAssessment::STATUS_INSUFFICIENT_DATA,
            'responses_count' => $responsesCount,
            'expected_responses_count' => $expectedResponses,
            'coverage_percent' => $expectedResponses > 0
                ? round(($responsesCount / $expectedResponses) * 100, 2)
                : 0.0,
            'instrument_ready' => $instrumentHasExpectedItems,
            'dimensions' => $dimensions,
            'profile' => $profile,
        ];
    }

    private function normalizeResponses(array $responses): array
    {
        $normalized = [];

        foreach ($responses as $code => $value) {
            if (is_string($value) && ctype_digit($value)) {
                $value = (int) $value;
            }

            if (! is_int($value) || $value < 0 || $value > 6) {
                throw new InvalidArgumentException("Response for {$code} must be an integer between 0 and 6.");
            }

            $normalized[(string) $code] = $value;
        }

        return $normalized;
    }

    private function scoreDimension(
        string $dimension,
        int $expectedCount,
        Collection $items,
        array $responses
    ): array {
        $scores = [];
        $missingCodes = [];

        foreach ($items as $item) {
            if (! array_key_exists($item->code, $responses)) {
                $missingCodes[] = $item->code;
                continue;
            }

            $scores[] = $responses[$item->code];
        }

        $answeredCount = count($scores);
        $isComplete = $items->count() === $expectedCount
            && $answeredCount === $expectedCount
            && $missingCodes === [];

        return [
            'code' => $dimension,
            'status' => $isComplete ? MbiAssessment::STATUS_COMPLETE : MbiAssessment::STATUS_INSUFFICIENT_DATA,
            'expected_count' => $expectedCount,
            'configured_count' => $items->count(),
            'answered_count' => $answeredCount,
            'coverage_percent' => $expectedCount > 0
                ? round(($answeredCount / $expectedCount) * 100, 2)
                : 0.0,
            'missing_codes' => $missingCodes,
            'total' => $isComplete ? array_sum($scores) : null,
            'mean' => $isComplete ? round(array_sum($scores) / $expectedCount, 2) : null,
        ];
    }

    private function classifyProfile(float $exScore, float $cyScore, float $peScore): array
    {
        $classification = config('mbi.profile_classification', []);
        $thresholds = $classification['thresholds'] ?? [];

        if (! ($classification['enabled'] ?? false) || ! $this->hasValidThresholds($thresholds)) {
            return [
                'code' => 'CONTINUOUS_PROFILE',
                'basis' => 'Categorical profiles are disabled; report the three continuous dimension scores.',
            ];
        }

        $exHigh = $exScore >= (float) $thresholds['ex_high'];
        $cyHigh = $cyScore >= (float) $thresholds['cy_high'];
        $peLow = $peScore <= (float) $thresholds['pe_low'];

        $code = match (true) {
            $exHigh && $cyHigh && $peLow => 'BURNOUT_PATTERN',
            $exHigh && ! $cyHigh && ! $peLow => 'OVEREXTENDED',
            ! $exHigh && $cyHigh && ! $peLow => 'DISENGAGED',
            ! $exHigh && ! $cyHigh && $peLow => 'INEFFECTIVE',
            ! $exHigh && ! $cyHigh && ! $peLow => 'ENGAGED_PATTERN',
            default => 'MIXED_PROFILE',
        };

        return [
            'code' => $code,
            'basis' => 'Configured thresholds derived from licensed or locally validated norms.',
        ];
    }

    private function hasValidThresholds(array $thresholds): bool
    {
        foreach (['ex_high', 'cy_high', 'pe_low'] as $key) {
            if (! array_key_exists($key, $thresholds) || ! is_numeric($thresholds[$key])) {
                return false;
            }
        }

        return true;
    }
}
