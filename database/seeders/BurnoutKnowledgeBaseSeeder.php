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
        // ────────────────────────────────────────────────────────────────
        $diagnosas = [
            [
                'kode'      => 'D01',
                'nama'      => 'Burnout Sangat Tinggi (Severe)',
                'tingkat'   => 'SANGAT TINGGI',
                'deskripsi' => 'Kondisi kelelahan fisik, emosional, dan mental yang ekstrem. Mengindikasikan kegagalan pertahanan stres total di mana karyawan kehilangan fungsi produktivitas dan kepedulian profesional.',
                'saran'     => "Evaluasi Klinis: Segera lakukan pemeriksaan intensif ke psikolog klinis atau psikiater profesional.\nCuti Pemulihan Medis: Ambil cuti pemulihan minimal 2-4 minggu secara mutlak untuk detoksifikasi stres.\nRestrukturisasi Peran: Ajukan negosiasi perubahan tugas atau rotasi divisi kerja.\nIntervensi Sosial: Terbuka pada keluarga terdekat dan bangun support system yang kuat.\nPantau Gejala Fisik: Lakukan general check-up karena kondisi ini dapat memicu sindrom psikosomatik berat.",
                'color'     => '#dc2626',
                'bg_light'  => '#fee2e2',
            ],
            [
                'kode'      => 'D02',
                'nama'      => 'Burnout Tinggi (High)',
                'tingkat'   => 'TINGGI',
                'deskripsi' => 'Gejala kelelahan kronis telah memengaruhi efisiensi kerja sehari-hari. Mulai muncul perasaan sinis, dingin, dan tidak peduli terhadap pekerjaan atau rekan tim.',
                'saran'     => "Konseling HRD: Jadwalkan sesi konseling difasilitasi oleh tim HRD/psikolog perusahaan.\nBatasan Jam Kerja: Terapkan batas kerja tegas, hindari lembur, dan matikan perangkat kerja setelah jam operasional.\nTeknik Manajemen Stres: Lakukan relaksasi kognitif (MBSR - Mindful Stress Reduction) 20 menit per hari.\nDelegasi Tugas: Delegasikan tugas non-prioritas yang terlalu membebani kesehatan mental Anda.\nOlahraga Teratur: Lakukan aktivitas fisik aerobik minimal 3 kali seminggu untuk memicu endorfin.",
                'color'     => '#ea580c',
                'bg_light'  => '#fff7ed',
            ],
            [
                'kode'      => 'D03',
                'nama'      => 'Burnout Sedang (Moderate)',
                'tingkat'   => 'SEDANG',
                'deskripsi' => 'Kondisi kejenuhan awal. Karyawan mulai merasakan kelelahan di pagi hari, performa sedikit menurun, namun masih mampu mempertahankan kendali tugas.',
                'saran'     => "Evaluasi Gaya Hidup: Tinjau proporsi waktu kerja, istirahat, hobi, dan lakukan perbaikan kualitas tidur.\nRe-kreasi & Hobi: Jadwalkan aktivitas rekreasi atau hobi yang menenangkan di akhir pekan.\nManajemen Beban Kerja: Gunakan matriks Eisenhower untuk memisahkan tugas mendesak dan penting.\nBincang Rekan Kerja: Diskusikan hambatan kerja sehari-hari untuk mengurangi beban emosional sendiri.\nDetoks Digital: Batasi penggunaan media sosial atau berita terkait pekerjaan di malam hari.",
                'color'     => '#ca8a04',
                'bg_light'  => '#fefce8',
            ],
            [
                'kode'      => 'D04',
                'nama'      => 'Risiko Burnout Rendah (Normal/Mild)',
                'tingkat'   => 'RENDAH',
                'deskripsi' => 'Stabilitas emosional dan tingkat kompetensi diri berada pada rentang sangat baik. Stres harian dikelola secara sehat.',
                'saran'     => "Pertahankan Kebiasaan: Lanjutkan kebiasaan manajemen waktu, istirahat, dan olahraga yang sudah berjalan baik.\nDeteksi Dini Rutin: Lakukan pengujian skrining burnout berkala (setiap 30-45 hari).\nInvestasi Keterampilan: Ikuti pelatihan pengembangan kompetensi diri untuk memperkuat ketahanan karir.\nBerbagi Kebaikan: Berikan dukungan sosial serta aura positif kepada rekan kerja di sekitar Anda.\nGaya Hidup Stres: Tetap konsumsi makanan bernutrisi dan cukupi kebutuhan hidrasi harian.",
                'color'     => '#16a34a',
                'bg_light'  => '#dcfce7',
            ],
        ];

        foreach ($diagnosas as $d) {
            Diagnosa::create($d);
        }

        // ────────────────────────────────────────────────────────────────
        // 2. Seed Gejala (20 Gejala Ilmiah Komprehensif MBI-GS)
        // ────────────────────────────────────────────────────────────────
        $gejalas = [
            // Dimensi 1: Emotional Exhaustion (EE) - emosional
            ['kode' => 'G01', 'kategori' => 'emosional', 'nama' => 'Merasa terkuras habis secara fisik dan emosional setelah seharian bekerja', 'bobot' => 0.85],
            ['kode' => 'G02', 'kategori' => 'emosional', 'nama' => 'Merasa letih dan tidak berenergi saat bangun tidur di pagi hari untuk bekerja', 'bobot' => 0.75],
            ['kode' => 'G03', 'kategori' => 'emosional', 'nama' => 'Merasa tegang dan tertekan akibat beban tuntutan pekerjaan yang terus-menerus', 'bobot' => 0.70],
            ['kode' => 'G04', 'kategori' => 'emosional', 'nama' => 'Sangat mudah tersinggung, frustrasi, atau marah pada hal-hal kecil di kantor', 'bobot' => 0.80],
            ['kode' => 'G13', 'kategori' => 'emosional', 'nama' => 'Merasa hampa secara emosional dan tidak mampu memberikan empati lagi pada rekan kerja atau keluarga', 'bobot' => 0.85],
            ['kode' => 'G14', 'kategori' => 'emosional', 'nama' => 'Mengalami keputusasaan mendalam mengenai masa depan karier dan perkembangan profesional Anda', 'bobot' => 0.80],

            // Dimensi 2: Depersonalization / Cynicism (DP) - perilaku
            ['kode' => 'G05', 'kategori' => 'perilaku',  'nama' => 'Menjadi semakin sinis, dingin, dan skeptis terhadap nilai kegunaan pekerjaan Anda', 'bobot' => 0.90],
            ['kode' => 'G06', 'kategori' => 'perilaku',  'nama' => 'Merasa tidak peduli lagi dengan nasib rekan kerja, klien, atau perusahaan tempat bekerja', 'bobot' => 0.85],
            ['kode' => 'G07', 'kategori' => 'perilaku',  'nama' => 'Menjauhkan diri secara sosial dan cenderung mengisolasi diri dari lingkungan kerja', 'bobot' => 0.75],
            ['kode' => 'G15', 'kategori' => 'perilaku',  'nama' => 'Menunjukkan sikap ketus, sinis, atau defensif ketika diajak berdiskusi tentang tugas baru', 'bobot' => 0.80],
            ['kode' => 'G16', 'kategori' => 'perilaku',  'nama' => 'Sering menunda-nunda pekerjaan (prokrastinasi ekstrem) karena merasa enggan berinteraksi dengan tugas tersebut', 'bobot' => 0.75],

            // Dimensi 3: Reduced Personal Accomplishment (PA) - kognitif
            ['kode' => 'G08', 'kategori' => 'kognitif',  'nama' => 'Merasa pekerjaan yang dilakukan sia-sia dan tidak memberikan kontribusi nyata bagi tim', 'bobot' => 0.70],
            ['kode' => 'G09', 'kategori' => 'kognitif',  'nama' => 'Sulit berkonsentrasi pada tugas penting dan sering melakukan kesalahan teknis', 'bobot' => 0.65],
            ['kode' => 'G10', 'kategori' => 'kognitif',  'nama' => 'Mengalami penurunan kepercayaan diri dan ketidakpuasan tinggi terhadap hasil kerja sendiri', 'bobot' => 0.75],
            ['kode' => 'G17', 'kategori' => 'kognitif',  'nama' => 'Sering merasa tidak berdaya (helplessness) saat menghadapi tantangan kerja yang biasa dihadapi sebelumnya', 'bobot' => 0.70],
            ['kode' => 'G18', 'kategori' => 'kognitif',  'nama' => 'Merasa tidak ada apresiasi atau pengakuan sosial atas upaya maksimal yang telah didedikasikan', 'bobot' => 0.65],

            // Dimensi 4: Fisik (Physical Manifestations) - fisik
            ['kode' => 'G11', 'kategori' => 'fisik',     'nama' => 'Sering mengalami gangguan pencernaan atau sakit kepala kronis tanpa diagnosis medis lain', 'bobot' => 0.60],
            ['kode' => 'G12', 'kategori' => 'fisik',     'nama' => 'Mengalami insomnia berat atau selalu merasa mengantuk berat sepanjang hari kerja', 'bobot' => 0.65],
            ['kode' => 'G19', 'kategori' => 'fisik',     'nama' => 'Merasa leher, pundak, atau otot punggung kaku dan nyeri berkepanjangan akibat ketegangan mental kerja', 'bobot' => 0.70],
            ['kode' => 'G20', 'kategori' => 'fisik',     'nama' => 'Mengalami perubahan nafsu makan drastis (sangat menurun atau makan berlebihan secara emosional)', 'bobot' => 0.60],
        ];

        foreach ($gejalas as $g) {
            Gejala::create($g);
        }

        // Helper closures
        $g = fn(string $kode) => Gejala::where('kode', $kode)->first()->id;
        $d = fn(string $kode) => Diagnosa::where('kode', $kode)->first()->id;

        // ────────────────────────────────────────────────────────────────
        // 3. Seed Aturan Pakar (15 Rules Terstruktur, Prioritas & Validasi)
        // ────────────────────────────────────────────────────────────────

        // ── D01: Burnout Sangat Tinggi (Severe) ──
        // R01: Kombinasi Kelelahan Emosional (EE) Berat + Depersonalisasi (DP) Ekstrem (Kondisi Kritis)
        // Prioritas Tertinggi = 15
        $r1 = Aturan::create([
            'kode' => 'R01', 
            'diagnosa_id' => $d('D01'), 
            'cf_pakar' => 0.98,
            'prioritas' => 15,
            'is_active' => true,
            'min_threshold' => 0.35,
            'deskripsi' => 'Aturan kritis untuk mendeteksi sindrom burnout akut di mana pilar kelelahan emosional dan sinisme kerja (depersonalisasi) berada pada level paling ekstrim secara bersamaan.'
        ]);
        $r1->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.90],
            $g('G02') => ['bobot_pakar' => 0.85],
            $g('G05') => ['bobot_pakar' => 0.95],
            $g('G06') => ['bobot_pakar' => 0.90],
        ]);

        // R02: Kelelahan Emosional + Isolasi Kerja + Gangguan Insomnia Kronis
        // Prioritas = 14
        $r2 = Aturan::create([
            'kode' => 'R02', 
            'diagnosa_id' => $d('D01'), 
            'cf_pakar' => 0.92,
            'prioritas' => 14,
            'is_active' => true,
            'min_threshold' => 0.30,
            'deskripsi' => 'Aturan pendukung burnout berat yang berfokus pada kelelahan emosional yang meluas ke penarikan diri sosial (isolasi) dan diiringi kerusakan siklus tidur psikosomatik.'
        ]);
        $r2->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.85],
            $g('G04') => ['bobot_pakar' => 0.80],
            $g('G07') => ['bobot_pakar' => 0.85],
            $g('G12') => ['bobot_pakar' => 0.75],
        ]);

        // R09: Kelelahan Emosional Berat + Hampa Emosi + Ketidakberdayaan Kognitif (Kombinasi Baru)
        // Prioritas = 13
        $r9 = Aturan::create([
            'kode' => 'R09', 
            'diagnosa_id' => $d('D01'), 
            'cf_pakar' => 0.95,
            'prioritas' => 13,
            'is_active' => true,
            'min_threshold' => 0.30,
            'deskripsi' => 'Aturan diagnosis burnout tingkat lanjut yang dipicu oleh kehampaan emosional total serta rasa ketidakberdayaan kognitif yang meluas.'
        ]);
        $r9->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.85],
            $g('G13') => ['bobot_pakar' => 0.90],
            $g('G17') => ['bobot_pakar' => 0.80],
        ]);

        // R12: Apatis Sosial + Keputusasaan Karier + Insomnia Kronis
        // Prioritas = 12
        $r12 = Aturan::create([
            'kode' => 'R12', 
            'diagnosa_id' => $d('D01'), 
            'cf_pakar' => 0.94,
            'prioritas' => 12,
            'is_active' => true,
            'min_threshold' => 0.30,
            'deskripsi' => 'Aturan deteksi keputusasaan kerja kronis dengan manifestasi gangguan tidur berat serta hilangnya rasa peduli sosial.'
        ]);
        $r12->gejala()->attach([
            $g('G06') => ['bobot_pakar' => 0.85],
            $g('G14') => ['bobot_pakar' => 0.90],
            $g('G12') => ['bobot_pakar' => 0.80],
        ]);

        // ── D02: Burnout Tinggi (High) ──
        // R03: Kelelahan Emosional Terkuras + Penurunan Kompetensi Kognitif Kerja (PA Rendah)
        // Prioritas = 11
        $r3 = Aturan::create([
            'kode' => 'R03', 
            'diagnosa_id' => $d('D02'), 
            'cf_pakar' => 0.88,
            'prioritas' => 11,
            'is_active' => true,
            'min_threshold' => 0.25,
            'deskripsi' => 'Aturan burnout tinggi yang mengevaluasi hubungan antara stamina emosional yang kering dengan penurunan tajam performa kognitif kerja sehari-hari.'
        ]);
        $r3->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.80],
            $g('G03') => ['bobot_pakar' => 0.75],
            $g('G08') => ['bobot_pakar' => 0.85],
            $g('G09') => ['bobot_pakar' => 0.80],
        ]);

        // R04: Sinisme Kerja (DP) + Mengisolasi Diri + Somatik Fisik (Sakit Kepala/Pencernaan)
        // Prioritas = 10
        $r4 = Aturan::create([
            'kode' => 'R04', 
            'diagnosa_id' => $d('D02'), 
            'cf_pakar' => 0.84,
            'prioritas' => 10,
            'is_active' => true,
            'min_threshold' => 0.25,
            'deskripsi' => 'Aturan burnout tinggi dengan manifestasi perilaku dingin (sinis) disertai keluhan fisik akibat stres psikologis kronis.'
        ]);
        $r4->gejala()->attach([
            $g('G05') => ['bobot_pakar' => 0.85],
            $g('G06') => ['bobot_pakar' => 0.80],
            $g('G07') => ['bobot_pakar' => 0.80],
            $g('G11') => ['bobot_pakar' => 0.70],
        ]);

        // R10: Keputusasaan Emosional + Prokrastinasi Ekstrem + Nyeri Otot Somatik
        // Prioritas = 9
        $r10 = Aturan::create([
            'kode' => 'R10', 
            'diagnosa_id' => $d('D02'), 
            'cf_pakar' => 0.86,
            'prioritas' => 9,
            'is_active' => true,
            'min_threshold' => 0.25,
            'deskripsi' => 'Mengevaluasi kondisi burnout tinggi di mana karyawan menunda pekerjaan secara ekstrim dengan ketegangan fisik yang kaku.'
        ]);
        $r10->gejala()->attach([
            $g('G14') => ['bobot_pakar' => 0.85],
            $g('G16') => ['bobot_pakar' => 0.80],
            $g('G19') => ['bobot_pakar' => 0.80],
        ]);

        // ── D03: Burnout Sedang (Moderate) ──
        // R05: Kelelahan Emosional Ringan + Keluhan Fisik Awal Stres
        // Prioritas = 8
        $r5 = Aturan::create([
            'kode' => 'R05', 
            'diagnosa_id' => $d('D03'), 
            'cf_pakar' => 0.78,
            'prioritas' => 8,
            'is_active' => true,
            'min_threshold' => 0.20,
            'deskripsi' => 'Aturan burnout sedang untuk mendeteksi fase kelelahan awal yang mulai termanifestasi ke dalam gangguan fisik ringan di luar jam kerja.'
        ]);
        $r5->gejala()->attach([
            $g('G01') => ['bobot_pakar' => 0.70],
            $g('G11') => ['bobot_pakar' => 0.70],
            $g('G12') => ['bobot_pakar' => 0.75],
        ]);

        // R06: Penurunan Efisiensi Diri (PA Rendah) + Hilangnya Rasa Puas Berprestasi
        // Prioritas = 7
        $r6 = Aturan::create([
            'kode' => 'R06', 
            'diagnosa_id' => $d('D03'), 
            'cf_pakar' => 0.74,
            'prioritas' => 7,
            'is_active' => true,
            'min_threshold' => 0.20,
            'deskripsi' => 'Aturan burnout sedang yang didominasi oleh perasaan demoralisasi kerja, di mana karyawan merasa kontribusinya menurun drastis.'
        ]);
        $r6->gejala()->attach([
            $g('G08') => ['bobot_pakar' => 0.65],
            $g('G09') => ['bobot_pakar' => 0.65],
            $g('G10') => ['bobot_pakar' => 0.70],
            $g('G04') => ['bobot_pakar' => 0.60],
        ]);

        // R11: Sikap Defensif Perilaku + Perubahan Nafsu Makan + Penurunan Kepercayaan Diri
        // Prioritas = 6
        $r11 = Aturan::create([
            'kode' => 'R11', 
            'diagnosa_id' => $d('D03'), 
            'cf_pakar' => 0.78,
            'prioritas' => 6,
            'is_active' => true,
            'min_threshold' => 0.20,
            'deskripsi' => 'Aturan deteksi stres emosional yang ditunjukkan lewat defensif perilaku dan ketidakseimbangan nutrisi harian.'
        ]);
        $r11->gejala()->attach([
            $g('G15') => ['bobot_pakar' => 0.80],
            $g('G20') => ['bobot_pakar' => 0.75],
            $g('G10') => ['bobot_pakar' => 0.70],
        ]);

        // R13: Beban Tuntutan Kerja + Kurang Apresiasi Kognitif
        // Prioritas = 5
        $r13 = Aturan::create([
            'kode' => 'R13', 
            'diagnosa_id' => $d('D03'), 
            'cf_pakar' => 0.70,
            'prioritas' => 5,
            'is_active' => true,
            'min_threshold' => 0.15,
            'deskripsi' => 'Aturan moderat untuk memantau stres akibat tuntutan kerja tinggi tanpa adanya pengakuan atau apresiasi yang cukup.'
        ]);
        $r13->gejala()->attach([
            $g('G03') => ['bobot_pakar' => 0.75],
            $g('G18') => ['bobot_pakar' => 0.70],
        ]);

        // R14: Mudah Tersinggung Emosional + Nyeri Otot + Nafsu Makan Terganggu
        // Prioritas = 4
        $r14 = Aturan::create([
            'kode' => 'R14', 
            'diagnosa_id' => $d('D03'), 
            'cf_pakar' => 0.72,
            'prioritas' => 4,
            'is_active' => true,
            'min_threshold' => 0.15,
            'deskripsi' => 'Mengidentifikasi stres kognitif yang memicu emosi labil beriringan kaku fisik somatic.'
        ]);
        $r14->gejala()->attach([
            $g('G04') => ['bobot_pakar' => 0.75],
            $g('G19') => ['bobot_pakar' => 0.70],
            $g('G20') => ['bobot_pakar' => 0.65],
        ]);

        // ── D04: Risiko Rendah / Normal (Mild) ──
        // R07: Penurunan Kepuasan Kerja Minor Tanpa Kelelahan
        // Prioritas = 3
        $r7 = Aturan::create([
            'kode' => 'R07', 
            'diagnosa_id' => $d('D04'), 
            'cf_pakar' => 0.60,
            'prioritas' => 3,
            'is_active' => true,
            'min_threshold' => 0.10,
            'deskripsi' => 'Aturan normal untuk mengevaluasi ketidakpuasan kerja ringan sesaat yang bersifat umum dan tidak berbahaya.'
        ]);
        $r7->gejala()->attach([
            $g('G04') => ['bobot_pakar' => 0.50],
            $g('G10') => ['bobot_pakar' => 0.60],
        ]);

        // R08: Hanya Kelelahan Letih Pagi Biasa Akibat Kurang Istirahat
        // Prioritas = 2
        $r8 = Aturan::create([
            'kode' => 'R08', 
            'diagnosa_id' => $d('D04'), 
            'cf_pakar' => 0.55,
            'prioritas' => 2,
            'is_active' => true,
            'min_threshold' => 0.10,
            'deskripsi' => 'Aturan normal untuk mendeteksi rasa letih pagi biasa (burnout rendah) yang murni dipicu oleh siklus istirahat jangka pendek.'
        ]);
        $r8->gejala()->attach([
            $g('G11') => ['bobot_pakar' => 0.45],
            $g('G12') => ['bobot_pakar' => 0.50],
            $g('G02') => ['bobot_pakar' => 0.40],
        ]);

        // R15: Penundaan Kerja Minor + Ketegangan Otot Ringan
        // Prioritas = 1
        $r15 = Aturan::create([
            'kode' => 'R15', 
            'diagnosa_id' => $d('D04'), 
            'cf_pakar' => 0.50,
            'prioritas' => 1,
            'is_active' => true,
            'min_threshold' => 0.05,
            'deskripsi' => 'Kondisi stres normal sangat ringan yang termanifestasi ke penundaan minor sesaat.'
        ]);
        $r15->gejala()->attach([
            $g('G16') => ['bobot_pakar' => 0.50],
            $g('G19') => ['bobot_pakar' => 0.45],
        ]);
    }
}
