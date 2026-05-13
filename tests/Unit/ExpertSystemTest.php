<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExpertSystemService;

class ExpertSystemTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpertSystemService();
    }

    public function test_it_returns_correct_cf_user_mapping()
    {
        $this->assertEquals(1.0, $this->service->getCfUser('Sering'));
        $this->assertEquals(0.6, $this->service->getCfUser('Kadang'));
        $this->assertEquals(0.0, $this->service->getCfUser('Tidak Pernah'));
    }

    public function test_it_correctly_calculates_hypothesis_cf()
    {
        // Mock a Rule and Symptoms
        $gejala1 = (object)['kode' => 'G001', 'bobot' => 0.8, 'nama' => 'Pusing'];
        $gejala2 = (object)['kode' => 'G002', 'bobot' => 0.6, 'nama' => 'Lelah'];
        
        $rule = (object)[
            'kode' => 'R001',
            'cf_pakar' => 0.9,
            'gejala' => collect([$gejala1, $gejala2])
        ];

        $answers = [
            'G001' => 'Sering', // cf_user = 1.0
            'G002' => 'Kadang'  // cf_user = 0.6
        ];

        // Calculation:
        // G001: 1.0 * 0.8 = 0.8
        // G002: 0.6 * 0.6 = 0.36
        // Sum: 0.8 + 0.36 = 1.16
        // Avg (2 gejala): 1.16 / 2 = 0.58
        // CF Final: 0.58 * 0.9 = 0.522

        [$cfHasil, $bestRule, $tracing] = $this->service->evaluateHypothesis([$rule], $answers);

        $this->assertEquals(0.522, round($cfHasil, 3));
        $this->assertEquals('R001', $bestRule->kode);
        $this->assertEquals(0.58, round($tracing['avg_gejala_cf'], 2));
    }
}
