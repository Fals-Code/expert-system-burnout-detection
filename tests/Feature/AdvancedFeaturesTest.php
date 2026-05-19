<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use App\Services\HrisService;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $employeeUser;
    protected $divisi;
    protected $diagnosa;

    protected function setUp(): void
    {
        parent::setUp();

        // Create division
        $this->divisi = Divisi::create(['nama' => 'IT Department']);

        // Create users
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'divisi_id' => $this->divisi->id,
        ]);

        $this->employeeUser = User::factory()->create([
            'role' => 'karyawan',
            'divisi_id' => $this->divisi->id,
        ]);

        // Create Diagnosa
        $this->diagnosa = Diagnosa::create([
            'kode' => 'D01',
            'nama' => 'Severe Burnout',
            'tingkat' => 'SANGAT TINGGI',
            'deskripsi' => 'Parah',
            'saran' => 'Istirahat'
        ]);
    }

    /**
     * Test locale switcher route and middleware.
     */
    public function test_locale_switcher_changes_session_locale()
    {
        // Default locale is id (as fallback)
        $response = $this->get('/locale/en');
        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'en');

        // Middleware setLocale should set App locale
        $this->withSession(['locale' => 'en'])
             ->get(route('login'))
             ->assertStatus(200);
        $this->assertEquals('en', App::getLocale());

        // Switch back to id
        $response = $this->get('/locale/id');
        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'id');

        $this->withSession(['locale' => 'id'])
             ->get(route('login'))
             ->assertStatus(200);
        $this->assertEquals('id', App::getLocale());
    }

    /**
     * Test HRIS Service returns expected metrics.
     */
    public function test_hris_service_generates_metrics()
    {
        $service = new HrisService();
        $metrics = $service->getMetrics($this->employeeUser);

        $this->assertArrayHasKey('total_hours', $metrics);
        $this->assertArrayHasKey('overtime_hours', $metrics);
        $this->assertArrayHasKey('late_arrivals', $metrics);
        $this->assertArrayHasKey('remaining_leaves', $metrics);
        $this->assertArrayHasKey('correlation_message', $metrics);
    }

    /**
     * Test Recommendation Service behaves correctly under different levels.
     */
    public function test_recommendation_service_scales_by_burnout_level()
    {
        $service = new RecommendationService();
        $hrisMetrics = [
            'overtime_hours' => 25,
            'remaining_leaves' => 10,
        ];

        // 1. Sangat Tinggi Burnout Consultation
        $cHigh = new Konsultasi();
        $cHigh->cf_final = 0.85;
        $cHigh->setRelation('diagnosa', $this->diagnosa); // SANGAT TINGGI

        $recsHigh = $service->generate($this->employeeUser, $hrisMetrics, $cHigh);
        $this->assertStringContainsString('3 hari', $recsHigh['leave_recommendation']);
        $this->assertStringContainsString('Box Breathing', $recsHigh['activity_recommendation']);

        // 2. Normal / Rendah
        $diagnosaLow = Diagnosa::create([
            'kode' => 'D04',
            'nama' => 'Normal',
            'tingkat' => 'RENDAH',
            'deskripsi' => 'Aman',
            'saran' => 'Pertahankan'
        ]);
        $cLow = new Konsultasi();
        $cLow->cf_final = 0.15;
        $cLow->setRelation('diagnosa', $diagnosaLow);

        $recsLow = $service->generate($this->employeeUser, $hrisMetrics, $cLow);
        $this->assertStringContainsString('kardio ringan', $recsLow['activity_recommendation']);
    }

    /**
     * Test admin dashboard returns correct analytics view data.
     */
    public function test_admin_dashboard_returns_analytics_data()
    {
        // Create consultation history to populate trend
        Konsultasi::create([
            'user_id' => $this->employeeUser->id,
            'diagnosa_id' => $this->diagnosa->id,
            'cf_final' => 0.82,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('riskDistribution');
        $response->assertViewHas('divisionLabels');
        $response->assertViewHas('divisionAverages');
        $response->assertViewHas('earlyAlerts');
    }

    /**
     * Test employee dashboard fetches hris and recommendations correctly.
     */
    public function test_employee_dashboard_fetches_hris_and_recommendations()
    {
        // Create consultation history
        Konsultasi::create([
            'user_id' => $this->employeeUser->id,
            'diagnosa_id' => $this->diagnosa->id,
            'cf_final' => 0.76,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->employeeUser)
            ->get(route('karyawan.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('hrisMetrics');
        $response->assertViewHas('recommendations');
    }
}
