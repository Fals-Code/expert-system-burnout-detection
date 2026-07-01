<?php

namespace App\Services;

use App\Models\User;

class HrisService
{
    public function getMetrics(User $user): array
    {
        return [
            'total_hours' => null,
            'overtime_hours' => null,
            'late_arrivals' => null,
            'remaining_leaves' => null,
            'clock_in_time' => null,
            'is_late' => false,
            'correlation_factor' => 0.0,
            'correlation_message' => 'Data HRIS belum terhubung. SanctuaryHub tidak membuat klaim korelasi kerja atau kesehatan dari data dummy.',
        ];
    }
}
