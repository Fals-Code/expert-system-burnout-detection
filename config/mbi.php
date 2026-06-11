<?php

return [
    'instrument' => [
        'code' => 'MBI-GS',
        'version' => '1996/2016',
        'expected_item_count' => 16,
        'dimensions' => [
            'EX' => 5,
            'CY' => 5,
            'PE' => 6,
        ],
        'response_scale' => [
            0 => 'Tidak pernah',
            1 => 'Beberapa kali setahun atau kurang',
            2 => 'Sekali sebulan atau kurang',
            3 => 'Beberapa kali sebulan',
            4 => 'Sekali seminggu',
            5 => 'Beberapa kali seminggu',
            6 => 'Setiap hari',
        ],
    ],
    'licensed_content' => [
        'required' => true,
    ],
    'profile_classification' => [
        'enabled' => (bool) env('MBI_PROFILE_CLASSIFICATION_ENABLED', false),
        'thresholds' => [
            'ex_high' => env('MBI_EX_HIGH_THRESHOLD'),
            'cy_high' => env('MBI_CY_HIGH_THRESHOLD'),
            'pe_low' => env('MBI_PE_LOW_THRESHOLD'),
        ],
    ],
    'red_flag' => [
        'code' => 'G14',
        'high_response_threshold' => 4,
    ],
    'disclaimer_version' => 'mbi-gs-screening-v1',
    'disclaimer' => 'MBI-GS menghasilkan skor kontinu untuk keperluan riset dan pengembangan organisasi. Hasil ini bukan diagnosis klinis atau medis dan tidak boleh digunakan sebagai satu-satunya dasar keputusan ketenagakerjaan.',
];
