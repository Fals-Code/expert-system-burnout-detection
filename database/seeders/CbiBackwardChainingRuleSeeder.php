<?php

namespace Database\Seeders;

use App\Models\CbiItem;
use App\Models\ExpertPremise;
use App\Models\ExpertRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CbiBackwardChainingRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            ExpertPremise::query()->delete();
            ExpertRule::query()->delete();

            $this->createFactRule(
                code: 'R-PERSONAL-4-OF-6',
                goal: 'BURNOUT_PERSONAL',
                operator: ExpertRule::OPERATOR_K_OF_N,
                requiredCount: 4,
                itemCodes: $this->codes('PB', 6),
                description: 'Minimal empat dari enam indikator personal bernilai benar.'
            );

            $this->createFactRule(
                code: 'R-WORK-5-OF-7',
                goal: 'BURNOUT_KERJA',
                operator: ExpertRule::OPERATOR_K_OF_N,
                requiredCount: 5,
                itemCodes: $this->codes('WB', 7),
                expectedOverrides: [
                    'CBI-WB-07' => false,
                ],
                description: 'Minimal lima dari tujuh indikator terkait pekerjaan terpenuhi. Item WB-07 adalah item positif sehingga premis mengharapkan nilai false.'
            );

            $this->createFactRule(
                code: 'R-CLIENT-4-OF-6',
                goal: 'BURNOUT_CLIENT',
                operator: ExpertRule::OPERATOR_K_OF_N,
                requiredCount: 4,
                itemCodes: $this->codes('CB', 6),
                description: 'Minimal empat dari enam indikator terkait penerima layanan bernilai benar.'
            );

            $this->createGoalRule(
                code: 'R-CHRONIC-ALL',
                goal: 'BURNOUT_KERJA_KRONIS',
                operator: ExpertRule::OPERATOR_ALL,
                requiredCount: 2,
                premises: [
                    ['BURNOUT_PERSONAL', true],
                    ['BURNOUT_KERJA', true],
                ],
                description: 'Pola kronis dibuktikan apabila burnout personal dan burnout terkait pekerjaan sama-sama terbukti.'
            );

            $this->createGoalRule(
                code: 'R-STABLE-ALL-NEGATIVE',
                goal: 'KONDISI_STABIL',
                operator: ExpertRule::OPERATOR_ALL,
                requiredCount: 3,
                premises: [
                    ['BURNOUT_PERSONAL', false],
                    ['BURNOUT_KERJA', false],
                    ['BURNOUT_CLIENT', false],
                ],
                description: 'Kondisi relatif stabil dibuktikan apabila ketiga goal burnout tidak terbukti.'
            );
        });
    }

    private function createFactRule(
        string $code,
        string $goal,
        string $operator,
        int $requiredCount,
        array $itemCodes,
        array $expectedOverrides = [],
        ?string $description = null
    ): void {
        $rule = ExpertRule::query()->create([
            'code' => $code,
            'goal' => $goal,
            'operator' => $operator,
            'required_count' => $requiredCount,
            'priority' => 10,
            'description' => $description,
            'is_active' => true,
        ]);

        foreach ($itemCodes as $index => $itemCode) {
            $item = CbiItem::query()->where('code', $itemCode)->first();

            if (! $item) {
                throw new RuntimeException(
                    "CBI item {$itemCode} must exist before rule seeding."
                );
            }

            $rule->premises()->create([
                'premise_type' => ExpertPremise::TYPE_FACT,
                'premise_key' => $itemCode,
                'cbi_item_id' => $item->id,
                'expected_boolean' => $expectedOverrides[$itemCode] ?? true,
                'sequence' => $index + 1,
                'label' => $item->prompt_text,
            ]);
        }
    }

    private function createGoalRule(
        string $code,
        string $goal,
        string $operator,
        int $requiredCount,
        array $premises,
        ?string $description = null
    ): void {
        $rule = ExpertRule::query()->create([
            'code' => $code,
            'goal' => $goal,
            'operator' => $operator,
            'required_count' => $requiredCount,
            'priority' => 10,
            'description' => $description,
            'is_active' => true,
        ]);

        foreach ($premises as $index => [$premiseGoal, $expected]) {
            $rule->premises()->create([
                'premise_type' => ExpertPremise::TYPE_GOAL,
                'premise_key' => $premiseGoal,
                'cbi_item_id' => null,
                'expected_boolean' => $expected,
                'sequence' => $index + 1,
                'label' => config(
                    "expert_system.goal_labels.{$premiseGoal}",
                    $premiseGoal
                ),
            ]);
        }
    }

    private function codes(string $dimension, int $count): array
    {
        return collect(range(1, $count))
            ->map(fn (int $index): string => sprintf(
                'CBI-%s-%02d',
                $dimension,
                $index
            ))
            ->all();
    }
}
