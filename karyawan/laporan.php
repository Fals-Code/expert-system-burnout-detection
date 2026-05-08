<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = "Budi Santoso";
$tanggal = isset($_GET['tgl']) ? htmlspecialchars($_GET['tgl']) : "12 April 2025";
$level = "TINGGI";
$confidence = 78;
$desc = "Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, penurunan motivasi, dan depersonalisasi.";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Burnout - <?= $nama ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1E3A5F;
            --accent: #F4845F;
            --gray: #6B7E96;
            --light: #F1F4F7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #525659; padding: 40px 0; color: #1A2B40; }

        .report-paper {
            background: #fff; width: 210mm; min-height: 297mm; padding: 25mm;
            margin: 0 auto; box-shadow: 0 0 20px rgba(0,0,0,0.3);
            position: relative;
        }

        /* ── Header Kop ── */
        .report-header {
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 3px solid var(--primary); padding-bottom: 1.5rem; margin-bottom: 2.5rem;
        }
        .brand-kop { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .brand-kop span { color: var(--accent); }
        .report-type { text-align: right; }
        .report-type h1 { font-size: 1rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; }
        .report-type p { font-size: 0.75rem; color: var(--gray); font-weight: 600; }

        /* ── Info Section ── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem; }
        .info-item { margin-bottom: 1rem; }
        .info-label { font-size: 0.75rem; font-weight: 700; color: var(--gray); text-transform: uppercase; margin-bottom: 0.25rem; }
        .info-value { font-size: 1rem; font-weight: 600; color: var(--primary); }

        /* ── Result Section ── */
        .result-box {
            background: #FFF5F5; border: 2px solid #DC3545; border-radius: 12px;
            padding: 2rem; margin-bottom: 3rem; text-align: center;
        }
        .result-level { font-size: 2.5rem; font-weight: 900; color: #DC3545; margin-bottom: 0.5rem; line-height: 1; }
        .result-conf { font-size: 0.9rem; font-weight: 700; color: var(--gray); }

        .section-title { font-size: 1.1rem; font-weight: 800; color: var(--primary); border-bottom: 1px solid var(--light); padding-bottom: 0.5rem; margin-bottom: 1.25rem; text-transform: uppercase; }
        .content-para { font-size: 1rem; line-height: 1.7; color: #4A5E75; margin-bottom: 2rem; }

        /* ── Recommendations ── */
        .rec-list { list-style: none; }
        .rec-item { display: flex; gap: 1rem; margin-bottom: 1.25rem; }
        .rec-bullet { width: 24px; height: 24px; background: var(--primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0; }
        .rec-text h3 { font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem; }
        .rec-text p { font-size: 0.85rem; color: var(--gray); line-height: 1.5; }

        /* ── Footer ── */
        .report-footer { margin-top: 5rem; display: flex; justify-content: space-between; align-items: flex-end; }
        .signature { text-align: center; width: 200px; }
        .signature-line { border-top: 2px solid var(--primary); margin-top: 4rem; padding-top: 0.5rem; font-weight: 700; color: var(--primary); font-size: 0.9rem; }

        /* ── Controls ── */
        .controls { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 1rem; z-index: 200; }
        .btn { padding: 0.8rem 1.5rem; border-radius: 50px; font-weight: 700; cursor: pointer; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: 0.2s; display: flex; align-items: center; gap: 0.6rem; text-decoration: none; font-size: 0.9rem; }
        .btn-print { background: var(--accent); color: #fff; }
        .btn-print:hover { transform: scale(1.05); background: #e06840; }
        .btn-back { background: #fff; color: var(--primary); }

        @media print {
            body { background: #fff; padding: 0; }
            .report-paper { box-shadow: none; width: 100%; margin: 0; padding: 0; }
            .controls { display: none; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>

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
                <div class="info-value">Staff Engineering</div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Laporan</div>
                <div class="info-value">BX-<?= date('Ymd', strtotime($tanggal)) ?>-001</div>
            </div>
        </div>

        <!-- Hasil Deteksi -->
        <div class="result-box">
            <div class="info-label" style="color: #DC3545;">Hasil Diagnosis Utama</div>
            <div class="result-level">BURNOUT <?= $level ?></div>
            <div class="result-conf">Tingkat Keyakinan Sistem: <?= $confidence ?>%</div>
        </div>

        <!-- Deskripsi -->
        <h2 class="section-title">Analisis Kondisi</h2>
        <p class="content-para"><?= $desc ?></p>

        <!-- Rekomendasi -->
        <h2 class="section-title">Rekomendasi Tindak Lanjut</h2>
        <div class="rec-list">
            <div class="rec-item">
                <div class="rec-bullet">1</div>
                <div class="rec-text">
                    <h3>Konseling Profesional</h3>
                    <p>Sangat disarankan untuk melakukan sesi konsultasi dengan psikolog klinis untuk menangani gejala kelelahan emosional yang dialami.</p>
                </div>
            </div>
            <div class="rec-item">
                <div class="rec-bullet">2</div>
                <div class="rec-text">
                    <h3>Istirahat dan Pemulihan</h3>
                    <p>Ambil cuti terencana minimal 3-5 hari kerja untuk melakukan pemulihan energi fisik dan mental jauh dari beban pekerjaan.</p>
                </div>
            </div>
            <div class="rec-item">
                <div class="rec-bullet">3</div>
                <div class="rec-text">
                    <h3>Penyesuaian Beban Kerja</h3>
                    <p>Segera diskusikan hasil laporan ini dengan Manajer atau HRD untuk merestrukturisasi tugas dan tanggung jawab sementara waktu.</p>
                </div>
            </div>
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
