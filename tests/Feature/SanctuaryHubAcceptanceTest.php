<?php

namespace Tests\Feature;

use App\Models\Diagnosa;
use App\Models\Konsultasi;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SanctuaryHubAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_users_can_login_to_their_role_dashboards(): void
    {
        $this->seed();

        foreach ([
            'karyawan@sanctuaryhub.test' => '/karyawan/dashboard',
            'hrd@sanctuaryhub.test' => '/hrd/dashboard',
            'admin@sanctuaryhub.test' => '/admin/dashboard',
        ] as $email => $redirect) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ])->assertRedirect($redirect);

            $this->post('/logout')->assertRedirect('/login');
        }
    }

    public function test_employee_cannot_open_other_employee_result(): void
    {
        $this->seed();

        $owner = User::factory()->create(['role' => 'karyawan']);
        $other = User::factory()->create(['role' => 'karyawan']);
        $diagnosa = Diagnosa::firstOrFail();
        $konsultasi = Konsultasi::create([
            'user_id' => $owner->id,
            'diagnosa_id' => $diagnosa->id,
            'cf_final' => 0.0,
            'tracing' => [],
        ]);

        $this->actingAs($other)
            ->get(route('karyawan.hasil', ['id' => $konsultasi->id]))
            ->assertForbidden();
    }

    public function test_hrd_cannot_modify_knowledge_base_and_admin_can_create_symptom(): void
    {
        $this->seed();

        $hrd = User::where('role', 'hrd')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        $payload = [
            'kode' => 'G999',
            'nama' => 'Gejala test',
            'kategori' => 'emosional',
            'bobot' => 0.5,
        ];

        $this->actingAs($hrd)
            ->post(route('admin.knowledge.gejala.store'), $payload)
            ->assertRedirect('/hrd/dashboard');

        $this->assertDatabaseMissing('gejala', ['kode' => 'G999']);

        $this->actingAs($admin)
            ->post(route('admin.knowledge.gejala.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('gejala', ['kode' => 'G999']);
    }

    public function test_hrd_dashboard_exposes_four_categories(): void
    {
        $this->seed();

        $hrd = User::where('role', 'hrd')->firstOrFail();

        $this->actingAs($hrd)
            ->get(route('hrd.dashboard'))
            ->assertOk()
            ->assertSeeText('Burnout Tinggi')
            ->assertSeeText('Burnout Sedang')
            ->assertSeeText('Burnout Rendah')
            ->assertSeeText('Tidak Terindikasi');
    }

    public function test_service_worker_does_not_cache_sensitive_pages(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertStringNotContainsString('/login', $serviceWorker);
        $this->assertStringNotContainsString('/karyawan', $serviceWorker);
        $this->assertStringNotContainsString('/hrd', $serviceWorker);
        $this->assertStringNotContainsString('/admin', $serviceWorker);
    }

    public function test_hrd_notification_does_not_expose_employee_identity(): void
    {
        $this->seed();

        $employee = User::factory()->create([
            'nama' => 'Sensitive Employee',
            'role' => 'karyawan',
            'password' => Hash::make('password'),
        ]);
        $diagnosa = Diagnosa::where('tingkat', 'TINGGI')->firstOrFail();
        $konsultasi = Konsultasi::create([
            'user_id' => $employee->id,
            'diagnosa_id' => $diagnosa->id,
            'cf_final' => 0.8,
            'tracing' => [],
        ]);

        NotificationService::dispatchAfterDeteksi($konsultasi, $employee, $diagnosa);

        $messages = Notification::whereHas('user', fn ($query) => $query->where('role', 'hrd'))
            ->pluck('message')
            ->implode("\n");

        $this->assertStringNotContainsString('Sensitive Employee', $messages);
        $this->assertStringContainsString('dashboard agregat HRD', $messages);
    }

    public function test_knowledge_rule_requires_at_least_one_premise(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $diagnosa = Diagnosa::where('tingkat', 'TINGGI')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.knowledge'))
            ->post(route('admin.knowledge.aturan.store'), [
                'kode' => 'R999',
                'diagnosa_id' => $diagnosa->id,
                'cf_pakar' => 0.5,
                'gejala_ids' => [],
                'bobot_pakar' => [],
                'prioritas' => 1,
                'min_threshold' => 0.25,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('gejala_ids');
    }
}
