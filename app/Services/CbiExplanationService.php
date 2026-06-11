<?php

namespace App\Services;

use App\Models\CbiAssessment;

class CbiExplanationService
{
    public function build(CbiAssessment $assessment): array
    {
        $isComplete = $assessment->status === CbiAssessment::STATUS_COMPLETE;

        return [
            'title' => 'Profil Risiko Burnout Berdasarkan ' . 'Copenhagen Burnout Inventory (CBI)',
            'status' => $assessment->status,
            'is_complete' => $isComplete,
            'summary' => $isComplete
                ? 'Hasil ditampilkan sebagai tiga skor kontinu yang dihitung secara terpisah. ' . 'Semakin tinggi skor, semakin tinggi tingkat kelelahan yang dilaporkan.'
                : 'Data belum mencukupi untuk menghitung seluruh dimensi. ' . 'Skor parsial tidak diterbitkan.',
            'dimensions' => [
                'PB' => $this->dimension(
                    'Personal Burnout',
                    $assessment->personal_score,
                    'Kelelahan fisik dan psikologis yang dialami secara umum.'
                ),
                'WB' => $this->dimension(
                    'Work-related Burnout',
                    $assessment->work_score,
                    'Kelelahan yang dipersepsikan berkaitan dengan pekerjaan.'
                ),
                'CB' => $this->dimension(
                    'Client-related Burnout',
                    $assessment->client_score,
                    'Kelelahan yang dipersepsikan berkaitan dengan penerima layanan atau pihak yang dilayani.'
                ),
            ],
            'disclaimer' => (string) config('cbi.disclaimer'),
            'translation_note' => (string) config('cbi.translation_note'),
        ];
    }

    private function dimension(string $name, ?float $score, string $description): array
    {
        return [
            'name' => $name,
            'score' => $score,
            'chart_value' => $score === null
                ? 0
                : max(0, min(100, round($score, 2))),
            'description' => $description,
            'scale' => '0–100',
        ];
    }
}
