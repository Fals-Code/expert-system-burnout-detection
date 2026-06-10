<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update master knowledge base after D01 became Tidak Burnout / Healthy State.
     */
    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            $this->updateDiagnosaMaster($now);
            $this->updateAturanMaster($now);
            $this->updateAturanGejalaPivot();
        });

        $this->flushExpertSystemCache();
    }

    /**
     * Rollback only the semantic changes introduced by this data migration.
     * Historical timestamps are intentionally not restored exactly.
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::table('diagnosa')->where('id', 1)->update([
                'nama' => 'Tidak Burnout (Kondisi Sehat)',
                'tingkat' => 'TIDAK BURNOUT',
                'deskripsi' => 'Tidak ditemukan pola gejala burnout yang signifikan.',
                'saran' => 'Pertahankan pola kerja sehat, istirahat cukup, dan keseimbangan aktivitas harian.',
                'color' => '#16a34a',
                'bg_light' => '#f0fdf4',
                'updated_at' => now(),
            ]);
        });

        $this->flushExpertSystemCache();
    }

    private function updateDiagnosaMaster($now): void
    {
        DB::table('diagnosa')->where('id', 1)->update([
            'nama' => 'Tidak Burnout (Healthy State)',
            'tingkat' => 'TIDAK BURNOUT',
            'deskripsi' => 'Karyawan berada dalam kondisi kesehatan mental dan fisik yang prima. Memiliki keseimbangan kerja (work-life balance) yang baik, motivasi tinggi, dan fungsi kognitif yang bekerja secara optimal.',
            'saran' => 'Pertahankan pola kerja saat ini, lakukan evaluasi berkala, dan bagikan tips manajemen stres positif kepada rekan kerja lainnya.',
            'color' => '#16a34a',
            'bg_light' => '#f0fdf4',
            'updated_at' => $now,
        ]);
    }

    private function updateAturanMaster($now): void
    {
        $aturan = [
            [1, 'R01', 1, 0.98, 1, 1, 'Aturan kritis untuk mendeteksi kondisi tidak burnout (sehat) di mana karyawan memiliki stamina emosional prima, motivasi kerja tinggi, dan hubungan sosial positif di tempat kerja.', 0.35],
            [2, 'R02', 1, 0.92, 2, 1, 'Aturan pendukung kondisi tidak burnout yang berfokus pada stabilitas emosional yang meluas pada hubungan sosial yang harmonis dan siklus tidur yang sehat berkualitas.', 0.30],
            [3, 'R09', 1, 0.95, 3, 1, 'Aturan diagnosis tingkat lanjut yang menunjukkan kesiapan kognitif total, fokus optimal, serta rasa kepemilikan yang kuat terhadap tanggung jawab profesional.', 0.30],
            [4, 'R12', 1, 0.94, 4, 1, 'Aturan deteksi kepuasan kerja kronis dengan manifestasi pola istirahat yang sangat baik serta kepedulian sosial yang tinggi terhadap rekan kerja.', 0.30],
            [5, 'R03', 2, 0.88, 15, 1, 'Aturan burnout tinggi yang mengevaluasi hubungan antara stamina emosional yang kering dengan penurunan tajam performa kognitif kerja sehari-hari.', 0.25],
            [6, 'R04', 2, 0.84, 14, 1, 'Aturan burnout tinggi dengan manifestasi perilaku dingin (sinis) disertai keluhan fisik akibat stres psikologis kronis.', 0.25],
            [7, 'R10', 2, 0.86, 13, 1, 'Mengevaluasi kondisi burnout tinggi di mana karyawan menunda pekerjaan secara ekstrim dengan ketegangan fisik yang kaku.', 0.25],
            [8, 'R05', 3, 0.78, 12, 1, 'Aturan burnout sedang untuk mendeteksi fase kelelahan awal yang mulai termanifestasi ke dalam gangguan fisik ringan di luar jam kerja.', 0.20],
            [9, 'R06', 3, 0.74, 11, 1, 'Aturan burnout sedang yang didominasi oleh perasaan demoralisasi kerja, di mana karyawan merasa kontribusinya menurun drastis.', 0.20],
            [10, 'R11', 3, 0.78, 10, 1, 'Aturan deteksi stres emosional yang ditunjukkan lewat defensif perilaku dan ketidakseimbangan nutrisi harian.', 0.20],
            [11, 'R13', 3, 0.70, 9, 1, 'Aturan moderat untuk memantau stres akibat tuntutan kerja tinggi tanpa adanya pengakuan atau apresiasi yang cukup.', 0.15],
            [12, 'R14', 3, 0.72, 8, 1, 'Mengidentifikasi stres kognitif yang memicu emosi labil beriringan kaku fisik somatic.', 0.15],
            [13, 'R07', 4, 0.60, 7, 1, 'Aturan normal untuk mengevaluasi ketidakpuasan kerja ringan sesaat yang bersifat umum dan tidak berbahaya.', 0.10],
            [14, 'R08', 4, 0.55, 6, 1, 'Aturan normal untuk mendeteksi rasa letih pagi biasa (burnout rendah) yang murni dipicu oleh siklus istirahat jangka pendek.', 0.10],
            [15, 'R15', 4, 0.50, 5, 1, 'Kondisi stres normal sangat ringan yang termanifestasi ke penundaan minor sesaat.', 0.05],
        ];

        foreach ($aturan as [$id, $kode, $diagnosaId, $cfPakar, $prioritas, $isActive, $deskripsi, $minThreshold]) {
            DB::table('aturan')->updateOrInsert(
                ['id' => $id],
                [
                    'kode' => $kode,
                    'diagnosa_id' => $diagnosaId,
                    'cf_pakar' => $cfPakar,
                    'prioritas' => $prioritas,
                    'is_active' => $isActive,
                    'deskripsi' => $deskripsi,
                    'min_threshold' => $minThreshold,
                    'created_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function updateAturanGejalaPivot(): void
    {
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

        DB::table('aturan_gejala')->delete();

        foreach ($pivots as [$aturanId, $gejalaId, $bobotPakar]) {
            $payload = [
                'aturan_id' => $aturanId,
                'gejala_id' => $gejalaId,
                'bobot_pakar' => $bobotPakar,
            ];

            if (Schema::hasColumn('aturan_gejala', 'evidence_direction')) {
                $payload['evidence_direction'] = in_array($aturanId, [1, 2, 3, 4], true)
                    ? 'ABSENT_SUPPORTS'
                    : 'PRESENT_SUPPORTS';
            }

            DB::table('aturan_gejala')->insert($payload);
        }
    }

    private function flushExpertSystemCache(): void
    {
        Cache::forget('aturan_active_rules_base64');
        Cache::forget('diagnosa_ordered_base64');
        Cache::forget('diagnosa_default_rendah_base64');
        Cache::forget('diagnosa_default_tidak_burnout_base64');

        DB::table('diagnosa')
            ->pluck('id')
            ->each(function ($id) {
                Cache::forget("aturan_by_diagnosa_{$id}_base64");
            });
    }
};
