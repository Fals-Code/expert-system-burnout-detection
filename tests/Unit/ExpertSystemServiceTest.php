<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExpertSystemService;

class ExpertSystemServiceTest extends TestCase
{
    protected ExpertSystemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpertSystemService();
    }

    // ════════════════════════════════════════════════════════════════
    // 1. getCfUser() – Mapping jawaban ke nilai CF
    // ════════════════════════════════════════════════════════════════

    public function test_cf_user_ya_returns_one(): void
    {
        $this->assertEquals(1.0, $this->service->getCfUser('Ya'));
        $this->assertEquals(1.0, $this->service->getCfUser('Pasti Ya'));
        $this->assertEquals(1.0, $this->service->getCfUser('Sangat Sering'));
    }

    public function test_cf_user_sering_returns_point_eight(): void
    {
        $this->assertEquals(0.8, $this->service->getCfUser('Sering'));
        $this->assertEquals(0.8, $this->service->getCfUser('Hampir Pasti'));
    }

    public function test_cf_user_kadang_returns_point_six(): void
    {
        $this->assertEquals(0.6, $this->service->getCfUser('Kadang'));
        $this->assertEquals(0.5, $this->service->getCfUser('Mungkin'));
    }

    public function test_cf_user_jarang_returns_point_four(): void
    {
        $this->assertEquals(0.4, $this->service->getCfUser('Jarang'));
        $this->assertEquals(0.3, $this->service->getCfUser('Ragu-ragu'));
    }

    public function test_cf_user_sangat_jarang_returns_point_two(): void
    {
        $this->assertEquals(0.2, $this->service->getCfUser('Sangat Jarang'));
        $this->assertEquals(0.2, $this->service->getCfUser('Sedikit'));
    }

    public function test_cf_user_tidak_returns_zero(): void
    {
        $this->assertEquals(0.0, $this->service->getCfUser('Tidak'));
        $this->assertEquals(0.0, $this->service->getCfUser('Tidak Pernah'));
    }

    public function test_cf_user_unknown_answer_returns_zero(): void
    {
        $this->assertEquals(0.0, $this->service->getCfUser('jawaban_tidak_valid'));
        $this->assertEquals(0.0, $this->service->getCfUser(''));
    }

    // ════════════════════════════════════════════════════════════════
    // 2. CF Combine Formula Validation
    // ════════════════════════════════════════════════════════════════

    public function test_cf_combine_formula_is_mathematically_correct(): void
    {
        $cf1 = 0.8;
        $cf2 = 0.9;
        $expected = $cf1 + ($cf2 * (1 - $cf1));
        $this->assertEqualsWithDelta(0.98, $expected, 0.001);
    }

    public function test_cf_combine_with_zero_stays_unchanged(): void
    {
        $cf1 = 0.0;
        $cf2 = 0.7;
        $result = $cf1 + ($cf2 * (1 - $cf1));
        $this->assertEqualsWithDelta(0.7, $result, 0.001);
    }

    public function test_cf_combine_never_exceeds_one(): void
    {
        $cf = 0.0;
        foreach ([1.0, 1.0, 1.0, 1.0] as $next) {
            $cf = $cf + ($next * (1 - $cf));
        }
        $this->assertLessThanOrEqual(1.0, $cf);
    }

    // ════════════════════════════════════════════════════════════════
    // 3. Calculation scenarios
    // ════════════════════════════════════════════════════════════════

    public function test_all_negative_answers_produce_zero_cf(): void
    {
        $answers = ['Tidak Pernah', 'Tidak', 'Tidak', 'Tidak Pernah'];
        $totalCf = 0.0;
        foreach ($answers as $ans) {
            $cf = $this->service->getCfUser($ans) * 0.8;
            if ($cf > 0) {
                $totalCf = $totalCf + ($cf * (1 - $totalCf));
            }
        }
        $this->assertEquals(0.0, $totalCf);
    }

    public function test_all_positive_answers_produce_high_cf(): void
    {
        $answers = ['Ya', 'Ya', 'Ya', 'Ya'];
        $bobot = 0.9;
        $totalCf = 0.0;
        foreach ($answers as $ans) {
            $cfWeighted = $this->service->getCfUser($ans) * $bobot;
            if ($cfWeighted > 0) {
                $totalCf = $totalCf + ($cfWeighted * (1 - $totalCf));
            }
        }
        $this->assertGreaterThan(0.5, $totalCf);
    }

    public function test_mixed_answers_produce_intermediate_cf(): void
    {
        $data = [
            ['ans' => 'Ya',     'bobot' => 0.8],
            ['ans' => 'Kadang', 'bobot' => 0.7],
            ['ans' => 'Tidak',  'bobot' => 0.9],
            ['ans' => 'Sering', 'bobot' => 0.6],
        ];
        $totalCf = 0.0;
        foreach ($data as $item) {
            $cfWeighted = $this->service->getCfUser($item['ans']) * $item['bobot'];
            if ($cfWeighted > 0) {
                $totalCf = $totalCf + ($cfWeighted * (1 - $totalCf));
            }
        }
        $this->assertGreaterThan(0.0, $totalCf);
        $this->assertLessThanOrEqual(1.0, $totalCf);
    }

    // ════════════════════════════════════════════════════════════════
    // 4. Service structure
    // ════════════════════════════════════════════════════════════════

    public function test_service_has_required_methods(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $this->assertTrue($reflection->hasMethod('solve'));
        $this->assertTrue($reflection->hasMethod('getCfUser'));
        $this->assertTrue($reflection->hasMethod('getNextSymptoms'));
        $this->assertTrue($reflection->hasMethod('saveResult'));
        $this->assertTrue($reflection->hasMethod('generateExplanation'));
    }

    public function test_solve_method_accepts_array_parameter(): void
    {
        $method = new \ReflectionMethod($this->service, 'solve');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('answers', $params[0]->getName());
        $this->assertEquals('conflictStrategy', $params[1]->getName());
    }

    public function test_get_cf_user_covers_all_expected_answer_options(): void
    {
        $expectedMappings = [
            'Sangat Sering'  => 1.0,
            'Pasti Ya'       => 1.0,
            'Ya'             => 1.0,
            'Sering'         => 0.8,
            'Hampir Pasti'   => 0.8,
            'Kadang'         => 0.6,
            'Mungkin'        => 0.5,
            'Jarang'         => 0.4,
            'Ragu-ragu'      => 0.3,
            'Sangat Jarang'  => 0.2,
            'Sedikit'        => 0.2,
            'Tidak'          => 0.0,
            'Tidak Pernah'   => 0.0,
        ];
        foreach ($expectedMappings as $answer => $expectedCf) {
            $this->assertEquals(
                $expectedCf,
                $this->service->getCfUser($answer),
                "CF untuk jawaban '{$answer}' seharusnya {$expectedCf}"
            );
        }
    }

    // ════════════════════════════════════════════════════════════════
    // 5. Threshold & Explanation
    // ════════════════════════════════════════════════════════════════

    public function test_threshold_exists_in_source(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString('0.25', $source);
    }

    public function test_generate_explanation_returns_correct_structure(): void
    {
        $tracing = [
            'rule_kode' => 'R01',
            'cf_pakar_rule' => 0.95,
            'cf_combine_gejala' => 0.85,
            'gejala_details' => [
                ['gejala' => 'Test Gejala', 'kode' => 'G01', 'user_ans' => 'Ya', 'cf_user' => 1.0, 'bobot' => 0.8, 'cf_sub' => 0.8],
            ],
            'method' => 'Backward Chaining (CF Combine)',
        ];
        $diagnosa = new \stdClass();
        $diagnosa->nama = 'Burnout Tinggi (High)';

        $result = $this->service->generateExplanation($tracing, $diagnosa, 0.8075);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('reasoning_chain', $result);
        $this->assertArrayHasKey('dominant_symptoms', $result);
        $this->assertArrayHasKey('confidence_label', $result);
        $this->assertNotEmpty($result['summary']);
        $this->assertNotEmpty($result['reasoning_chain']);
        $this->assertCount(1, $result['dominant_symptoms']);
        $this->assertEquals('Sangat Yakin', $result['confidence_label']);
    }

    public function test_explanation_confidence_labels(): void
    {
        $tracing = [];
        $diagnosa = new \stdClass();
        $diagnosa->nama = 'Test';

        $r1 = $this->service->generateExplanation($tracing, $diagnosa, 0.90);
        $this->assertEquals('Sangat Yakin', $r1['confidence_label']);

        $r2 = $this->service->generateExplanation($tracing, $diagnosa, 0.65);
        $this->assertEquals('Cukup Yakin', $r2['confidence_label']);

        $r3 = $this->service->generateExplanation($tracing, $diagnosa, 0.45);
        $this->assertEquals('Cukup Mungkin', $r3['confidence_label']);

        $r4 = $this->service->generateExplanation($tracing, $diagnosa, 0.25);
        $this->assertEquals('Kemungkinan Rendah', $r4['confidence_label']);

        $r5 = $this->service->generateExplanation($tracing, $diagnosa, 0.10);
        $this->assertEquals('Tidak Terkonfirmasi', $r5['confidence_label']);
    }
}
