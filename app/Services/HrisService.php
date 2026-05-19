<?php

namespace App\Services;

use App\Models\User;

class HrisService
{
    /**
     * Fetch synchronized HRIS & Attendance data for a specific user.
     * Generates consistent, deterministic dummy metrics based on user ID.
     */
    public function getMetrics(User $user): array
    {
        // Deterministic mock seed based on User ID
        $seed = $user->id;
        mt_srand($seed);

        $totalHours = mt_rand(160, 185);
        $overtimeHours = mt_rand(5, 32);
        $lateArrivals = mt_rand(0, 7);
        $remainingLeaves = mt_rand(4, 14);

        $baseMinutes = mt_rand(0, 20);
        $isLate = $baseMinutes > 15;
        $clockInTime = sprintf("08:%02d", $baseMinutes);

        // Analyze correlation to mental stress levels (CF)
        $correlationCoefficient = 0.0;
        if ($overtimeHours > 20) {
            $correlationCoefficient += 0.15; // 15% increase risk
        }
        if ($lateArrivals > 3) {
            $correlationCoefficient += 0.08; // 8% increase risk
        }

        $correlationMessage = "Jam lembur bulanan Anda (" . $overtimeHours . " jam) berkorelasi ";
        if ($correlationCoefficient >= 0.20) {
            $correlationMessage .= "sangat kuat dengan peningkatan risiko kelelahan emosional sebesar " . ($correlationCoefficient * 100) . "%!";
        } elseif ($correlationCoefficient > 0.0) {
            $correlationMessage .= "sedang dengan peningkatan kecenderungan tingkat stress sebesar " . ($correlationCoefficient * 100) . "%!";
        } else {
            $correlationMessage = "Keseimbangan jam kerja (Work-Life Balance) Anda sangat baik dan tidak memicu penumpukan beban kognitif.";
        }

        return [
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'late_arrivals' => $lateArrivals,
            'remaining_leaves' => $remainingLeaves,
            'clock_in_time' => $clockInTime,
            'is_late' => $isLate,
            'correlation_factor' => $correlationCoefficient,
            'correlation_message' => $correlationMessage,
        ];
    }
}
