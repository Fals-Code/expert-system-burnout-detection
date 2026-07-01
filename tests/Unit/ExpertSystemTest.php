<?php

namespace Tests\Unit;

use App\Models\Aturan;
use App\Models\Diagnosa;
use App\Models\Gejala;
use App\Services\ExpertSystemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_priority_prefers_high_before_medium_even_when_medium_cf_is_higher(): void
    {
        $high = Diagnosa::create(['kode' => 'D02', 'nama' => 'Risiko Burnout Tinggi', 'tingkat' => 'TINGGI', 'deskripsi' => 'x', 'saran' => 'x']);
        $medium = Diagnosa::create(['kode' => 'D03', 'nama' => 'Risiko Burnout Sedang', 'tingkat' => 'SEDANG', 'deskripsi' => 'x', 'saran' => 'x']);
        Diagnosa::create(['kode' => 'D01', 'nama' => 'Tidak Terindikasi Burnout', 'tingkat' => 'TIDAK_TERINDIKASI', 'deskripsi' => 'x', 'saran' => 'x']);

        $gHigh = Gejala::create(['kode' => 'G001', 'nama' => 'High symptom', 'bobot' => 0.5, 'kategori' => 'emosional']);
        $gMedium = Gejala::create(['kode' => 'G002', 'nama' => 'Medium symptom', 'bobot' => 1.0, 'kategori' => 'emosional']);

        $rHigh = Aturan::create(['kode' => 'R001', 'diagnosa_id' => $high->id, 'cf_pakar' => 0.6, 'prioritas' => 10, 'is_active' => true, 'min_threshold' => 0.25]);
        $rMedium = Aturan::create(['kode' => 'R003', 'diagnosa_id' => $medium->id, 'cf_pakar' => 1.0, 'prioritas' => 10, 'is_active' => true, 'min_threshold' => 0.25]);
        $rHigh->gejala()->attach([$gHigh->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS']]);
        $rMedium->gejala()->attach([$gMedium->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS']]);

        $result = (new ExpertSystemService)->solve([
            'G001' => 'Sering',
            'G002' => 'Sering',
        ]);

        $this->assertSame('D02', $result['diagnosa']->kode);
        $this->assertEqualsWithDelta(0.3, $result['cf'], 0.0001);
    }

    public function test_fallback_is_returned_when_no_goal_passes_threshold(): void
    {
        $fallback = Diagnosa::create(['kode' => 'D01', 'nama' => 'Tidak Terindikasi Burnout', 'tingkat' => 'TIDAK_TERINDIKASI', 'deskripsi' => 'x', 'saran' => 'x']);
        $high = Diagnosa::create(['kode' => 'D02', 'nama' => 'Risiko Burnout Tinggi', 'tingkat' => 'TINGGI', 'deskripsi' => 'x', 'saran' => 'x']);
        $g = Gejala::create(['kode' => 'G001', 'nama' => 'Symptom', 'bobot' => 0.8, 'kategori' => 'emosional']);
        $r = Aturan::create(['kode' => 'R001', 'diagnosa_id' => $high->id, 'cf_pakar' => 0.95, 'prioritas' => 10, 'is_active' => true, 'min_threshold' => 0.25]);
        $r->gejala()->attach([$g->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS']]);

        $result = (new ExpertSystemService)->solve(['G001' => 'Tidak Pernah']);

        $this->assertSame($fallback->id, $result['diagnosa']->id);
        $this->assertSame(0.0, $result['cf']);
        $this->assertSame('TIDAK_TERINDIKASI', $result['tracing']['goal_terkonfirmasi']);
    }
}
