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
        DB::table('aturan_gejala')->truncate();
        Aturan::truncate();
        Gejala::truncate();
        Diagnosa::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ────────────────────────────────────────────────────────────────
        // 1. Seed Diagnosa (4 Level Burnout)
        //    Referensi: Maslach, C., Jackson, S.E. & Leiter, M.P. (1996).
        //    Maslach Burnout Inventory Manual (3rd ed.). CPP.
        // ────────────────────────────────────────────────────────────────
        $diagnosas = [
            [
                'kode'      => 'D01',
                'nama'      => 'Burnout Sangat Tinggi (Severe)',
                'tingkat'   => 'SANGAT TINGGI',
                'deskripsi' => 'Anda berada pada fase kritis burnout. Kelelahan fisik dan emosional sudah sangat parah, disertai sinisme tinggi dan hilangnya rasa kompetensi diri.',
                'saran'     => "Hubungi Psikolog: Segera jadwalkan sesi dengan psikolog atau psikiater profesional untuk evaluasi mendalam\nCuti Panjang: Ambil cuti minimal 2 minggu untuk pemulihan fisik dan mental\nPerubahan Radikal: Evaluasi ulang lingkungan kerja, beban tugas, dan pertimbangkan restrukturisasi peran\nDukungan Sosial: Terbuka kepada keluarga dan orang terdekat tentang kondisi Anda\nPantau Kesehatan: Lakukan pemeriksaan kesehatan fisik menyeluruh karena burnout berdampak pada kesehatan tubuh",
                'color'     => '#dc2626',
                'bg_light'  => '#fee2e2',
            ],
            [
                'kode'      => 'D02',
                'nama'      => 'Burnout Tinggi (High)',
                'tingkat'   => 'TINGGI',
                'deskripsi' => 'Anda mengalami gejala burnout yang signifikan. Pekerjaan terasa sangat membebani dan mulai berdampak buruk pada kesehatan serta hubungan sosial.',
                'saran'     => "Komunikasi Atasan: Diskusikan beban kerja dan batasan kemampuan Anda dengan supervisor secara terbuka\nPrioritaskan Istirahat: Pastikan tidur 7-8 jam per malam dan hindari lembur berlebihan\nTeknik Relaksasi: Terapkan meditasi, pernapasan dalam, atau yoga minimal 15 menit sehari\nBatasi Pekerjaan: Terapkan batas tegas antara waktu kerja dan waktu pribadi (work-life boundary)\nAktivitas Fisik: Mulai olahraga ringan 3x seminggu untuk membantu mengelola stres",
                'color'     => '#ea580c',
                'bg_light'  => '#fff7ed',
            ],
            [
                'kode'      => 'D03',
                'nama'      => 'Burnout Sedang (Moderate)',
                'tingkat'   => 'SEDANG',
                'deskripsi' => 'Muncul tanda-tanda awal kelelahan kronis. Anda mulai merasa jenuh namun masih bisa menjalankan rutinitas meski dengan usaha ekstra.',
                'saran'     => "Evaluasi Work-Life Balance: Tinjau kembali proporsi waktu kerja vs waktu pribadi dan buat penyesuaian\nHobi & Rekreasi: Tingkatkan frekuensi aktivitas yang menenangkan dan menyenangkan di luar kerja\nBatasi Jam Lembur: Hindari lembur lebih dari 2 jam per hari dan pastikan akhir pekan bebas kerja\nJalin Koneksi: Perkuat hubungan sosial dengan rekan kerja dan komunitas untuk dukungan emosional\nJadwal Rutin: Buat dan patuhi rutinitas harian yang seimbang antara produktivitas dan istirahat",
                'color'     => '#ca8a04',
                'bg_light'  => '#fefce8',
            ],
            [
                'kode'      => 'D04',
                'nama'      => 'Risiko Burnout Rendah (Normal/Mild)',
                'tingkat'   => 'RENDAH',
                'deskripsi' => 'Kondisi psikologis Anda cenderung stabil. Stres yang dirasakan masih dalam batas wajar dan bisa dikelola dengan baik.',
                'saran'     => "Pertahankan Rutinitas: Lanjutkan kebiasaan positif yang sudah terbentuk dan hindari perubahan drastis\nDeteksi Dini: Lakukan pemeriksaan burnout secara berkala (setiap 30 hari) untuk deteksi perubahan dini\nInvestasi Diri: Fokus pada pengembangan keterampilan dan keseimbangan hidup jangka panjang\nBagikan Pengalaman: Jadilah sumber dukungan bagi rekan kerja yang mungkin mengalami kesulitan\nJaga Kesehatan: Pertahankan pola tidur, makan, dan olahraga yang sudah baik",
                'color'     => '#16a34a',
                'bg_light'  => '#dcfce7',
            ],
        ];

        foreach ($diagnosas as $d) {
            Diagnosa::create($d);
        }

        // ────────────────────────────────────────────────────────────────
        // 2. Seed Gejala (12 Gejala Berbasis MBI)
        //    3 Dimensi: Emotional Exhaustion (EE), Depersonalization (DP),
        //    Reduced Personal Accomplishment (PA), + Fisik (F)
        //    Referensi: Maslach Burnout Inventory – General Survey (MBI-GS)
        // ────────────────────────────────────────────────────────────────
        $gejalas = [
            // Dimensi 1: Emotional Exhaustion (EE)
            ['kode' => 'G01', 'kategori' => 'emosional', 'nama' => 'Merasa terkuras secara emosional setelah seharian bekerja',            'bobot' => 0.80],
            ['kode' => 'G02', 'kategori' => 'emosional', 'nama' => 'Merasa lelah saat bangun pagi dan harus menghadapi hari kerja',         'bobot' => 0.70],
            ['kode' => 'G03', 'kategori' => 'emosional', 'nama' => 'Merasa bekerja dengan orang lain adalah beban yang berat',              'bobot' => 0.60],
            ['kode' => 'G04', 'kategori' => 'emosional', 'nama' => 'Mudah tersinggung atau marah karena hal-hal kecil di kantor',          'bobot' => 0.75],

            // Dimensi 2: Depersonalization / Cynicism (DP)
            ['kode' => 'G05', 'kategori' => 'perilaku',  'nama' => 'Menjadi lebih sinis terhadap rekan kerja atau klien',                  'bobot' => 0.90],
            ['kode' => 'G06', 'kategori' => 'perilaku',  'nama' => 'Merasa tidak peduli dengan apa yang terjadi pada rekan atau klien',    'bobot' => 0.85],
            ['kode' => 'G07', 'kategori' => 'perilaku',  'nama' => 'Ingin menjauh atau mengisolasi diri dari lingkungan kerja',            'bobot' => 0.70],

            // Dimensi 3: Reduced Personal Accomplishment (PA)
            ['kode' => 'G08', 'kategori' => 'kognitif',  'nama' => 'Merasa tidak memberikan dampak berarti melalui pekerjaan',             'bobot' => 0.65],
            ['kode' => 'G09', 'kategori' => 'kognitif',  'nama' => 'Sulit berkonsentrasi dan sering membuat kesalahan sepele',             'bobot' => 0.60],
            ['kode' => 'G10', 'kategori' => 'kognitif',  'nama' => 'Merasa tidak puas dengan hasil pekerjaan sendiri',                     'bobot' => 0.70],

            // Dimensi 4: Fisik (Physical Symptoms)
            ['kode' => 'G11', 'kategori' => 'fisik',     'nama' => 'Sering mengalami sakit kepala atau gangguan pencernaan tanpa sebab medis jelas', 'bobot' => 0.55],
            ['kode' => 'G12', 'kategori' => 'fisik',     'nama' => 'Gangguan tidur (insomnia atau selalu merasa mengantuk sepanjang hari)', 'bobot' => 0.60],
        ];

        foreach ($gejalas as $g) {
            Gejala::create($g);
        }

        // Helper closure
        $g = fn(string $kode) => Gejala::where('kode', $kode)->first()->id;
        $d = fn(string $kode) => Diagnosa::where('kode', $kode)->first()->id;

        // ────────────────────────────────────────────────────────────────
        // 3. Seed Aturan (8 Rules – semua 12 gejala ter-cover)
        //
        //    Formula CF: CF_final = avg(CF_user_i × bobot_pakar_i) × CF_pakar_rule
        //    Metode: Backward Chaining (goal → gejala)
        //
        //    D01 = Sangat Tinggi:  Rules R01, R02
        //    D02 = Tinggi:         Rules R03, R04
        //    D03 = Sedang:         Rules R05, R06
        //    D04 = Rendah:         Rules R07, R08
        // ────────────────────────────────────────────────────────────────

        // ── D01: Burnout Sangat Tinggi ──
        // R01: Kombinasi EE + DP ekstrem (gejala inti severe burnout)
        $r1 = Aturan::create(['kode' => 'R01', 'diagnosa_id' => $d('D01'), 'cf_pakar' => 0.95]);
        $r1->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.85],
            $g('G02') => ['bobot_pakar' => 0.80],
            $g('G05') => ['bobot_pakar' => 0.90],
            $g('G06') => ['bobot_pakar' => 0.90],
        ]);

        // R02: EE + Isolasi + Sinis (varian gejala perilaku berat)
        $r2 = Aturan::create(['kode' => 'R02', 'diagnosa_id' => $d('D01'), 'cf_pakar' => 0.90]);
        $r2->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.80],
            $g('G04') => ['bobot_pakar' => 0.75],
            $g('G07') => ['bobot_pakar' => 0.80],
            $g('G12') => ['bobot_pakar' => 0.70],
        ]);

        // ── D02: Burnout Tinggi ──
        // R03: EE + PA rendah + Kognitif terganggu
        $r3 = Aturan::create(['kode' => 'R03', 'diagnosa_id' => $d('D02'), 'cf_pakar' => 0.85]);
        $r3->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.75],
            $g('G03') => ['bobot_pakar' => 0.70],
            $g('G08') => ['bobot_pakar' => 0.80],
            $g('G09') => ['bobot_pakar' => 0.75],
        ]);

        // R04: DP + Isolasi + Fisik (burnout tinggi dgn manifestasi fisik)
        $r4 = Aturan::create(['kode' => 'R04', 'diagnosa_id' => $d('D02'), 'cf_pakar' => 0.80]);
        $r4->gejala()->attach([
            $g('G05') => ['bobot_pakar' => 0.80],
            $g('G06') => ['bobot_pakar' => 0.75],
            $g('G07') => ['bobot_pakar' => 0.75],
            $g('G11') => ['bobot_pakar' => 0.60],
        ]);

        // ── D03: Burnout Sedang ──
        // R05: EE ringan + Fisik (kelelahan dengan keluhan fisik awal)
        $r5 = Aturan::create(['kode' => 'R05', 'diagnosa_id' => $d('D03'), 'cf_pakar' => 0.75]);
        $r5->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.65],
            $g('G11') => ['bobot_pakar' => 0.65],
            $g('G12') => ['bobot_pakar' => 0.70],
        ]);

        // R06: PA rendah + Emosi tidak stabil (awal demoralisasi kerja)
        $r6 = Aturan::create(['kode' => 'R06', 'diagnosa_id' => $d('D03'), 'cf_pakar' => 0.70]);
        $r6->gejala()->attach([
            $g('G08') => ['bobot_pakar' => 0.60],
            $g('G09') => ['bobot_pakar' => 0.60],
            $g('G10') => ['bobot_pakar' => 0.65],
            $g('G04') => ['bobot_pakar' => 0.55],
        ]);

        // ── D04: Risiko Rendah / Normal ──
        // R07: Hanya PA rendah ringan (ketidakpuasan minor)
        $r7 = Aturan::create(['kode' => 'R07', 'diagnosa_id' => $d('D04'), 'cf_pakar' => 0.60]);
        $r7->gejala()->attach([
            $g('G04') => ['bobot_pakar' => 0.45],
            $g('G10') => ['bobot_pakar' => 0.55],
        ]);

        // R08: Hanya gejala fisik ringan tanpa komponen emosional/DP
        $r8 = Aturan::create(['kode' => 'R08', 'diagnosa_id' => $d('D04'), 'cf_pakar' => 0.55]);
        $r8->gejala()->attach([
            $g('G11') => ['bobot_pakar' => 0.40],
            $g('G12') => ['bobot_pakar' => 0.45],
            $g('G02') => ['bobot_pakar' => 0.35],
        ]);
    }
}
