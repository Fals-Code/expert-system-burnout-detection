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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DiagnosisTest extends TestCase
{
    use RefreshDatabase;

    private Diagnosa $healthyDiagnosis;
    private Diagnosa $highBurnoutDiagnosis;
    private Diagnosa $mediumBurnoutDiagnosis;
    private Diagnosa $lowBurnoutDiagnosis;

    private Gejala $g01;
    private Gejala $g02;
    private Gejala $g03;
    private Gejala $g04;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->registerDiagnosisTestingRoute();
        $this->seedExpertKnowledgeBase();
    }

    public function test_healthy_employee_receives_stable_work_balance_with_r01_rule(): void
    {
        $user = User::factory()->create([
            'role' => 'karyawan',
        ]);

        $response = $this->actingAs($user)
            ->post('/diagnosis', [
                'gejala_id' => [],
            ]);

        $response->assertStatus(200);
        $response->assertSeeText('Kondisi Kerja Anda Tampak Stabil');
        $response->assertSeeText('R01');

        $latestConsultation = Konsultasi::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($latestConsultation);
        $this->assertSame(1, (int) $latestConsultation->diagnosa_id);
        $this->assertSame($this->healthyDiagnosis->id, (int) $latestConsultation->diagnosa_id);
    }

    public function test_high_burnout_symptoms_receives_support_summary_and_not_healthy_diagnosis(): void
    {
        $user = User::factory()->create([
            'role' => 'karyawan',
        ]);

        $response = $this->actingAs($user)
            ->post('/diagnosis', [
                'gejala_id' => [
                    $this->g01->id,
                    $this->g02->id,
                    $this->g03->id,
                ],
            ]);

        $response->assertStatus(200);
        $response->assertSeeText('Kondisi Anda Membutuhkan Perhatian Ekstra');
        $response->assertDontSeeText('Kondisi Kerja Anda Tampak Stabil');

        $latestConsultation = Konsultasi::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($latestConsultation);
        $this->assertSame(2, (int) $latestConsultation->diagnosa_id);
        $this->assertSame($this->highBurnoutDiagnosis->id, (int) $latestConsultation->diagnosa_id);
        $this->assertNotSame($this->healthyDiagnosis->id, (int) $latestConsultation->diagnosa_id);
    }

    private function registerDiagnosisTestingRoute(): void
    {
        Route::middleware(['web', 'auth'])->post('/diagnosis', function (
            Request $request,
            ExpertSystemService $expertSystem
        ) {
            $selectedGejalaIds = collect($request->input('gejala_id', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();

            $selectedKodeGejala = Gejala::query()
                ->whereIn('id', $selectedGejalaIds)
                ->pluck('kode')
                ->toArray();

            $answers = [];

            Gejala::query()
                ->orderBy('kode')
                ->pluck('kode')
                ->each(function (string $kode) use (&$answers, $selectedKodeGejala) {
                    $answers[$kode] = in_array($kode, $selectedKodeGejala, true)
                        ? 'Ya'
                        : 'Tidak';
                });

            $result = $expertSystem->solve($answers);

            $konsultasi = $expertSystem->saveResult(
                Auth::id(),
                $result,
                $answers
            );

            $tracing = $result['tracing'] ?? [];

            $explanation = $expertSystem->generateExplanation(
                $tracing,
                $result['diagnosa'],
                (float) $result['cf']
            );

            return response()->view('karyawan.deteksi.hasil', [
                'konsultasi' => $konsultasi->load(['diagnosa', 'gejala', 'user']),
                'confidence' => number_format($konsultasi->cf_final * 100, 1),
                'tracing' => $tracing,
                'explanation' => $explanation,
            ]);
        });
    }

    private function seedExpertKnowledgeBase(): void
    {
        $this->healthyDiagnosis = Diagnosa::query()->create([
            'kode' => 'D01',
            'nama' => 'Tidak Burnout (Kondisi Sehat)',
            'tingkat' => 'TIDAK BURNOUT',
            'deskripsi' => 'Kondisi karyawan berada dalam keadaan sehat dan stabil.',
            'saran' => 'Pertahankan pola kerja sehat, istirahat cukup, dan keseimbangan aktivitas harian.',
            'color' => '#16a34a',
            'bg_light' => '#f0fdf4',
        ]);

        $this->highBurnoutDiagnosis = Diagnosa::query()->create([
            'kode' => 'D02',
            'nama' => 'Burnout Tinggi',
            'tingkat' => 'TINGGI',
            'deskripsi' => 'Karyawan menunjukkan indikasi burnout tinggi.',
            'saran' => 'Segera lakukan evaluasi beban kerja dan konsultasi dengan pihak terkait.',
            'color' => '#dc2626',
            'bg_light' => '#fef2f2',
        ]);

        $this->mediumBurnoutDiagnosis = Diagnosa::query()->create([
            'kode' => 'D03',
            'nama' => 'Burnout Sedang',
            'tingkat' => 'SEDANG',
            'deskripsi' => 'Karyawan menunjukkan indikasi burnout sedang.',
            'saran' => 'Kurangi tekanan kerja dan perbaiki pola istirahat.',
            'color' => '#f97316',
            'bg_light' => '#fff7ed',
        ]);

        $this->lowBurnoutDiagnosis = Diagnosa::query()->create([
            'kode' => 'D04',
            'nama' => 'Burnout Rendah',
            'tingkat' => 'RENDAH',
            'deskripsi' => 'Karyawan menunjukkan indikasi burnout rendah.',
            'saran' => 'Tetap pantau kondisi kerja dan jaga rutinitas positif.',
            'color' => '#eab308',
            'bg_light' => '#fefce8',
        ]);

        $this->g01 = Gejala::query()->create([
            'kode' => 'G01',
            'nama' => 'Merasa terkuras secara fisik dan emosional setelah bekerja.',
            'kategori' => 'emosional',
            'bobot' => 0.90,
        ]);

        $this->g02 = Gejala::query()->create([
            'kode' => 'G02',
            'nama' => 'Merasa letih dan tidak memiliki energi menghadapi pekerjaan.',
            'kategori' => 'emosional',
            'bobot' => 0.85,
        ]);

        $this->g03 = Gejala::query()->create([
            'kode' => 'G03',
            'nama' => 'Merasa tegang, tertekan, atau kewalahan oleh tuntutan pekerjaan.',
            'kategori' => 'emosional',
            'bobot' => 0.88,
        ]);

        $this->g04 = Gejala::query()->create([
            'kode' => 'G04',
            'nama' => 'Mudah tersinggung, frustrasi, atau kehilangan kesabaran.',
            'kategori' => 'perilaku',
            'bobot' => 0.80,
        ]);

        $healthyRule = Aturan::query()->create([
            'kode' => 'R01',
            'diagnosa_id' => $this->healthyDiagnosis->id,
            'cf_pakar' => 0.98,
            'prioritas' => 10,
            'is_active' => true,
            'deskripsi' => 'Rule kondisi sehat berdasarkan tidak munculnya gejala burnout utama.',
            'min_threshold' => 0.30,
        ]);

        $healthyRule->gejala()->attach([
            $this->g01->id => [
                'bobot_pakar' => 0.90,
                'evidence_direction' => 'ABSENT_SUPPORTS',
            ],
            $this->g02->id => [
                'bobot_pakar' => 0.85,
                'evidence_direction' => 'ABSENT_SUPPORTS',
            ],
            $this->g03->id => [
                'bobot_pakar' => 0.88,
                'evidence_direction' => 'ABSENT_SUPPORTS',
            ],
        ]);

        $highBurnoutRule = Aturan::query()->create([
            'kode' => 'R02',
            'diagnosa_id' => $this->highBurnoutDiagnosis->id,
            'cf_pakar' => 0.97,
            'prioritas' => 20,
            'is_active' => true,
            'deskripsi' => 'Rule burnout tinggi berdasarkan munculnya gejala burnout utama.',
            'min_threshold' => 0.30,
        ]);

        $highBurnoutRule->gejala()->attach([
            $this->g01->id => [
                'bobot_pakar' => 0.95,
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ],
            $this->g02->id => [
                'bobot_pakar' => 0.90,
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ],
            $this->g03->id => [
                'bobot_pakar' => 0.92,
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ],
        ]);

        Cache::flush();
    }
}
