<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user    = $_SESSION['user'];
$user_id = $user['id'] ?? 'U_DEMO_K';
$nama    = $user['nama'];

// ── Tentukan sumber data laporan ──
// Prioritas 1: ?id= dari URL (buka laporan entri riwayat tertentu)
// Prioritas 2: $_SESSION['hasil_deteksi'] (laporan terbaru langsung dari deteksi)
$hasil = null;
$report_id  = $_GET['id'] ?? null;
$report_tgl = $_GET['tgl'] ?? null;

if ($report_id || $report_tgl) {
    // Cari di riwayat user berdasarkan report_id atau tgl
    $history = get_user_history($user_id);
    foreach ($history as $h) {
        if ($report_id && ($h['id'] ?? '') === $report_id) {
            $hasil = $h;
            break;
        }
        // Jika pakai tgl, bandingkan tanggal Y-m-d
        if ($report_tgl && date('Y-m-d', strtotime($h['timestamp'] ?? '')) === $report_tgl) {
            $hasil = $h;
            break;
        }
    }
    if (!$hasil && $report_id === 'latest') {
        // Fallback: jika tidak ditemukan di history tapi minta 'latest', cek session
        $hasil = $_SESSION['hasil_deteksi'] ?? null;
    }
} else {
    // Tidak ada parameter → gunakan hasil deteksi terbaru dari session
    $hasil = $_SESSION['hasil_deteksi'] ?? null;
}

if (!$hasil) {
    header('Location: riwayat.php');
    exit();
}

$tanggal      = $hasil['tanggal'];
$level        = $hasil['level'];
$label        = $hasil['label'];
$confidence   = $hasil['confidence'];
$desc         = $hasil['desc'];
$rekomendasi  = $hasil['rekomendasi'];
$color        = $hasil['color'];
$bg_light     = $hasil['bg_light'];
$gejala_list  = $hasil['gejala_terdeteksi'] ?? [];
$rid          = $hasil['id'] ?? ('BX-' . date('Ymd') . '-' . rand(100, 999));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Burnout – <?= htmlspecialchars($nama) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/report.css">
</head>
<body class="report-body">

    <div class="controls">
        <a href="riwayat.php" class="btn btn-back">← Kembali ke Riwayat</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Laporan</button>
    </div>

    <div class="report-paper">
        <!-- Kop Surat -->
        <header class="report-header">
            <div class="brand-kop">Burnout<span>Xpert</span></div>
            <div class="report-type">
                <h1>Laporan Analisis Burnout</h1>
                <p>Dokumen Resmi Hasil Deteksi Sistem Pakar</p>
            </div>
        </header>

        <!-- Informasi Karyawan -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Karyawan</div>
                <div class="info-value"><?= htmlspecialchars($nama) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Deteksi</div>
                <div class="info-value"><?= htmlspecialchars($tanggal) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Divisi / Posisi</div>
                <div class="info-value">
                    <?= htmlspecialchars($user['divisi'] ?? '-') ?>
                    <?= isset($user['posisi']) ? ' / ' . htmlspecialchars($user['posisi']) : '' ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Laporan</div>
                <div class="info-value" style="font-family: monospace; font-size: 0.9em;"><?= htmlspecialchars($rid) ?></div>
            </div>
        </div>

        <!-- Hasil Deteksi -->
        <div class="result-box" style="border-left: 4px solid <?= $color ?>; background: <?= $bg_light ?>;">
            <div class="info-label" style="color: <?= $color ?>;">Hasil Diagnosis Utama</div>
            <div class="result-level" style="color: <?= $color ?>;"><?= htmlspecialchars($label) ?></div>
            <div class="result-conf">Tingkat Keyakinan Sistem: <strong><?= $confidence ?>%</strong></div>
        </div>

        <!-- Deskripsi -->
        <h2 class="section-title">Analisis Kondisi</h2>
        <p class="content-para"><?= htmlspecialchars($desc) ?></p>

        <!-- Gejala Terdeteksi -->
        <?php if (!empty($gejala_list)): ?>
        <h2 class="section-title">Gejala yang Terdeteksi</h2>
        <ul class="content-para" style="padding-left: 1.5rem; line-height: 2;">
            <?php foreach ($gejala_list as $g): ?>
            <li><?= htmlspecialchars($g) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- Rekomendasi -->
        <h2 class="section-title">Rekomendasi Tindak Lanjut</h2>
        <div class="rec-list">
            <?php foreach ($rekomendasi as $index => $rec): ?>
            <div class="rec-item">
                <div class="rec-bullet"><?= $index + 1 ?></div>
                <div class="rec-text">
                    <h3><?= htmlspecialchars($rec['icon'] . ' ' . $rec['judul']) ?></h3>
                    <p><?= htmlspecialchars($rec['isi']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Penutup / Tanda Tangan -->
        <div class="report-footer">
            <div style="font-size: 0.7rem; color: var(--gray); max-width: 320px; line-height: 1.6;">
                *Laporan ini dihasilkan secara otomatis oleh Sistem Pakar BurnoutXpert menggunakan metode
                <em>Backward Chaining</em> dengan algoritma <em>Certainty Factor</em>.
                Hasil ini bersifat indikatif dan tidak menggantikan diagnosis profesional.
            </div>
            <div class="signature">
                <div style="font-size: 0.8rem; margin-bottom: 0.5rem;">Dicetak pada: <?= date('d M Y H:i') ?></div>
                <div class="signature-line">Sistem Pakar BurnoutXpert v2.0</div>
            </div>
        </div>
    </div>

</body>
</html>
