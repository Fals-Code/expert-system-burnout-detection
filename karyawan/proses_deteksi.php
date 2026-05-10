<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}

$kb_gejala = $_SESSION['mock_kb']['gejala'];
$kb_aturan = $_SESSION['mock_kb']['aturan'];

$jawaban = [];
$user_cf = [];
$gejala_terdeteksi = [];
$total_skor = 0;

for ($i = 1; $i <= 10; $i++) {
    $gid = 'G' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $val = isset($_POST["q$i"]) ? $_POST["q$i"] : 'Tidak Pernah';
    $jawaban[$i] = $val;
    
    $skor = 0;
    $cf_val = 0.0;
    if ($val === 'Sering') {
        $skor = 2;
        $cf_val = 1.0;
        $nama_g = array_values(array_filter($kb_gejala, fn($g) => $g['id'] === $gid))[0]['nama'] ?? $gid;
        $gejala_terdeteksi[] = $nama_g . ' (Sering)';
    } elseif ($val === 'Kadang') {
        $skor = 1;
        $cf_val = 0.5;
        $nama_g = array_values(array_filter($kb_gejala, fn($g) => $g['id'] === $gid))[0]['nama'] ?? $gid;
        $gejala_terdeteksi[] = $nama_g . ' (Kadang)';
    }
    
    $user_cf[$gid] = $cf_val;
    $total_skor += $skor;
}

$best_rule = null;
$highest_cf = -1.0;

foreach ($kb_aturan as $aturan) {
    $sum_cf = 0;
    $count = count($aturan['gejala']);
    if ($count === 0) continue;
    
    foreach ($aturan['gejala'] as $req_gid) {
        $ev_cf = $user_cf[$req_gid] ?? 0.0;
        $sum_cf += $ev_cf;
    }
    $avg_evidence_cf = $sum_cf / $count;
    $final_cf = $avg_evidence_cf * $aturan['cf_pakar'];
    
    if ($final_cf > $highest_cf) {
        $highest_cf = $final_cf;
        $best_rule = $aturan;
    }
}

if ($highest_cf < 0.2 || !$best_rule) {
    $level    = 'TIDAK ADA';
    $label    = 'TIDAK BURNOUT';
    $color    = '#10B981';
    $bg_light = '#F0FFF4';
    $desc     = 'Selamat! Saat ini Anda tidak menunjukkan gejala burnout yang signifikan berdasarkan basis pengetahuan kami.';
    $confidence = 5;
    $rekomendasi = [
        ['icon' => '🌟', 'judul' => 'Pertahankan Keseimbangan', 'isi' => 'Terus pertahankan work-life balance yang sudah baik.'],
        ['icon' => '📊', 'judul' => 'Deteksi Rutin', 'isi' => 'Lakukan deteksi burnout secara rutin setiap bulan.'],
    ];
} else {
    $level    = str_replace('BURNOUT ', '', $best_rule['diagnosa']);
    $label    = $best_rule['diagnosa'];
    $color    = $best_rule['color'] ?? '#1E3A5F';
    $bg_light = $best_rule['bg_light'] ?? '#F8FAFB';
    $desc     = $best_rule['desc'] ?? 'Tingkat burnout terdeteksi berdasarkan aturan ' . $best_rule['id'];
    $confidence = min(99, max(10, intval($highest_cf * 100)));
    
    if ($level === 'TINGGI') {
        $rekomendasi = [
            ['icon' => '🧘', 'judul' => 'Konseling Psikolog', 'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis.'],
            ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total sangat diperlukan untuk pemulihan.'],
        ];
    } elseif ($level === 'SEDANG') {
        $rekomendasi = [
            ['icon' => '⚖️', 'judul' => 'Manajemen Waktu', 'isi' => 'Prioritaskan tugas-tugas penting dan belajar mendelegasikan.'],
            ['icon' => '🏃', 'judul' => 'Rutinitas Olahraga', 'isi' => 'Aktivitas fisik terbukti dapat mengurangi hormon stres.'],
        ];
    } else {
        $rekomendasi = [
            ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur', 'isi' => 'Pastikan Anda tidur 7-9 jam setiap malam.'],
            ['icon' => '🧘', 'judul' => 'Meditasi & Mindfulness', 'isi' => 'Praktik meditasi efektif mengurangi stres.'],
        ];
    }
}

$_SESSION['hasil_deteksi'] = [
    'level'              => $level,
    'label'              => $label,
    'color'              => $color,
    'bg_light'           => $bg_light,
    'desc'               => $desc,
    'confidence'         => $confidence,
    'total_skor'         => $total_skor,
    'jawaban'            => $jawaban,
    'gejala_terdeteksi'  => $gejala_terdeteksi,
    'rekomendasi'        => $rekomendasi,
    'tanggal'            => date('d F Y'),
    'timestamp'          => date('Y-m-d H:i:s'),
];

header('Location: hasil.php');
exit();
