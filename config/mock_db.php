<?php
// ============================================================
//  BurnoutXpert – Mock Knowledge Base (Backward Chaining)
// ============================================================

$default_kb = [
    'gejala' => [
        ['id' => 'G001', 'nama' => 'Kelelahan Fisik', 'kategori' => 'fisik', 'bobot' => 0.8],
        ['id' => 'G002', 'nama' => 'Kelelahan Emosional', 'kategori' => 'emosional', 'bobot' => 0.9],
        ['id' => 'G003', 'nama' => 'Depersonalisasi (Tidak Peduli)', 'kategori' => 'perilaku', 'bobot' => 0.85],
        ['id' => 'G004', 'nama' => 'Sulit Berkonsentrasi', 'kategori' => 'kognitif', 'bobot' => 0.7],
        ['id' => 'G005', 'nama' => 'Penurunan Prestasi Kerja', 'kategori' => 'perilaku', 'bobot' => 0.75],
        ['id' => 'G006', 'nama' => 'Sikap Sinis', 'kategori' => 'emosional', 'bobot' => 0.8],
        ['id' => 'G007', 'nama' => 'Beban Kerja Berlebih', 'kategori' => 'fisik', 'bobot' => 0.6],
        ['id' => 'G008', 'nama' => 'Merasa Tidak Dihargai', 'kategori' => 'emosional', 'bobot' => 0.65],
        ['id' => 'G009', 'nama' => 'Sulit Memulai Kerja', 'kategori' => 'kognitif', 'bobot' => 0.7],
        ['id' => 'G010', 'nama' => 'Putus Asa terhadap Target', 'kategori' => 'emosional', 'bobot' => 0.9],
    ],
    'aturan' => [
        [
            'id' => 'R001', 
            'diagnosa' => 'BURNOUT TINGGI', 
            'gejala' => ['G001', 'G002', 'G003', 'G005', 'G006', 'G010'], 
            'cf_pakar' => 0.9,
            'color' => '#DC3545',
            'bg_light' => '#FFF5F5',
            'desc' => 'Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat dan penurunan motivasi signifikan. Kondisi ini memerlukan perhatian segera.'
        ],
        [
            'id' => 'R002', 
            'diagnosa' => 'BURNOUT SEDANG', 
            'gejala' => ['G001', 'G004', 'G007', 'G008', 'G009'], 
            'cf_pakar' => 0.7,
            'color' => '#F59E0B',
            'bg_light' => '#FFFBEB',
            'desc' => 'Anda menunjukkan tanda-tanda burnout tingkat sedang. Beberapa gejala mulai mengganggu produktivitas. Diperlukan tindakan preventif segera.'
        ],
        [
            'id' => 'R003', 
            'diagnosa' => 'BURNOUT RENDAH', 
            'gejala' => ['G001', 'G004', 'G007'], 
            'cf_pakar' => 0.4,
            'color' => '#10B981',
            'bg_light' => '#F0FFF4',
            'desc' => 'Anda menunjukkan gejala burnout tingkat rendah. Kondisi masih dalam batas normal namun perlu diwaspadai dengan istirahat cukup.'
        ]
    ]
];

return $default_kb;
