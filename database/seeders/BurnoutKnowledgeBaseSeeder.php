<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BurnoutKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

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
                'nama' => 'Tidak Terindikasi Burnout',
                'tingkat' => 'TIDAK_TERINDIKASI',
                'deskripsi' => 'Tidak ada rule risiko burnout yang mencapai ambang konfirmasi. Hasil ini adalah skrining awal, bukan diagnosis medis.',
                'saran' => 'Pertahankan kebiasaan kerja sehat, lakukan check-in berkala, dan komunikasikan kebutuhan dukungan bila kondisi berubah.',
                'color' => '#16a34a',
                'bg_light' => '#f0fdf4',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'kode' => 'D02',
                'nama' => 'Risiko Burnout Tinggi',
                'tingkat' => 'TINGGI',
                'deskripsi' => 'Pola jawaban menunjukkan indikasi burnout yang kuat pada rule pakar yang diuji.',
                'saran' => 'Kurangi beban non-prioritas, diskusikan dukungan kerja dengan pihak terkait, dan pertimbangkan bantuan profesional bila gejala terasa berat atau mengganggu fungsi harian.',
                'color' => '#dc2626',
                'bg_light' => '#fef2f2',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'kode' => 'D03',
                'nama' => 'Risiko Burnout Sedang',
                'tingkat' => 'SEDANG',
                'deskripsi' => 'Pola jawaban menunjukkan indikasi burnout sedang dan perlu dipantau.',
                'saran' => 'Evaluasi beban kerja, atur prioritas, jadwalkan pemulihan, dan lakukan check-in ulang secara berkala.',
                'color' => '#f97316',
                'bg_light' => '#fff7ed',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'kode' => 'D04',
                'nama' => 'Risiko Burnout Rendah',
                'tingkat' => 'RENDAH',
                'deskripsi' => 'Terdapat indikasi ringan yang belum menunjukkan pola burnout kuat.',
                'saran' => 'Pertahankan ritme kerja sehat, cukup istirahat, dan perhatikan perubahan kondisi dari waktu ke waktu.',
                'color' => '#eab308',
                'bg_light' => '#fefce8',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);

        DB::table('gejala')->insert([
            ['id' => 1, 'kode' => 'G001', 'kategori' => 'emosional', 'nama' => 'Merasa terkuras habis saat bekerja', 'bobot' => 0.85, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'kode' => 'G002', 'kategori' => 'emosional', 'nama' => 'Merasa letih dan tidak berenergi saat bangun untuk bekerja', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'kode' => 'G003', 'kategori' => 'emosional', 'nama' => 'Merasa tegang akibat tuntutan pekerjaan yang terus-menerus', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'kode' => 'G004', 'kategori' => 'emosional', 'nama' => 'Mudah tersinggung atau frustrasi di tempat kerja', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'kode' => 'G005', 'kategori' => 'fisik', 'nama' => 'Beban kerja fisik berlebih', 'bobot' => 0.60, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'kode' => 'G006', 'kategori' => 'emosional', 'nama' => 'Kelelahan emosional mendalam', 'bobot' => 0.92, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'kode' => 'G007', 'kategori' => 'perilaku', 'nama' => 'Menjauhkan diri dari lingkungan kerja', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'kode' => 'G008', 'kategori' => 'kognitif', 'nama' => 'Merasa pekerjaan yang dilakukan sia-sia', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 9, 'kode' => 'G009', 'kategori' => 'perilaku', 'nama' => 'Sinisme terhadap pekerjaan', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 10, 'kode' => 'G010', 'kategori' => 'kognitif', 'nama' => 'Kepercayaan diri terhadap hasil kerja menurun', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 11, 'kode' => 'G011', 'kategori' => 'fisik', 'nama' => 'Keluhan fisik muncul saat tekanan kerja meningkat', 'bobot' => 0.60, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 12, 'kode' => 'G012', 'kategori' => 'fisik', 'nama' => 'Tidur terganggu karena pikiran tentang pekerjaan', 'bobot' => 0.65, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 13, 'kode' => 'G013', 'kategori' => 'emosional', 'nama' => 'Merasa hampa secara emosional', 'bobot' => 0.85, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 14, 'kode' => 'G014', 'kategori' => 'emosional', 'nama' => 'Tidak mampu pulih meski sudah istirahat', 'bobot' => 0.88, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 15, 'kode' => 'G015', 'kategori' => 'perilaku', 'nama' => 'Defensif ketika menerima tugas baru', 'bobot' => 0.80, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 16, 'kode' => 'G016', 'kategori' => 'perilaku', 'nama' => 'Menunda pekerjaan karena enggan memulainya', 'bobot' => 0.75, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 17, 'kode' => 'G017', 'kategori' => 'kognitif', 'nama' => 'Merasa tidak berdaya menghadapi tantangan kerja', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 18, 'kode' => 'G018', 'kategori' => 'kognitif', 'nama' => 'Merasa tidak mendapat apresiasi yang cukup', 'bobot' => 0.65, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 19, 'kode' => 'G019', 'kategori' => 'fisik', 'nama' => 'Ketegangan kerja terasa sebagai nyeri otot atau kaku tubuh', 'bobot' => 0.70, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 20, 'kode' => 'G020', 'kategori' => 'fisik', 'nama' => 'Tekanan kerja memengaruhi pola makan', 'bobot' => 0.60, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ]);

        DB::table('aturan')->insert([
            ['id' => 1, 'kode' => 'R001', 'diagnosa_id' => 2, 'cf_pakar' => 0.95, 'prioritas' => 100, 'is_active' => true, 'deskripsi' => 'PDF: IF G001 AND G006 AND G009 AND G014 AND G017 THEN BURNOUT_TINGGI.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'kode' => 'R002', 'diagnosa_id' => 2, 'cf_pakar' => 0.88, 'prioritas' => 90, 'is_active' => true, 'deskripsi' => 'Rule pendukung burnout tinggi berbasis penarikan diri, sinisme, dan keluhan fisik.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'kode' => 'R003', 'diagnosa_id' => 3, 'cf_pakar' => 0.78, 'prioritas' => 80, 'is_active' => true, 'deskripsi' => 'Rule burnout sedang untuk kelelahan awal dan gangguan kerja.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'kode' => 'R004', 'diagnosa_id' => 3, 'cf_pakar' => 0.74, 'prioritas' => 70, 'is_active' => true, 'deskripsi' => 'Rule burnout sedang untuk penurunan kontribusi dan apresiasi.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'kode' => 'R005', 'diagnosa_id' => 4, 'cf_pakar' => 0.62, 'prioritas' => 60, 'is_active' => true, 'deskripsi' => 'Rule burnout rendah untuk indikasi ringan.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'kode' => 'R006', 'diagnosa_id' => 4, 'cf_pakar' => 0.58, 'prioritas' => 50, 'is_active' => true, 'deskripsi' => 'Rule burnout rendah untuk gejala ringan fisik dan perilaku.', 'min_threshold' => 0.25, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ]);

        $pivots = [
            [1, 1], [1, 6], [1, 9], [1, 14], [1, 17],
            [2, 7], [2, 9], [2, 11], [2, 16], [2, 19],
            [3, 2], [3, 3], [3, 10], [3, 12],
            [4, 4], [4, 8], [4, 15], [4, 18],
            [5, 2], [5, 5], [5, 10],
            [6, 3], [6, 16], [6, 20],
        ];

        DB::table('aturan_gejala')->insert(array_map(fn (array $pivot) => [
            'aturan_id' => $pivot[0],
            'gejala_id' => $pivot[1],
            'bobot_pakar' => 0.0,
            'evidence_direction' => 'PRESENT_SUPPORTS',
        ], $pivots));

        Cache::flush();
    }
}
