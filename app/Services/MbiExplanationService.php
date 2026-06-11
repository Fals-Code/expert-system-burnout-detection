<?php

namespace App\Services;

use App\Models\MbiAssessment;

class MbiExplanationService
{
    public function build(MbiAssessment $assessment): array
    {
        $isComplete = $assessment->status === MbiAssessment::STATUS_COMPLETE;
        $profileCode = $assessment->profile_code ?? 'CONTINUOUS_PROFILE';

        return [
            'title' => 'Profil Risiko Burnout Berdasarkan MBI-GS',
            'status' => $assessment->status,
            'is_complete' => $isComplete,
            'profile_code' => $profileCode,
            'profile_label' => $this->profileLabel($profileCode),
            'profile_basis' => $assessment->profile_basis,
            'summary' => $this->summary($assessment, $isComplete),
            'dimensions' => [
                'EX' => [
                    'name' => 'Exhaustion',
                    'score' => $assessment->ex_score,
                    'total' => $assessment->ex_total,
                    'direction' => 'Skor lebih tinggi menunjukkan frekuensi kelelahan yang lebih tinggi.',
                ],
                'CY' => [
                    'name' => 'Cynicism',
                    'score' => $assessment->cy_score,
                    'total' => $assessment->cy_total,
                    'direction' => 'Skor lebih tinggi menunjukkan frekuensi sinisme atau jarak psikologis yang lebih tinggi.',
                ],
                'PE' => [
                    'name' => 'Professional Efficacy',
                    'score' => $assessment->pe_score,
                    'total' => $assessment->pe_total,
                    'direction' => 'Skor lebih tinggi menunjukkan efikasi profesional yang lebih kuat.',
                ],
            ],
            'red_flag' => [
                'active' => $assessment->has_red_flag,
                'codes' => $assessment->red_flag_codes ?? [],
                'recommendation' => $assessment->has_red_flag
                    ? 'Hasil pemeriksaan keselamatan terpisah menunjukkan kebutuhan dukungan. Pertimbangkan menghubungi psikolog independen atau tenaga kesehatan mental yang kompeten.'
                    : null,
            ],
            'disclaimer' => (string) config('mbi.disclaimer'),
        ];
    }

    private function summary(MbiAssessment $assessment, bool $isComplete): string
    {
        if (! $isComplete) {
            return 'Data belum mencukupi untuk menghitung seluruh dimensi. Tidak ada diagnosis atau profil kategoris yang diterbitkan.';
        }

        if (($assessment->profile_code ?? 'CONTINUOUS_PROFILE') === 'CONTINUOUS_PROFILE') {
            return 'Hasil disajikan sebagai tiga skor dimensi kontinu. Profil kategoris tidak diaktifkan karena ambang organisasi yang tervalidasi belum dikonfigurasi.';
        }

        return 'Profil merupakan interpretasi pola tiga dimensi berdasarkan ambang yang dikonfigurasi dari pedoman berlisensi atau norma organisasi yang telah divalidasi. Profil ini tetap bukan diagnosis klinis.';
    }

    private function profileLabel(string $profileCode): string
    {
        return match ($profileCode) {
            'BURNOUT_PATTERN' => 'Pola Burnout',
            'OVEREXTENDED' => 'Overextended',
            'DISENGAGED' => 'Disengaged',
            'INEFFECTIVE' => 'Ineffective',
            'ENGAGED_PATTERN' => 'Pola Engagement',
            'MIXED_PROFILE' => 'Profil Campuran',
            'INSUFFICIENT_DATA' => 'Data Tidak Mencukupi',
            default => 'Profil Dimensional Kontinu',
        };
    }
}
