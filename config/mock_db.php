<?php
// ============================================================
//  BurnoutXpert – Mock Knowledge Base (Backward Chaining + CF)
//  Versi 2.0 — 20 Gejala, 6 Aturan
// ============================================================

$default_kb = [
    'gejala' => [
        // Kategori: Fisik
        ['id' => 'G001', 'nama' => 'Kelelahan Fisik Berkepanjangan',        'kategori' => 'fisik',      'bobot' => 0.85],
        ['id' => 'G002', 'nama' => 'Sakit Kepala atau Migrain Sering',       'kategori' => 'fisik',      'bobot' => 0.70],
        ['id' => 'G003', 'nama' => 'Gangguan Tidur (Insomnia/Hipersomnia)',  'kategori' => 'fisik',      'bobot' => 0.75],
        ['id' => 'G004', 'nama' => 'Penurunan Imunitas (Sering Sakit)',      'kategori' => 'fisik',      'bobot' => 0.65],
        ['id' => 'G005', 'nama' => 'Beban Kerja Fisik Berlebih',             'kategori' => 'fisik',      'bobot' => 0.60],
        // Kategori: Emosional
        ['id' => 'G006', 'nama' => 'Kelelahan Emosional Mendalam',           'kategori' => 'emosional',  'bobot' => 0.92],
        ['id' => 'G007', 'nama' => 'Sikap Sinis terhadap Pekerjaan',         'kategori' => 'emosional',  'bobot' => 0.80],
        ['id' => 'G008', 'nama' => 'Merasa Tidak Dihargai atau Diabaikan',   'kategori' => 'emosional',  'bobot' => 0.70],
        ['id' => 'G009', 'nama' => 'Putus Asa terhadap Target Kerja',        'kategori' => 'emosional',  'bobot' => 0.90],
        ['id' => 'G010', 'nama' => 'Rasa Cemas Berlebih terkait Pekerjaan',  'kategori' => 'emosional',  'bobot' => 0.75],
        // Kategori: Perilaku
        ['id' => 'G011', 'nama' => 'Depersonalisasi (Tidak Peduli/Apatis)',  'kategori' => 'perilaku',   'bobot' => 0.88],
        ['id' => 'G012', 'nama' => 'Penurunan Prestasi & Produktivitas',     'kategori' => 'perilaku',   'bobot' => 0.78],
        ['id' => 'G013', 'nama' => 'Menghindari Tanggungjawab Kerja',        'kategori' => 'perilaku',   'bobot' => 0.72],
        ['id' => 'G014', 'nama' => 'Isolasi Diri dari Rekan Kerja',          'kategori' => 'perilaku',   'bobot' => 0.68],
        ['id' => 'G015', 'nama' => 'Terlambat atau Sering Absen',            'kategori' => 'perilaku',   'bobot' => 0.60],
        // Kategori: Kognitif
        ['id' => 'G016', 'nama' => 'Sulit Berkonsentrasi & Fokus',           'kategori' => 'kognitif',   'bobot' => 0.72],
        ['id' => 'G017', 'nama' => 'Pelupa dan Sering Membuat Kesalahan',    'kategori' => 'kognitif',   'bobot' => 0.65],
        ['id' => 'G018', 'nama' => 'Sulit Membuat Keputusan',                'kategori' => 'kognitif',   'bobot' => 0.70],
        ['id' => 'G019', 'nama' => 'Sulit Memulai atau Menyelesaikan Tugas', 'kategori' => 'kognitif',   'bobot' => 0.75],
        ['id' => 'G020', 'nama' => 'Hilang Kreativitas & Inisiatif',         'kategori' => 'kognitif',   'bobot' => 0.68],
    ],
    'aturan' => [
        // ── R001: BURNOUT TINGGI (Kelelahan total: fisik + emosional + perilaku)
        [
            'id'       => 'R001',
            'diagnosa' => 'BURNOUT TINGGI',
            'gejala'   => ['G001', 'G006', 'G007', 'G009', 'G011', 'G012', 'G016'],
            'cf_pakar' => 0.95,
            'color'    => '#DC3545',
            'bg_light' => '#FFF5F5',
            'desc'     => 'Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, depersonalisasi, dan penurunan motivasi signifikan. Kondisi ini memerlukan perhatian dan penanganan segera dari profesional.'
        ],
        // ── R002: BURNOUT TINGGI (Variasi: isolasi + putus asa + kognitif terganggu)
        [
            'id'       => 'R002',
            'diagnosa' => 'BURNOUT TINGGI',
            'gejala'   => ['G006', 'G009', 'G011', 'G013', 'G014', 'G018'],
            'cf_pakar' => 0.88,
            'color'    => '#DC3545',
            'bg_light' => '#FFF5F5',
            'desc'     => 'Anda menunjukkan pola burnout tinggi dengan ciri isolasi sosial, perasaan putus asa yang mendalam, dan kesulitan kognitif yang signifikan. Intervensi segera sangat dianjurkan.'
        ],
        // ── R003: BURNOUT SEDANG (Beban kerja + emosional mulai terganggu)
        [
            'id'       => 'R003',
            'diagnosa' => 'BURNOUT SEDANG',
            'gejala'   => ['G001', 'G005', 'G008', 'G010', 'G016', 'G019'],
            'cf_pakar' => 0.75,
            'color'    => '#F59E0B',
            'bg_light' => '#FFFBEB',
            'desc'     => 'Anda menunjukkan tanda-tanda burnout tingkat sedang. Beberapa gejala mulai mengganggu produktivitas dan kesejahteraan. Diperlukan tindakan preventif segera untuk mencegah eskalasi.'
        ],
        // ── R004: BURNOUT SEDANG (Variasi: fisik + perilaku awal)
        [
            'id'       => 'R004',
            'diagnosa' => 'BURNOUT SEDANG',
            'gejala'   => ['G002', 'G003', 'G007', 'G012', 'G017', 'G020'],
            'cf_pakar' => 0.70,
            'color'    => '#F59E0B',
            'bg_light' => '#FFFBEB',
            'desc'     => 'Anda menunjukkan gejala burnout sedang dengan gangguan fisik dan penurunan kreativitas. Kondisi ini perlu dikelola sebelum memburuk lebih lanjut.'
        ],
        // ── R005: BURNOUT RENDAH (Gejala awal / ringan)
        [
            'id'       => 'R005',
            'diagnosa' => 'BURNOUT RENDAH',
            'gejala'   => ['G001', 'G005', 'G016'],
            'cf_pakar' => 0.50,
            'color'    => '#3B82F6',
            'bg_light' => '#EFF6FF',
            'desc'     => 'Anda menunjukkan gejala burnout tingkat rendah berupa kelelahan fisik dan beban kerja awal. Kondisi masih dalam batas dapat dikelola namun perlu diwaspadai dan ditangani sejak dini.'
        ],
        // ── R006: BURNOUT RENDAH (Variasi: kognitif ringan + tidur terganggu)
        [
            'id'       => 'R006',
            'diagnosa' => 'BURNOUT RENDAH',
            'gejala'   => ['G003', 'G004', 'G019'],
            'cf_pakar' => 0.45,
            'color'    => '#3B82F6',
            'bg_light' => '#EFF6FF',
            'desc'     => 'Anda menunjukkan beberapa gejala awal burnout berupa gangguan tidur dan kesulitan memulai tugas. Istirahat yang cukup dan manajemen stres dini sangat dianjurkan.'
        ],
    ]
];

return $default_kb;
