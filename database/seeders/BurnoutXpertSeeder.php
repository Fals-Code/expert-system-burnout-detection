<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BurnoutXpertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        // 1. Divisi
        DB::table('divisi')->insert([
            ['id' => 1, 'nama' => 'Engineering'],
            ['id' => 2, 'nama' => 'Marketing'],
            ['id' => 3, 'nama' => 'Finance'],
            ['id' => 4, 'nama' => 'Human Resources'],
            ['id' => 5, 'nama' => 'Operations'],
        ]);

        // 2. Users
        DB::table('users')->insert([
            [
                'id' => 1,
                'nama' => 'Ahmad Fauzi',
                'email' => 'karyawan@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'divisi_id' => 1,
            ],
            [
                'id' => 2,
                'nama' => 'Siti Rahayu',
                'email' => 'hrd@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'divisi_id' => 4,
            ],
            [
                'id' => 3,
                'nama' => 'Budi Santoso',
                'email' => 'admin@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'divisi_id' => 1,
            ],
        ]);

        // 3. Gejala
        DB::table('gejala')->insert([
            ['id' => 1, 'kode' => 'G001', 'nama' => 'Kelelahan Fisik Berkepanjangan', 'bobot' => 0.85],
            ['id' => 2, 'kode' => 'G002', 'nama' => 'Sakit Kepala atau Migrain Sering', 'bobot' => 0.70],
            ['id' => 3, 'kode' => 'G003', 'nama' => 'Gangguan Tidur (Insomnia/Hipersomnia)', 'bobot' => 0.75],
            ['id' => 4, 'kode' => 'G004', 'nama' => 'Penurunan Imunitas (Sering Sakit)', 'bobot' => 0.65],
            ['id' => 5, 'kode' => 'G005', 'nama' => 'Beban Kerja Fisik Berlebih', 'bobot' => 0.60],
            ['id' => 6, 'kode' => 'G006', 'nama' => 'Kelelahan Emosional Mendalam', 'bobot' => 0.92],
            ['id' => 7, 'kode' => 'G007', 'nama' => 'Sikap Sinis terhadap Pekerjaan', 'bobot' => 0.80],
            ['id' => 8, 'kode' => 'G008', 'nama' => 'Merasa Tidak Dihargai atau Diabaikan', 'bobot' => 0.70],
            ['id' => 9, 'kode' => 'G009', 'nama' => 'Putus Asa terhadap Target Kerja', 'bobot' => 0.90],
            ['id' => 10, 'kode' => 'G010', 'nama' => 'Rasa Cemas Berlebih terkait Pekerjaan', 'bobot' => 0.75],
            ['id' => 11, 'kode' => 'G011', 'nama' => 'Depersonalisasi (Tidak Peduli/Apatis)', 'bobot' => 0.88],
            ['id' => 12, 'kode' => 'G012', 'nama' => 'Penurunan Prestasi & Produktivitas', 'bobot' => 0.78],
            ['id' => 13, 'kode' => 'G013', 'nama' => 'Menghindari Tanggungjawab Kerja', 'bobot' => 0.72],
            ['id' => 14, 'kode' => 'G014', 'nama' => 'Isolasi Diri dari Rekan Kerja', 'bobot' => 0.68],
            ['id' => 15, 'kode' => 'G015', 'nama' => 'Terlambat atau Sering Absen', 'bobot' => 0.60],
            ['id' => 16, 'kode' => 'G016', 'nama' => 'Sulit Berkonsentrasi & Fokus', 'bobot' => 0.72],
            ['id' => 17, 'kode' => 'G017', 'nama' => 'Pelupa dan Sering Membuat Kesalahan', 'bobot' => 0.65],
            ['id' => 18, 'kode' => 'G018', 'nama' => 'Sulit Membuat Keputusan', 'bobot' => 0.70],
            ['id' => 19, 'kode' => 'G019', 'nama' => 'Sulit Memulai atau Menyelesaikan Tugas', 'bobot' => 0.75],
            ['id' => 20, 'kode' => 'G020', 'nama' => 'Hilang Kreativitas & Inisiatif', 'bobot' => 0.68],
        ]);

        // 4. Diagnosa
        DB::table('diagnosa')->insert([
            [
                'id' => 1,
                'kode' => 'D001',
                'nama' => 'Burnout Tinggi',
                'tingkat' => 'BERAT',
                'deskripsi' => 'Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, depersonalisasi, dan penurunan motivasi signifikan.',
                'saran' => 'Segera konsultasi dengan psikolog/psikiater, pertimbangkan cuti panjang atau rotasi jabatan.',
                'color' => '#DC3545',
                'bg_light' => '#FFF5F5'
            ],
            [
                'id' => 2,
                'kode' => 'D002',
                'nama' => 'Burnout Sedang',
                'tingkat' => 'SEDANG',
                'deskripsi' => 'Anda menunjukkan tanda-tanda burnout tingkat sedang. Beberapa gejala mulai mengganggu produktivitas dan kesejahteraan.',
                'saran' => 'Konsultasikan dengan HRD, pertimbangkan cuti, sesi konseling, manajemen waktu.',
                'color' => '#F59E0B',
                'bg_light' => '#FFFBEB'
            ],
            [
                'id' => 3,
                'kode' => 'D003',
                'nama' => 'Burnout Rendah',
                'tingkat' => 'RINGAN',
                'deskripsi' => 'Anda menunjukkan gejala burnout tingkat rendah berupa kelelahan fisik dan beban kerja awal.',
                'saran' => 'Istirahat yang cukup, olahraga ringan, kurangi lembur, hobi sebagai relaksasi.',
                'color' => '#3B82F6',
                'bg_light' => '#EFF6FF'
            ],
        ]);

        // 5. Aturan
        DB::table('aturan')->insert([
            ['id' => 1, 'kode' => 'R001', 'diagnosa_id' => 1, 'cf_pakar' => 0.95],
            ['id' => 2, 'kode' => 'R002', 'diagnosa_id' => 1, 'cf_pakar' => 0.88],
            ['id' => 3, 'kode' => 'R003', 'diagnosa_id' => 2, 'cf_pakar' => 0.75],
            ['id' => 4, 'kode' => 'R004', 'diagnosa_id' => 2, 'cf_pakar' => 0.70],
            ['id' => 5, 'kode' => 'R005', 'diagnosa_id' => 3, 'cf_pakar' => 0.50],
            ['id' => 6, 'kode' => 'R006', 'diagnosa_id' => 3, 'cf_pakar' => 0.45],
        ]);

        // 6. Aturan Gejala
        DB::table('aturan_gejala')->insert([
            ['aturan_id' => 1, 'gejala_id' => 1], ['aturan_id' => 1, 'gejala_id' => 6], ['aturan_id' => 1, 'gejala_id' => 7], ['aturan_id' => 1, 'gejala_id' => 9], ['aturan_id' => 1, 'gejala_id' => 11], ['aturan_id' => 1, 'gejala_id' => 12], ['aturan_id' => 1, 'gejala_id' => 16],
            ['aturan_id' => 2, 'gejala_id' => 6], ['aturan_id' => 2, 'gejala_id' => 9], ['aturan_id' => 2, 'gejala_id' => 11], ['aturan_id' => 2, 'gejala_id' => 13], ['aturan_id' => 2, 'gejala_id' => 14], ['aturan_id' => 2, 'gejala_id' => 18],
            ['aturan_id' => 3, 'gejala_id' => 1], ['aturan_id' => 3, 'gejala_id' => 5], ['aturan_id' => 3, 'gejala_id' => 8], ['aturan_id' => 3, 'gejala_id' => 10], ['aturan_id' => 3, 'gejala_id' => 16], ['aturan_id' => 3, 'gejala_id' => 19],
            ['aturan_id' => 4, 'gejala_id' => 2], ['aturan_id' => 4, 'gejala_id' => 3], ['aturan_id' => 4, 'gejala_id' => 7], ['aturan_id' => 4, 'gejala_id' => 12], ['aturan_id' => 4, 'gejala_id' => 17], ['aturan_id' => 4, 'gejala_id' => 20],
            ['aturan_id' => 5, 'gejala_id' => 1], ['aturan_id' => 5, 'gejala_id' => 5], ['aturan_id' => 5, 'gejala_id' => 16],
            ['aturan_id' => 6, 'gejala_id' => 3], ['aturan_id' => 6, 'gejala_id' => 4], ['aturan_id' => 6, 'gejala_id' => 19],
        ]);

        // 7. Settings
        DB::table('settings')->insert([
            ['kunci' => 'app_name', 'nilai' => 'BurnoutXpert'],
            ['kunci' => 'threshold_high', 'nilai' => '0.8'],
            ['kunci' => 'maintenance_mode', 'nilai' => '0'],
        ]);
    }

    public function run(): void
    {
        $this->up();
    }
}
