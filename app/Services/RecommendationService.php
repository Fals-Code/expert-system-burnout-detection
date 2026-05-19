<?php

namespace App\Services;

use App\Models\User;
use App\Models\Konsultasi;

class RecommendationService
{
    /**
     * Generate context-aware, highly personalized recommendations.
     */
    public function generate(User $user, array $hrisMetrics, ?Konsultasi $latestResult): array
    {
        $overtime = $hrisMetrics['overtime_hours'] ?? 0;
        $leaves = $hrisMetrics['remaining_leaves'] ?? 0;
        $level = $latestResult ? strtoupper($latestResult->diagnosa?->tingkat ?? 'RENDAH') : 'RENDAH';
        $cf = $latestResult ? $latestResult->cf_final : 0.0;

        $recommendedLeaveDays = 0;
        $scheduleAdvice = "";
        $mentalActivity = "";
        $riskNarrative = "";

        // Core Recommendation Rules Matrix
        if ($level === 'SANGAT TINGGI' || $level === 'TINGGI') {
            $riskNarrative = "Tingkat stress kognitif Anda memerlukan intervensi segera guna membatasi dampak somatic klinis.";
            
            // Leave suggestions based on remaining leaves
            if ($leaves >= 5) {
                $recommendedLeaveDays = min(3, $leaves);
                $leaveReason = "mengistirahatkan sistem saraf simpatik Anda sepenuhnya.";
            } elseif ($leaves > 0) {
                $recommendedLeaveDays = $leaves;
                $leaveReason = "memberikan jeda relaksasi mental esensial.";
            } else {
                $recommendedLeaveDays = 0;
                $leaveReason = "";
            }

            // Schedule suggestions based on overtime
            if ($overtime > 15) {
                $scheduleAdvice = "Kurangi jam lembur bulanan Anda secara radikal. Batasi waktu kerja ekstra maksimal 2 jam seminggu selama 14 hari ke depan.";
            } else {
                $scheduleAdvice = "Delegasikan tugas berprioritas tinggi dan batasi interaksi layar/komputer di luar jam kerja normal.";
            }

            // Mental/Therapeutic activity suggestion
            $mentalActivity = "Lakukan latihan pernapasan 'Box Breathing' (tarik 4s, tahan 4s, hembus 4s, tahan 4s) selama 5-10 menit sebelum memulai pekerjaan.";
        } elseif ($level === 'SEDANG') {
            $riskNarrative = "Kondisi kelelahan mental Anda berada pada ambang batas sedang. Dibutuhkan tindakan pencegahan proaktif.";

            if ($leaves >= 3) {
                $recommendedLeaveDays = 1;
                $leaveReason = "mengembalikan kesegaran fokus kognitif Anda.";
            } else {
                $recommendedLeaveDays = 0;
                $leaveReason = "";
            }

            if ($overtime > 10) {
                $scheduleAdvice = "Jadwalkan 'digital detox' di akhir pekan dan hindari memeriksa email/grup koordinasi kerja setelah pukul 19:00.";
            } else {
                $scheduleAdvice = "Tetapkan batasan kerja yang tegas antara ruang pribadi dan tanggung jawab profesional harian.";
            }

            $mentalActivity = "Terapkan teknik 'Pomodoro' (25 menit kerja fokus, 5 menit istirahat berjalan kaki/minum air) untuk membatasi kelelahan mental.";
        } else {
            // Rendah / Aman
            $riskNarrative = "Indeks ketahanan psikologis Anda sangat solid. Fokus Anda saat ini adalah menjaga kestabilan energi.";
            $recommendedLeaveDays = 0;
            $leaveReason = "";
            
            $scheduleAdvice = "Pertahankan ritme kerja seimbang saat ini. Tetap sisihkan waktu untuk sosialisasi non-pekerjaan dengan rekan kantor.";
            $mentalActivity = "Lakukan olahraga kardio ringan 3 kali seminggu (misalnya jalan cepat atau jogging 20 menit) guna memperkuat hormon endorfin.";
        }

        // Formatting leave suggestion text
        $leaveText = "";
        if ($recommendedLeaveDays > 0) {
            $leaveText = "Disarankan mengambil cuti selama " . $recommendedLeaveDays . " hari dari sisa " . $leaves . " hari cuti Anda untuk " . $leaveReason;
        } else {
            $leaveText = "Pertahankan jatah cuti tahunan Anda (" . $leaves . " hari tersisa) sebagai cadangan pemulihan di kuartal berikutnya.";
        }

        return [
            'risk_narrative' => $riskNarrative,
            'leave_days' => $recommendedLeaveDays,
            'leave_recommendation' => $leaveText,
            'schedule_recommendation' => $scheduleAdvice,
            'activity_recommendation' => $mentalActivity,
        ];
    }
}
