<?php

namespace App\Services;

use App\Models\CbiAssessment;
use App\Models\CbiItem;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CbiScoringService
{
    public function score(array $responses, ?Collection $items = null): array
    {
        $items ??= CbiItem::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $expectedDimensions = config('cbi.instrument.dimensions', [
            CbiItem::DIMENSION_PERSONAL => 6,
            CbiItem::DIMENSION_WORK => 7,
            CbiItem::DIMENSION_CLIENT => 6,
        ]);

        $expectedResponseCount = (int) config('cbi.instrument.expected_item_count', 19);
        $normalizedAnswers = $this->normalizeAnswers($responses);
        $dimensions = [];
        $normalizedResponses = [];

        foreach ($expectedDimensions as $dimension => $expectedCount) {
            $dimensionItems = $items
                ->where('dimension', $dimension)
                ->sortBy('position')
                ->values();

            $result = $this->scoreDimension(
                (string) $dimension,
                (int) $expectedCount,
                $dimensionItems,
                $normalizedAnswers
            );

            $dimensions[$dimension] = $result['dimension'];
            $normalizedResponses = array_merge(
                $normalizedResponses,
                $result['normalized_responses']
            );
        }

        $responsesCount = (int) collect($dimensions)->sum('answered_count');
        $instrumentReady = $items->count() === $expectedResponseCount
            && collect($dimensions)->every(
                fn (array $dimension): bool =>
                    $dimension['configured_count'] === $dimension['expected_count']
            );

        $isComplete = $instrumentReady
            && collect($dimensions)->every(
                fn (array $dimension): bool =>
                    $dimension['status'] === CbiAssessment::STATUS_COMPLETE
            );

        return [
            'status' => $isComplete
                ? CbiAssessment::STATUS_COMPLETE
                : CbiAssessment::STATUS_INSUFFICIENT_DATA,
            'responses_count' => $responsesCount,
            'expected_responses_count' => $expectedResponseCount,
            'instrument_ready' => $instrumentReady,
            'dimensions' => $dimensions,
            'normalized_responses' => $normalizedResponses,
        ];
    }

    private function normalizeAnswers(array $responses): array
    {
        $validOptions = array_keys(config('cbi.response_options', []));

        if ($validOptions === []) {
            throw new InvalidArgumentException('CBI response options are not configured.');
        }

        $normalized = [];

        foreach ($responses as $code => $answerKey) {
            $answerKey = strtoupper(trim((string) $answerKey));

            if (! in_array($answerKey, $validOptions, true)) {
                throw new InvalidArgumentException(
                    "Response for {$code} is not a valid CBI answer option."
                );
            }

            $normalized[(string) $code] = $answerKey;
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
        $normalizedResponses = [];

        foreach ($items as $item) {
            if (! array_key_exists($item->code, $responses)) {
                $missingCodes[] = $item->code;
                continue;
            }

            $answerKey = $responses[$item->code];
            $rawScore = (int) data_get(
                config('cbi.response_options'),
                "{$answerKey}.score"
            );
            $normalizedScore = $item->is_reverse
                ? 100 - $rawScore
                : $rawScore;

            $scores[] = $normalizedScore;
            $normalizedResponses[$item->code] = [
                'item_id' => $item->id,
                'answer_key' => $answerKey,
                'raw_score' => $rawScore,
                'normalized_score' => $normalizedScore,
            ];
        }

        $answeredCount = count($scores);
        $isComplete = $items->count() === $expectedCount
            && $answeredCount === $expectedCount
            && $missingCodes === [];

        return [
            'dimension' => [
                'code' => $dimension,
                'status' => $isComplete
                    ? CbiAssessment::STATUS_COMPLETE
                    : CbiAssessment::STATUS_INSUFFICIENT_DATA,
                'expected_count' => $expectedCount,
                'configured_count' => $items->count(),
                'answered_count' => $answeredCount,
                'missing_codes' => $missingCodes,
                'total' => $isComplete ? array_sum($scores) : null,
                'mean' => $isComplete
                    ? round(array_sum($scores) / $expectedCount, 2)
                    : null,
            ],
            'normalized_responses' => $normalizedResponses,
        ];
    }
}
