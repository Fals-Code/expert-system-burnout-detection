<?php

return [
    'instrument' => [
        'code' => 'CBI',
        'version' => '2005-ID-adapted',
        'expected_item_count' => 19,
        'dimensions' => [
            'PB' => 6,
            'WB' => 7,
            'CB' => 6,
        ],
    ],

    'response_options' => [
        'ALWAYS' => ['label' => 'Selalu', 'score' => 100],
        'OFTEN' => ['label' => 'Sering', 'score' => 75],
        'SOMETIMES' => ['label' => 'Kadang-kadang', 'score' => 50],
        'SELDOM' => ['label' => 'Jarang', 'score' => 25],
        'NEVER' => ['label' => 'Tidak pernah', 'score' => 0],
    ],

    'disclaimer_version' => 'cbi-screening-v1',
    'disclaimer' => 'Copenhagen Burnout Inventory menghasilkan skor kontinu 0–100 untuk keperluan skrining, riset, dan pengembangan organisasi. Hasil ini bukan diagnosis klinis atau medis dan tidak boleh digunakan sebagai satu-satunya dasar keputusan ketenagakerjaan.',
    'translation_note' => 'Teks Bahasa Indonesia merupakan terjemahan operasional dari CBI versi bahasa Inggris Kristensen dkk. (2005). Untuk penelitian formal, lakukan validasi bahasa dan lintas budaya pada populasi sasaran.',
];
