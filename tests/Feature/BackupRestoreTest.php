<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Diagnosa;
use App\Models\Gejala;
use App\Models\Aturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin User
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // Seed some starter knowledge base records
        Diagnosa::create([
            'id' => 1,
            'kode' => 'D01',
            'nama' => 'Severe Burnout',
            'tingkat' => 'SANGAT TINGGI',
            'deskripsi' => 'Parah',
            'saran' => 'Istirahat'
        ]);

        Gejala::create([
            'id' => 1,
            'kode' => 'G01',
            'nama' => 'Lelah Fisik',
            'kategori' => 'emosional',
            'bobot' => 0.8
        ]);

        Aturan::create([
            'id' => 1,
            'kode' => 'R01',
            'diagnosa_id' => 1,
            'cf_pakar' => 0.9,
            'prioritas' => 5,
            'min_threshold' => 0.2
        ]);
    }

    /**
     * Test admin can download knowledge base backup.
     */
    public function test_admin_can_download_backup()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.knowledge.backup'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('diagnosa', $data);
        $this->assertArrayHasKey('gejala', $data);
        $this->assertArrayHasKey('aturan', $data);
    }

    /**
     * Test non-admin cannot download backup.
     */
    public function test_non_admin_cannot_download_backup()
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        $response = $this->actingAs($karyawan)
            ->get(route('admin.knowledge.backup'));

        $response->assertStatus(302);
        $response->assertRedirect('karyawan/dashboard');
    }

    /**
     * Test admin can restore knowledge base from valid backup file and it flushes cache.
     */
    public function test_admin_can_restore_knowledge_base()
    {
        // Set up dummy cache
        Cache::put('aturan_active_rules', 'some-stale-rules');

        // Create backup data with new items
        $backupData = [
            'diagnosa' => [
                [
                    'id' => 2,
                    'kode' => 'D02',
                    'nama' => 'Mild Stress',
                    'tingkat' => 'RENDAH',
                    'deskripsi' => 'Ringan',
                    'saran' => 'Jalan-jalan'
                ]
            ],
            'gejala' => [
                [
                    'id' => 2,
                    'kode' => 'G02',
                    'nama' => 'Kurang Konsentrasi',
                    'kategori' => 'kognitif',
                    'bobot' => 0.5
                ]
            ],
            'aturan' => [
                [
                    'id' => 2,
                    'kode' => 'R02',
                    'diagnosa_id' => 2,
                    'cf_pakar' => 0.7,
                    'prioritas' => 3,
                    'min_threshold' => 0.1,
                    'is_active' => true
                ]
            ],
            'aturan_gejala' => [
                [
                    'aturan_id' => 2,
                    'gejala_id' => 2,
                    'bobot_pakar' => 0.6
                ]
            ]
        ];

        $jsonFile = UploadedFile::fake()->createWithContent(
            'backup.json', 
            json_encode($backupData)
        );

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.knowledge.restore'), [
                'backup_file' => $jsonFile
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database matches the restored items exactly and old seeded items are truncated
        $this->assertDatabaseHas('diagnosa', ['kode' => 'D02', 'nama' => 'Mild Stress']);
        $this->assertDatabaseMissing('diagnosa', ['kode' => 'D01']);

        $this->assertDatabaseHas('gejala', ['kode' => 'G02', 'nama' => 'Kurang Konsentrasi']);
        $this->assertDatabaseMissing('gejala', ['kode' => 'G01']);

        // Assert cache is flushed
        $this->assertNull(Cache::get('aturan_active_rules'));
    }
}
