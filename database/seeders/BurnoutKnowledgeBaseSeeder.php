<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Aturan;
use Illuminate\Support\Facades\DB;

class BurnoutKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama untuk integritas MBI
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Aturan::truncate();
        Gejala::truncate();
        Diagnosa::truncate();
        DB::table('aturan_gejala')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Diagnosa (Tingkat Burnout)
        $diagnosas = [
            [
                'kode' => 'D01',
                'nama' => 'Burnout Sangat Tinggi (Severe)',
                'tingkat' => 'SANGAT TINGGI',
                'deskripsi' => 'Anda berada pada fase kritis burnout. Kelelahan fisik dan emosional sudah sangat parah, disertai sinisme tinggi dan hilangnya rasa kompetensi diri.',
                'saran' => 'Segera hubungi profesional kesehatan mental (Psikolog/Psikiater). Ambil cuti panjang dan lakukan perubahan radikal pada gaya hidup/pekerjaan.',
                'color' => '#dc2626', // Red
                'bg_light' => '#fee2e2'
            ],
            [
                'kode' => 'D02',
                'nama' => 'Burnout Tinggi (High)',
                'tingkat' => 'TINGGI',
                'deskripsi' => 'Anda mengalami gejala burnout yang signifikan. Pekerjaan terasa sangat membebani dan mulai berdampak buruk pada kesehatan serta hubungan sosial.',
                'saran' => 'Lakukan intervensi segera. Komunikasikan beban kerja dengan atasan, prioritaskan istirahat, dan mulai terapkan teknik manajemen stres.',
                'color' => '#ea580c', // Orange
                'bg_light' => '#ffeb3b20'
            ],
            [
                'kode' => 'D03',
                'nama' => 'Burnout Sedang (Moderate)',
                'tingkat' => 'SEDANG',
                'deskripsi' => 'Muncul tanda-tanda awal kelelahan kronis. Anda mulai merasa jenuh namun masih bisa menjalankan rutinitas meski dengan usaha ekstra.',
                'saran' => 'Evaluasi kembali keseimbangan kerja-hidup. Tingkatkan aktivitas yang menenangkan (hobi, olahraga) dan batasi jam kerja lembur.',
                'color' => '#ca8a04', // Yellow/Gold
                'bg_light' => '#fefce8'
            ],
            [
                'kode' => 'D04',
                'nama' => 'Risiko Burnout Rendah (Normal/Mild)',
                'tingkat' => 'RENDAH',
                'deskripsi' => 'Kondisi psikologis Anda cenderung stabil. Stres yang dirasakan masih dalam batas wajar dan bisa dikelola dengan baik.',
                'saran' => 'Pertahankan rutinitas positif Anda. Tetap waspada terhadap tanda-tanda stres agar tidak berkembang menjadi lebih serius.',
                'color' => '#16a34a', // Green
                'bg_light' => '#dcfce7'
            ],
        ];

        foreach ($diagnosas as $d) {
            Diagnosa::create($d);
        }

        // 2. Seed Gejala (MBI-Based)
        $gejalas = [
            // Emosional Exhaustion
            ['kode' => 'G01', 'kategori' => 'emosional', 'nama' => 'Merasa terkuras secara emosional setelah seharian bekerja', 'bobot' => 0.8],
            ['kode' => 'G02', 'kategori' => 'emosional', 'nama' => 'Merasa lelah saat bangun pagi dan harus menghadapi hari kerja', 'bobot' => 0.7],
            ['kode' => 'G03', 'kategori' => 'emosional', 'nama' => 'Merasa bekerja dengan orang lain adalah beban berat', 'bobot' => 0.6],
            ['kode' => 'G04', 'kategori' => 'emosional', 'nama' => 'Mudah tersinggung atau marah karena hal-hal kecil di kantor', 'bobot' => 0.75],
            
            // Depersonalization (Cynicism)
            ['kode' => 'G05', 'kategori' => 'perilaku', 'nama' => 'Menjadi lebih sinis terhadap rekan kerja atau klien', 'bobot' => 0.9],
            ['kode' => 'G06', 'kategori' => 'perilaku', 'nama' => 'Merasa tidak peduli dengan apa yang terjadi pada rekan/klien', 'bobot' => 0.85],
            ['kode' => 'G07', 'kategori' => 'perilaku', 'nama' => 'Ingin menjauh/mengisolasi diri dari lingkungan kerja', 'bobot' => 0.7],
            
            // Reduced Personal Accomplishment
            ['kode' => 'G08', 'kategori' => 'kognitif', 'nama' => 'Merasa tidak memberikan dampak berarti melalui pekerjaan', 'bobot' => 0.65],
            ['kode' => 'G09', 'kategori' => 'kognitif', 'nama' => 'Sulit berkonsentrasi dan sering membuat kesalahan sepele', 'bobot' => 0.6],
            ['kode' => 'G10', 'kategori' => 'kognitif', 'nama' => 'Merasa tidak puas dengan hasil pekerjaan sendiri', 'bobot' => 0.7],
            
            // Fisik
            ['kode' => 'G11', 'kategori' => 'fisik', 'nama' => 'Sering mengalami sakit kepala atau gangguan pencernaan tanpa sebab medis jelas', 'bobot' => 0.55],
            ['kode' => 'G12', 'kategori' => 'fisik', 'nama' => 'Gangguan tidur (insomnia atau selalu merasa mengantuk)', 'bobot' => 0.6],
        ];

        foreach ($gejalas as $g) {
            Gejala::create($g);
        }

        // 3. Seed Aturan (Rules)
        // Rule: If G01, G02, G05, G06 -> D01 (Severe)
        $r1 = Aturan::create(['kode' => 'R01', 'diagnosa_id' => Diagnosa::where('kode', 'D01')->first()->id, 'cf_pakar' => 0.95]);
        $r1->gejala()->attach([
            Gejala::where('kode', 'G01')->first()->id => ['bobot_pakar' => 0.8],
            Gejala::where('kode', 'G02')->first()->id => ['bobot_pakar' => 0.8],
            Gejala::where('kode', 'G05')->first()->id => ['bobot_pakar' => 0.9],
            Gejala::where('kode', 'G06')->first()->id => ['bobot_pakar' => 0.9],
        ]);

        // Rule: If G01, G03, G08, G09 -> D02 (High)
        $r2 = Aturan::create(['kode' => 'R02', 'diagnosa_id' => Diagnosa::where('kode', 'D02')->first()->id, 'cf_pakar' => 0.85]);
        $r2->gejala()->attach([
            Gejala::where('kode', 'G01')->first()->id => ['bobot_pakar' => 0.7],
            Gejala::where('kode', 'G03')->first()->id => ['bobot_pakar' => 0.8],
            Gejala::where('kode', 'G08')->first()->id => ['bobot_pakar' => 0.8],
            Gejala::where('kode', 'G09')->first()->id => ['bobot_pakar' => 0.7],
        ]);

        // Rule: If G01, G11, G12 -> D03 (Moderate)
        $r3 = Aturan::create(['kode' => 'R03', 'diagnosa_id' => Diagnosa::where('kode', 'D03')->first()->id, 'cf_pakar' => 0.75]);
        $r3->gejala()->attach([
            Gejala::where('kode', 'G01')->first()->id => ['bobot_pakar' => 0.6],
            Gejala::where('kode', 'G11')->first()->id => ['bobot_pakar' => 0.7],
            Gejala::where('kode', 'G12')->first()->id => ['bobot_pakar' => 0.7],
        ]);
        
        // Rule: G04, G10 -> D04 (Low)
        $r4 = Aturan::create(['kode' => 'R04', 'diagnosa_id' => Diagnosa::where('kode', 'D04')->first()->id, 'cf_pakar' => 0.6]);
        $r4->gejala()->attach([
            Gejala::where('kode', 'G04')->first()->id => ['bobot_pakar' => 0.5],
            Gejala::where('kode', 'G10')->first()->id => ['bobot_pakar' => 0.6],
        ]);
    }
}
