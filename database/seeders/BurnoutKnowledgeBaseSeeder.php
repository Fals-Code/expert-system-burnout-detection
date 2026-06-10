<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BurnoutKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ruleCreatedAt = '2026-05-18 22:54:01';
        $ruleCreatedAtSecond = '2026-05-18 22:54:02';
        $ruleUpdatedAt = '2026-06-10 21:10:00';

        Schema::disableForeignKeyConstraints();
        DB::table('aturan_gejala')->truncate();
        DB::table('aturan')->truncate();
        DB::table('gejala')->truncate();
        DB::table('diagnosa')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('diagnosa')->insert([
            [
                'id' => 1,
                'kode' => 'D01',
                'nama' => 'Tidak Burnout (Healthy State)',
                'tingkat' => 'TIDAK BURNOUT',
                'deskripsi' => 'Karyawan berada dalam kondisi kesehatan mental dan fisik yang prima. Memiliki keseimbangan kerja (work-life balance) yang baik, motivasi tinggi, dan fungsi kognitif yang bekerja secara optimal.',
                'saran' => 'Pertahankan pola kerja saat ini, lakukan evaluasi berkala, dan bagikan tips manajemen stres positif kepada rekan kerja lainnya.',
                'color' => '#16a34a',
                'bg_light' => '#f0fdf4',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'kode' => 'D02',
                'nama' => 'Burnout Tinggi',
                'tingkat' => 'TINGGI',
                'deskripsi' => 'Gejala burnout kuat dan telah memengaruhi efisiensi kerja sehari-hari. Mulai muncul kelelahan emosional, sinisme kerja, gangguan fisik, atau penurunan performa yang perlu segera ditindaklanjuti.',
                'saran' => "Jadwalkan konseling dengan HRD atau psikolog perusahaan. Kurangi beban kerja non-prioritas, batasi lembur, terapkan jeda pemulihan, dan evaluasi ulang distribusi tugas bersama atasan langsung.",
                'color' => '#dc2626',
                'bg_light' => '#fef2f2',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'kode' => 'D03',
                'nama' => 'Burnout Sedang',
                'tingkat' => 'SEDANG',
                'deskripsi' => 'Kondisi kejenuhan mulai terlihat. Karyawan masih mampu menjalankan tugas, tetapi sudah muncul tanda kelelahan, tekanan emosional, atau penurunan kepuasan kerja.',
                'saran' => "Evaluasi pola kerja dan istirahat. Gunakan manajemen prioritas, komunikasikan hambatan kerja, jadwalkan aktivitas pemulihan, dan lakukan pemantauan berkala sebelum kondisi meningkat.",
                'color' => '#f97316',
                'bg_light' => '#fff7ed',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'kode' => 'D04',
                'nama' => 'Burnout Rendah',
                'tingkat' => 'RENDAH',
                'deskripsi' => 'Terdapat indikasi stres kerja ringan atau ketidaknyamanan sesaat yang masih berada pada batas wajar dan belum menunjukkan pola burnout serius.',
                'saran' => "Pertahankan kebiasaan kerja sehat, cukup istirahat, kelola waktu kerja, dan lakukan evaluasi mandiri secara berkala agar kondisi tetap stabil.",
                'color' => '#eab308',
                'bg_light' => '#fefce8',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);

        DB::table('gejala')->insert([
            ['id' => 1, 'kode' => 'G01', 'kategori' => 'emosional', 'nama' => 'Merasa terkuras habis secara fisik dan emosional setelah seharian bekerja', 'bobot' => 0.85, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'kode' => 'G02', 'kategori' => 'emosional', 'nama' => 'Merasa letih dan tidak berenergi saat bangun tidur di pagi hari untuk bekerja', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'kode' => 'G03', 'kategori' => 'emosional', 'nama' => 'Merasa tegang dan tertekan akibat beban tuntutan pekerjaan yang terus-menerus', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'kode' => 'G04', 'kategori' => 'emosional', 'nama' => 'Sangat mudah tersinggung, frustrasi, atau marah pada hal-hal kecil di kantor', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'kode' => 'G13', 'kategori' => 'emosional', 'nama' => 'Merasa hampa secara emosional dan tidak mampu memberikan empati lagi pada rekan kerja atau keluarga', 'bobot' => 0.85, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'kode' => 'G14', 'kategori' => 'emosional', 'nama' => 'Mengalami keputusasaan mendalam mengenai masa depan karier dan perkembangan profesional Anda', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'kode' => 'G05', 'kategori' => 'perilaku', 'nama' => 'Menjadi semakin sinis, dingin, dan skeptis terhadap nilai kegunaan pekerjaan Anda', 'bobot' => 0.90, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'kode' => 'G06', 'kategori' => 'perilaku', 'nama' => 'Merasa tidak peduli lagi dengan nasib rekan kerja, klien, atau perusahaan tempat bekerja', 'bobot' => 0.85, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 9, 'kode' => 'G07', 'kategori' => 'perilaku', 'nama' => 'Menjauhkan diri secara sosial dan cenderung mengisolasi diri dari lingkungan kerja', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 10, 'kode' => 'G15', 'kategori' => 'perilaku', 'nama' => 'Menunjukkan sikap ketus, sinis, atau defensif ketika diajak berdiskusi tentang tugas baru', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 11, 'kode' => 'G16', 'kategori' => 'perilaku', 'nama' => 'Sering menunda-nunda pekerjaan (prokrastinasi ekstrem) karena merasa enggan berinteraksi dengan tugas tersebut', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 12, 'kode' => 'G08', 'kategori' => 'kognitif', 'nama' => 'Merasa pekerjaan yang dilakukan sia-sia dan tidak memberikan kontribusi nyata bagi tim', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 13, 'kode' => 'G09', 'kategori' => 'kognitif', 'nama' => 'Sulit berkonsentrasi pada tugas penting dan sering melakukan kesalahan teknis', 'bobot' => 0.65, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 14, 'kode' => 'G10', 'kategori' => 'kognitif', 'nama' => 'Mengalami penurunan kepercayaan diri dan ketidakpuasan tinggi terhadap hasil kerja sendiri', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 15, 'kode' => 'G17', 'kategori' => 'kognitif', 'nama' => 'Sering merasa tidak berdaya (helplessness) saat menghadapi tantangan kerja yang biasa dihadapi sebelumnya', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 16, 'kode' => 'G18', 'kategori' => 'kognitif', 'nama' => 'Merasa tidak ada apresiasi atau pengakuan sosial atas upaya maksimal yang telah didedikasikan', 'bobot' => 0.65, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 17, 'kode' => 'G11', 'kategori' => 'fisik', 'nama' => 'Sering mengalami gangguan pencernaan atau sakit kepala kronis tanpa diagnosis medis lain', 'bobot' => 0.60, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 18, 'kode' => 'G12', 'kategori' => 'fisik', 'nama' => 'Mengalami insomnia berat atau selalu merasa mengantuk berat sepanjang hari kerja', 'bobot' => 0.65, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 19, 'kode' => 'G19', 'kategori' => 'fisik', 'nama' => 'Merasa leher, pundak, atau otot punggung kaku dan nyeri berkepanjangan akibat ketegangan mental kerja', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 20, 'kode' => 'G20', 'kategori' => 'fisik', 'nama' => 'Mengalami perubahan nafsu makan drastis (sangat menurun atau makan berlebihan secara emosional)', 'bobot' => 0.60, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ]);

        DB::table('aturan')->insert([
            ['id' => 1, 'kode' => 'R01', 'diagnosa_id' => 1, 'cf_pakar' => 0.98, 'prioritas' => 1, 'is_active' => true, 'deskripsi' => 'Aturan kritis untuk mendeteksi kondisi tidak burnout (sehat) di mana karyawan memiliki stamina emosional prima, motivasi kerja tinggi, dan hubungan sosial positif di tempat kerja.', 'min_threshold' => 0.35, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 2, 'kode' => 'R02', 'diagnosa_id' => 1, 'cf_pakar' => 0.92, 'prioritas' => 2, 'is_active' => true, 'deskripsi' => 'Aturan pendukung kondisi tidak burnout yang berfokus pada stabilitas emosional yang meluas pada hubungan sosial yang harmonis dan siklus tidur yang sehat berkualitas.', 'min_threshold' => 0.30, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 3, 'kode' => 'R09', 'diagnosa_id' => 1, 'cf_pakar' => 0.95, 'prioritas' => 3, 'is_active' => true, 'deskripsi' => 'Aturan diagnosis tingkat lanjut yang menunjukkan kesiapan kognitif total, fokus optimal, serta rasa kepemilikan yang kuat terhadap tanggung jawab profesional.', 'min_threshold' => 0.30, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 4, 'kode' => 'R12', 'diagnosa_id' => 1, 'cf_pakar' => 0.94, 'prioritas' => 4, 'is_active' => true, 'deskripsi' => 'Aturan deteksi kepuasan kerja kronis dengan manifestasi pola istirahat yang sangat baik serta kepedulian sosial yang tinggi terhadap rekan kerja.', 'min_threshold' => 0.30, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 5, 'kode' => 'R03', 'diagnosa_id' => 2, 'cf_pakar' => 0.88, 'prioritas' => 15, 'is_active' => true, 'deskripsi' => 'Aturan burnout tinggi yang mengevaluasi hubungan antara stamina emosional yang kering dengan penurunan tajam performa kognitif kerja sehari-hari.', 'min_threshold' => 0.25, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 6, 'kode' => 'R04', 'diagnosa_id' => 2, 'cf_pakar' => 0.84, 'prioritas' => 14, 'is_active' => true, 'deskripsi' => 'Aturan burnout tinggi dengan manifestasi perilaku dingin (sinis) disertai keluhan fisik akibat stres psikologis kronis.', 'min_threshold' => 0.25, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 7, 'kode' => 'R10', 'diagnosa_id' => 2, 'cf_pakar' => 0.86, 'prioritas' => 13, 'is_active' => true, 'deskripsi' => 'Mengevaluasi kondisi burnout tinggi di mana karyawan menunda pekerjaan secara ekstrim dengan ketegangan fisik yang kaku.', 'min_threshold' => 0.25, 'created_at' => $ruleCreatedAt, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 8, 'kode' => 'R05', 'diagnosa_id' => 3, 'cf_pakar' => 0.78, 'prioritas' => 12, 'is_active' => true, 'deskripsi' => 'Aturan burnout sedang untuk mendeteksi fase kelelahan awal yang mulai termanifestasi ke dalam gangguan fisik ringan di luar jam kerja.', 'min_threshold' => 0.20, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 9, 'kode' => 'R06', 'diagnosa_id' => 3, 'cf_pakar' => 0.74, 'prioritas' => 11, 'is_active' => true, 'deskripsi' => 'Aturan burnout sedang yang didominasi oleh perasaan demoralisasi kerja, di mana karyawan merasa kontribusinya menurun drastis.', 'min_threshold' => 0.20, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 10, 'kode' => 'R11', 'diagnosa_id' => 3, 'cf_pakar' => 0.78, 'prioritas' => 10, 'is_active' => true, 'deskripsi' => 'Aturan deteksi stres emosional yang ditunjukkan lewat defensif perilaku dan ketidakseimbangan nutrisi harian.', 'min_threshold' => 0.20, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 11, 'kode' => 'R13', 'diagnosa_id' => 3, 'cf_pakar' => 0.70, 'prioritas' => 9, 'is_active' => true, 'deskripsi' => 'Aturan moderat untuk memantau stres akibat tuntutan kerja tinggi tanpa adanya pengakuan atau apresiasi yang cukup.', 'min_threshold' => 0.15, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 12, 'kode' => 'R14', 'diagnosa_id' => 3, 'cf_pakar' => 0.72, 'prioritas' => 8, 'is_active' => true, 'deskripsi' => 'Mengidentifikasi stres kognitif yang memicu emosi labil beriringan kaku fisik somatic.', 'min_threshold' => 0.15, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 13, 'kode' => 'R07', 'diagnosa_id' => 4, 'cf_pakar' => 0.60, 'prioritas' => 7, 'is_active' => true, 'deskripsi' => 'Aturan normal untuk mengevaluasi ketidakpuasan kerja ringan sesaat yang bersifat umum dan tidak berbahaya.', 'min_threshold' => 0.10, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 14, 'kode' => 'R08', 'diagnosa_id' => 4, 'cf_pakar' => 0.55, 'prioritas' => 6, 'is_active' => true, 'deskripsi' => 'Aturan normal untuk mendeteksi rasa letih pagi biasa (burnout rendah) yang murni dipicu oleh siklus istirahat jangka pendek.', 'min_threshold' => 0.10, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
            ['id' => 15, 'kode' => 'R15', 'diagnosa_id' => 4, 'cf_pakar' => 0.50, 'prioritas' => 5, 'is_active' => true, 'deskripsi' => 'Kondisi stres normal sangat ringan yang termanifestasi ke penundaan minor sesaat.', 'min_threshold' => 0.05, 'created_at' => $ruleCreatedAtSecond, 'updated_at' => $ruleUpdatedAt, 'deleted_at' => null],
        ]);

        $pivots = [
            [1, 1, 0.90], [1, 2, 0.85], [1, 7, 0.95], [1, 8, 0.90],
            [2, 1, 0.85], [2, 4, 0.80], [2, 9, 0.85], [2, 18, 0.75],
            [3, 1, 0.85], [3, 5, 0.90], [3, 15, 0.80],
            [4, 6, 0.90], [4, 8, 0.85], [4, 18, 0.80],
            [5, 1, 0.80], [5, 3, 0.75], [5, 12, 0.85], [5, 13, 0.80],
            [6, 7, 0.85], [6, 8, 0.80], [6, 9, 0.80], [6, 17, 0.70],
            [7, 6, 0.85], [7, 11, 0.80], [7, 19, 0.80],
            [8, 1, 0.70], [8, 17, 0.70], [8, 18, 0.75],
            [9, 4, 0.60], [9, 12, 0.65], [9, 13, 0.65], [9, 14, 0.70],
            [10, 10, 0.80], [10, 14, 0.70], [10, 20, 0.75],
            [11, 3, 0.75], [11, 16, 0.70],
            [12, 4, 0.75], [12, 19, 0.70], [12, 20, 0.65],
            [13, 4, 0.50], [13, 14, 0.60],
            [14, 2, 0.40], [14, 17, 0.45], [14, 18, 0.50],
            [15, 11, 0.50], [15, 19, 0.45],
        ];

        DB::table('aturan_gejala')->insert(array_map(function (array $pivot) {
            [$aturanId, $gejalaId, $bobotPakar] = $pivot;

            return [
                'aturan_id' => $aturanId,
                'gejala_id' => $gejalaId,
                'bobot_pakar' => $bobotPakar,
                'evidence_direction' => in_array($aturanId, [1, 2, 3, 4], true)
                    ? 'ABSENT_SUPPORTS'
                    : 'PRESENT_SUPPORTS',
            ];
        }, $pivots));
    }
}
