<?php

namespace App\Services;

class SafetyFlagService
{
    public function evaluate(?int $response): array
    {
        $threshold = (int) config('mbi.red_flag.high_response_threshold', 4);
        $code = (string) config('mbi.red_flag.code', 'G14');
        $isHigh = $response !== null && $response >= $threshold;

        return [
            'has_red_flag' => $isHigh,
            'response' => $response,
            'codes' => $isHigh ? [$code] : [],
            'recommendation' => $isHigh
                ? 'Pertimbangkan dukungan segera dari psikolog independen atau tenaga kesehatan mental yang kompeten. Jalur ini terpisah dari skor MBI-GS.'
                : null,
        ];
    }
}
