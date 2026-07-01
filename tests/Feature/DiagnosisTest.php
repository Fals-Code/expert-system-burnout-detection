<?php

namespace Tests\Feature;

use App\Models\Aturan;
use App\Models\Diagnosa;
use App\Models\Gejala;
use App\Models\Konsultasi;
use App\Models\User;
use App\Services\ExpertSystemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DiagnosisTest extends TestCase
{
    use RefreshDatabase;

    private Diagnosa $fallbackDiagnosis;

    private Diagnosa $highDiagnosis;

    private Gejala $g001;

    private Gejala $g006;

    private Gejala $g009;

    private Gejala $g014;

    private Gejala $g017;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerDiagnosisTestingRoute();
        $this->seedKnowledge();
    }

    public function test_all_negative_answers_fallback_to_not_indicated(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);

        $response = $this->actingAs($user)->post('/diagnosis', ['selected' => []]);

        $response->assertStatus(200);
        $response->assertSeeText('Tidak Terindikasi');
        $response->assertSeeText('FALLBACK');

        $latest = Konsultasi::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($latest);
        $this->assertSame($this->fallbackDiagnosis->id, (int) $latest->diagnosa_id);
    }

    public function test_pdf_r001_answers_confirm_high_risk(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);

        $response = $this->actingAs($user)->post('/diagnosis', [
            'selected' => [$this->g001->kode, $this->g006->kode, $this->g009->kode, $this->g014->kode, $this->g017->kode],
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Risiko Burnout Tinggi');
        $response->assertSeeText('R001');

        $latest = Konsultasi::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($latest);
        $this->assertSame($this->highDiagnosis->id, (int) $latest->diagnosa_id);
        $this->assertEqualsWithDelta(0.7885, $latest->cf_final, 0.001);
    }

    private function registerDiagnosisTestingRoute(): void
    {
        Route::middleware(['web', 'auth'])->post('/diagnosis', function (Request $request, ExpertSystemService $expertSystem) {
            $selected = $request->input('selected', []);
            $answers = Gejala::orderBy('kode')
                ->pluck('kode')
                ->mapWithKeys(fn (string $kode) => [$kode => in_array($kode, $selected, true) ? 'Sering' : 'Tidak Pernah'])
                ->all();

            $result = $expertSystem->solve($answers);
            $konsultasi = $expertSystem->saveResult(Auth::id(), $result, $answers);
            $explanation = $expertSystem->generateExplanation($result['tracing'], $result['diagnosa'], (float) $result['cf']);

            return response()->view('karyawan.deteksi.hasil', [
                'konsultasi' => $konsultasi->load(['diagnosa', 'gejala', 'user']),
                'confidence' => number_format($konsultasi->cf_final * 100, 1),
                'tracing' => $result['tracing'],
                'explanation' => $explanation,
            ]);
        });
    }

    private function seedKnowledge(): void
    {
        $this->fallbackDiagnosis = Diagnosa::create([
            'kode' => 'D01',
            'nama' => 'Tidak Terindikasi Burnout',
            'tingkat' => 'TIDAK_TERINDIKASI',
            'deskripsi' => 'Tidak ada rule terkonfirmasi.',
            'saran' => 'Pertahankan kebiasaan kerja sehat.',
            'color' => '#16a34a',
            'bg_light' => '#f0fdf4',
        ]);

        $this->highDiagnosis = Diagnosa::create([
            'kode' => 'D02',
            'nama' => 'Risiko Burnout Tinggi',
            'tingkat' => 'TINGGI',
            'deskripsi' => 'Indikasi tinggi.',
            'saran' => 'Cari dukungan yang sesuai.',
            'color' => '#dc2626',
            'bg_light' => '#fef2f2',
        ]);

        Diagnosa::create(['kode' => 'D03', 'nama' => 'Risiko Burnout Sedang', 'tingkat' => 'SEDANG', 'deskripsi' => 'x', 'saran' => 'x']);
        Diagnosa::create(['kode' => 'D04', 'nama' => 'Risiko Burnout Rendah', 'tingkat' => 'RENDAH', 'deskripsi' => 'x', 'saran' => 'x']);

        $this->g001 = Gejala::create(['kode' => 'G001', 'nama' => 'Merasa terkuras habis saat bekerja', 'kategori' => 'emosional', 'bobot' => 0.85]);
        $this->g006 = Gejala::create(['kode' => 'G006', 'nama' => 'Kelelahan emosional mendalam', 'kategori' => 'emosional', 'bobot' => 0.92]);
        $this->g009 = Gejala::create(['kode' => 'G009', 'nama' => 'Sinisme terhadap pekerjaan', 'kategori' => 'perilaku', 'bobot' => 0.80]);
        $this->g014 = Gejala::create(['kode' => 'G014', 'nama' => 'Tidak mampu pulih meski sudah istirahat', 'kategori' => 'emosional', 'bobot' => 0.88]);
        $this->g017 = Gejala::create(['kode' => 'G017', 'nama' => 'Merasa tidak berdaya menghadapi tantangan kerja', 'kategori' => 'kognitif', 'bobot' => 0.70]);

        $rule = Aturan::create([
            'kode' => 'R001',
            'diagnosa_id' => $this->highDiagnosis->id,
            'cf_pakar' => 0.95,
            'prioritas' => 100,
            'is_active' => true,
            'deskripsi' => 'PDF R001',
            'min_threshold' => 0.25,
        ]);

        $rule->gejala()->attach([
            $this->g001->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS'],
            $this->g006->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS'],
            $this->g009->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS'],
            $this->g014->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS'],
            $this->g017->id => ['bobot_pakar' => 0.0, 'evidence_direction' => 'PRESENT_SUPPORTS'],
        ]);
    }
}
