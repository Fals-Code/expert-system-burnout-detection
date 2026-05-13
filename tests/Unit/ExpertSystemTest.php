<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExpertSystemService;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Aturan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpertSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpertSystemService();
    }

    public function test_it_returns_correct_cf_user_mapping()
    {
        $this->assertEquals(1.0, $this->service->getCfUser('Sangat Sering'));
        $this->assertEquals(0.8, $this->service->getCfUser('Sering'));
        $this->assertEquals(0.6, $this->service->getCfUser('Kadang'));
        $this->assertEquals(0.0, $this->service->getCfUser('Tidak'));
    }

    public function test_it_correctly_calculates_cf_combine()
    {
        // 1. Setup Diagnosa
        $diagnosa = Diagnosa::create([
            'kode' => 'D_TEST',
            'nama' => 'Test Diagnosa',
            'tingkat' => 'TINGGI',
            'deskripsi' => 'Test',
            'saran' => 'Test'
        ]);

        // 2. Setup Gejala
        $g1 = Gejala::create(['kode' => 'G1', 'nama' => 'Gejala 1', 'bobot' => 0.8, 'kategori' => 'fisik']);
        $g2 = Gejala::create(['kode' => 'G2', 'nama' => 'Gejala 2', 'bobot' => 0.6, 'kategori' => 'fisik']);

        // 3. Setup Aturan
        $rule = Aturan::create(['kode' => 'R_TEST', 'diagnosa_id' => $diagnosa->id, 'cf_pakar' => 0.9]);
        $rule->gejala()->attach([
            $g1->id => ['bobot_pakar' => 0.8],
            $g2->id => ['bobot_pakar' => 0.6]
        ]);

        $answers = [
            'G1' => 'Sangat Sering', // cf_user = 1.0 -> CF_sub = 1.0 * 0.8 = 0.8
            'G2' => 'Sering'         // cf_user = 0.8 -> CF_sub = 0.8 * 0.6 = 0.48
        ];

        // Calculation:
        // CF_combine = 0.8 + 0.48 * (1 - 0.8) = 0.896
        // CF_final = 0.896 * 0.9 = 0.8064

        $result = $this->service->solve($answers);

        $this->assertEquals('D_TEST', $result['diagnosa']->kode);
        $this->assertEquals(0.8064, round($result['cf'], 4));
        $this->assertCount(2, $result['tracing']['gejala_details']);
    }
}
