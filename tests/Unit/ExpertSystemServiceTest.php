<?php

namespace Tests\Unit;

use App\Services\CertaintyFactorCalculator;
use App\Services\ExpertSystemService;
use Tests\TestCase;

class ExpertSystemServiceTest extends TestCase
{
    public function test_pdf_answer_mapping_is_canonical(): void
    {
        $service = new ExpertSystemService;

        $this->assertSame(1.0, $service->getCfUser('Sering'));
        $this->assertSame(0.6, $service->getCfUser('Kadang'));
        $this->assertSame(0.0, $service->getCfUser('Tidak Pernah'));
        $this->assertSame(0.0, $service->getCfUser('Ya'));
        $this->assertSame(0.0, $service->getCfUser('Sangat Sering'));
    }

    public function test_rule_cf_uses_average_premise_times_expert_cf(): void
    {
        $calculator = new CertaintyFactorCalculator;

        $premises = [
            $calculator->premiseCf('Sering', 0.85),
            $calculator->premiseCf('Kadang', 0.92),
            $calculator->premiseCf('Tidak Pernah', 0.80),
        ];

        $expectedAverage = ((1.0 * 0.85) + (0.6 * 0.92) + 0.0) / 3;
        $this->assertEqualsWithDelta($expectedAverage * 0.95, $calculator->ruleCf($premises, 0.95), 0.0001);
    }

    public function test_rule_cf_is_clamped_to_valid_range(): void
    {
        $calculator = new CertaintyFactorCalculator;

        $this->assertSame(1.0, $calculator->clamp(1.5));
        $this->assertSame(0.0, $calculator->clamp(-0.5));
        $this->assertSame(0.5, $calculator->clamp(0.5));
    }

    public function test_generate_explanation_returns_trace_structure(): void
    {
        $service = new ExpertSystemService;
        $diagnosa = (object) ['nama' => 'Risiko Burnout Tinggi', 'tingkat' => 'TINGGI'];

        $result = $service->generateExplanation([
            'rule_kode' => 'R001',
            'goal_terkonfirmasi' => 'TINGGI',
            'cf_average_premis' => 0.82,
            'cf_pakar_rule' => 0.95,
            'min_threshold' => 0.25,
            'gejala_details' => [
                ['gejala' => 'Kelelahan emosional mendalam', 'kode' => 'G006', 'kategori' => 'emosional', 'user_ans' => 'Sering', 'cf_sub' => 0.92],
            ],
        ], $diagnosa, 0.779);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('reasoning_chain', $result);
        $this->assertArrayHasKey('dominant_symptoms', $result);
        $this->assertSame('Yakin', $result['confidence_label']);
        $this->assertStringContainsString('R001', $result['summary']);
    }
}
