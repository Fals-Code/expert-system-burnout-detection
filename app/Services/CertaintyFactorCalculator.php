<?php

namespace App\Services;

class CertaintyFactorCalculator
{
    public const ANSWER_CF = [
        'Sering' => 1.0,
        'Kadang' => 0.6,
        'Tidak Pernah' => 0.0,
    ];

    public function userCf(?string $answer): float
    {
        return self::ANSWER_CF[$answer ?? ''] ?? 0.0;
    }

    public function premiseCf(?string $answer, float $symptomWeight): float
    {
        return $this->clamp($this->userCf($answer) * $symptomWeight);
    }

    /**
     * PDF-aligned rule score: average premise CF multiplied by expert rule CF.
     */
    public function ruleCf(array $premiseCfs, float $expertCf): float
    {
        if ($premiseCfs === []) {
            return 0.0;
        }

        return $this->clamp((array_sum($premiseCfs) / count($premiseCfs)) * $expertCf);
    }

    public function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    public function confidenceLabel(float $cf): string
    {
        return match (true) {
            $cf >= 0.8 => 'Sangat Yakin',
            $cf >= 0.6 => 'Yakin',
            $cf >= 0.4 => 'Cukup Yakin',
            $cf >= 0.25 => 'Terindikasi',
            default => 'Tidak Terkonfirmasi',
        };
    }
}
