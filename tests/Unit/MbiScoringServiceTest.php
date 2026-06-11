<?php

namespace Tests\Unit;

use App\Models\MbiAssessment;
use App\Models\MbiItem;
use App\Services\MbiScoringService;
use App\Services\SafetyFlagService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MbiScoringServiceTest extends TestCase
{
    public function test_it_calculates_each_dimension_independently(): void
    {
        config()->set('mbi.profile_classification.enabled', false);

        $items = $this->makeItems();
        $responses = [];

        foreach ($items as $item) {
            $responses[$item->code] = match ($item->dimension) {
                'EX' => 4,
                'CY' => [0, 1, 2, 3, 4][$item->position - 6],
                'PE' => 6,
            };
        }

        $result = app(MbiScoringService::class)->score($responses, $items);

        $this->assertSame(MbiAssessment::STATUS_COMPLETE, $result['status']);
        $this->assertSame(20, $result['dimensions']['EX']['total']);
        $this->assertSame(4.0, $result['dimensions']['EX']['mean']);
        $this->assertSame(10, $result['dimensions']['CY']['total']);
        $this->assertSame(2.0, $result['dimensions']['CY']['mean']);
        $this->assertSame(36, $result['dimensions']['PE']['total']);
        $this->assertSame(6.0, $result['dimensions']['PE']['mean']);
        $this->assertSame('CONTINUOUS_PROFILE', $result['profile']['code']);
    }

    public function test_it_marks_only_the_incomplete_dimension_as_insufficient(): void
    {
        $items = $this->makeItems();
        $responses = $items->mapWithKeys(fn (MbiItem $item): array => [$item->code => 3])->all();
        unset($responses['MBIGS-CY-05']);

        $result = app(MbiScoringService::class)->score($responses, $items);

        $this->assertSame(MbiAssessment::STATUS_INSUFFICIENT_DATA, $result['status']);
        $this->assertSame(MbiAssessment::STATUS_COMPLETE, $result['dimensions']['EX']['status']);
        $this->assertSame(MbiAssessment::STATUS_INSUFFICIENT_DATA, $result['dimensions']['CY']['status']);
        $this->assertNull($result['dimensions']['CY']['total']);
        $this->assertContains('MBIGS-CY-05', $result['dimensions']['CY']['missing_codes']);
    }

    public function test_it_classifies_a_profile_only_when_validated_thresholds_are_enabled(): void
    {
        config()->set('mbi.profile_classification', [
            'enabled' => true,
            'thresholds' => ['ex_high' => 3, 'cy_high' => 3, 'pe_low' => 3],
        ]);

        $items = $this->makeItems();
        $responses = $items->mapWithKeys(function (MbiItem $item): array {
            return [$item->code => $item->dimension === 'PE' ? 2 : 4];
        })->all();

        $result = app(MbiScoringService::class)->score($responses, $items);

        $this->assertSame('BURNOUT_PATTERN', $result['profile']['code']);
    }

    public function test_red_flag_is_evaluated_outside_dimension_scores(): void
    {
        config()->set('mbi.red_flag.high_response_threshold', 4);

        $result = app(SafetyFlagService::class)->evaluate(5);

        $this->assertTrue($result['has_red_flag']);
        $this->assertSame(['G14'], $result['codes']);
    }

    private function makeItems(): Collection
    {
        $items = collect();
        $position = 1;

        foreach (['EX' => 5, 'CY' => 5, 'PE' => 6] as $dimension => $count) {
            for ($index = 1; $index <= $count; $index++) {
                $items->push(new MbiItem([
                    'code' => sprintf('MBIGS-%s-%02d', $dimension, $index),
                    'dimension' => $dimension,
                    'position' => $position++,
                    'is_active' => true,
                ]));
            }
        }

        return $items;
    }
}
