<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\User;

class RecommendationService
{
    public function generate(User $user, array $hrisMetrics, ?Konsultasi $latestResult): array
    {
        $level = strtoupper($latestResult->diagnosa?->tingkat ?? 'TIDAK_TERINDIKASI');

        if ($level === 'TINGGI') {
            return [
                'risk_narrative' => 'Hasil skrining menunjukkan kategori tinggi. Gunakan informasi ini sebagai dasar percakapan dukungan, bukan diagnosis medis.',
                'leave_days' => 0,
                'leave_recommendation' => 'Diskusikan penyesuaian beban kerja atau kebutuhan istirahat dengan pihak yang berwenang sesuai kebijakan organisasi.',
                'schedule_recommendation' => 'Prioritaskan tugas yang paling penting dan batasi tambahan pekerjaan yang belum mendesak.',
                'activity_recommendation' => 'Pertimbangkan berbicara dengan profesional kesehatan bila kondisi terasa berat atau mengganggu fungsi harian.',
            ];
        }

        if ($level === 'SEDANG') {
            return [
                'risk_narrative' => 'Hasil skrining menunjukkan kategori sedang dan perlu dipantau.',
                'leave_days' => 0,
                'leave_recommendation' => 'Rencanakan jeda pemulihan sesuai kebutuhan dan kebijakan kerja.',
                'schedule_recommendation' => 'Tinjau prioritas kerja dan komunikasikan hambatan yang berulang.',
                'activity_recommendation' => 'Lakukan langkah pemulihan ringan yang realistis, seperti jeda singkat dan tidur cukup.',
            ];
        }

        return [
            'risk_narrative' => 'Hasil skrining tidak menunjukkan indikasi kuat pada saat ini.',
            'leave_days' => 0,
            'leave_recommendation' => 'Pertahankan kebiasaan kerja sehat dan lakukan check-in ulang secara berkala.',
            'schedule_recommendation' => 'Jaga batas kerja yang jelas dan evaluasi beban kerja jika kondisi berubah.',
            'activity_recommendation' => 'Gunakan aktivitas pemulihan yang sesuai dengan preferensi pribadi dan kondisi masing-masing.',
        ];
    }
}
