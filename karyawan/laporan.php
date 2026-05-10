<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];

// Ambil data deteksi dari session
if (!isset($_SESSION['hasil_deteksi'])) {
    header('Location: deteksi.php');
    exit();
}

$hasil = $_SESSION['hasil_deteksi'];
$tanggal = $hasil['tanggal'];
$level   = $hasil['level'];
$confidence = $hasil['confidence'];
$desc    = $hasil['desc'];
$rekomendasi = $hasil['rekomendasi'];
$color   = $hasil['color'];
$bg_light = $hasil['bg_light'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Burnout - <?= $nama ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/report.css">
</head>
<body class="report-body">

    <div class="controls">
        <a href="riwayat.php" class="btn btn-back">Kembali</a>
        <button onclick="window.print()" class="btn btn-print">Cetak Laporan</button>
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
                <div class="info-value"><?= $nama ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Deteksi</div>
                <div class="info-value"><?= $tanggal ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Divisi / Posisi</div>
                <div class="info-value"><?= htmlspecialchars($user['divisi']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Laporan</div>
                <div class="info-value">BX-<?= date('Ymd', strtotime($tanggal)) ?>-001</div>
            </div>
        </div>

        <!-- Hasil Deteksi -->
        <div class="result-box">
            <div class="info-label" style="color: <?= $color ?>;">Hasil Diagnosis Utama</div>
            <div class="result-level">BURNOUT <?= $level ?></div>
            <div class="result-conf">Tingkat Keyakinan Sistem: <?= $confidence ?>%</div>
        </div>

        <!-- Deskripsi -->
        <h2 class="section-title">Analisis Kondisi</h2>
        <p class="content-para"><?= $desc ?></p>

        <!-- Rekomendasi -->
        <h2 class="section-title">Rekomendasi Tindak Lanjut</h2>
        <div class="rec-list">
            <?php foreach ($rekomendasi as $index => $rec): ?>
            <div class="rec-item">
                <div class="rec-bullet"><?= $index + 1 ?></div>
                <div class="rec-text">
                    <h3><?= htmlspecialchars($rec['judul']) ?></h3>
                    <p><?= htmlspecialchars($rec['isi']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Penutup / Tanda Tangan -->
        <div class="report-footer">
            <div style="font-size: 0.7rem; color: var(--gray); max-width: 300px;">
                *Laporan ini dihasilkan secara otomatis oleh Sistem Pakar BurnoutXpert berdasarkan input data gejala yang diberikan oleh pengguna.
            </div>
            <div class="signature">
                <div style="font-size: 0.8rem; margin-bottom: 0.5rem;">Dicetak pada: <?= date('d M Y H:i') ?></div>
                <div class="signature-line">Sistem Pakar BurnoutXpert</div>
            </div>
        </div>
    </div>

</body>
</html>
