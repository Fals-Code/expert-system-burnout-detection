<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'riwayat';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Mock Result Data (Dapat diganti dengan data dari proses_deteksi.php)
$level = "TINGGI"; // TINGGI, SEDANG, RENDAH
$confidence = 78;
$label = "BURNOUT TINGGI";
$color = "#DC3545"; // Red for High
$bg_light = "#FFF5F5";
$desc = "Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, penurunan motivasi, dan depersonalisasi.";

if ($level == "SEDANG") {
    $color = "#FFC107";
    $bg_light = "#FFFBEB";
    $label = "BURNOUT SEDANG";
} elseif ($level == "RENDAH") {
    $color = "#28A745";
    $bg_light = "#F0FFF4";
    $label = "BURNOUT RENDAH";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        /* ── Main Wrapper ── */
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top Bar ── */
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }

        /* ── Result Content ── */
        .result-container { max-width: 900px; margin: 2rem auto; padding: 0 1.5rem; width: 100%; }

        /* Header Hasil */
        .result-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
        .header-icon { width: 48px; height: 48px; background: #FFF5F5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #DC3545; font-size: 1.5rem; }
        .header-text h1 { font-size: 1.5rem; font-weight: 800; color: var(--color-primary); }
        .header-text p { font-size: 0.875rem; color: var(--color-gray-500); }

        /* Main Card Result */
        .main-result-card {
            background: #fff; border-radius: 24px; padding: 3rem; box-shadow: var(--shadow-lg);
            border: 1px solid var(--color-gray-100); position: relative; overflow: hidden;
            display: grid; grid-template-columns: 1fr 200px; gap: 2rem; align-items: center;
            margin-bottom: 2.5rem;
        }
        .main-result-card::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: <?= $color ?>; }

        .result-info h2 { font-size: 0.875rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .result-info .level-label { font-size: 2.5rem; font-weight: 900; color: <?= $color ?>; margin-bottom: 1.5rem; line-height: 1; }
        .result-info .condition-desc { font-size: 1.1rem; color: var(--color-gray-600); line-height: 1.6; border-top: 1px solid var(--color-gray-100); padding-top: 1.5rem; }

        /* Circular Progress */
        .circular-progress { position: relative; width: 180px; height: 180px; display: flex; align-items: center; justify-content: center; }
        .circular-progress svg { transform: rotate(-90deg); width: 100%; height: 100%; }
        .circular-progress circle { fill: none; stroke-width: 12; stroke-linecap: round; }
        .circular-progress .bg { stroke: var(--color-gray-100); }
        .circular-progress .fg { stroke: <?= $color ?>; stroke-dasharray: 502; stroke-dashoffset: <?= 502 * (1 - $confidence/100) ?>; transition: stroke-dashoffset 1s ease-out; }
        .progress-val { position: absolute; text-align: center; }
        .progress-val .percent { font-size: 2.25rem; font-weight: 800; color: var(--color-primary); display: block; }
        .progress-val .txt { font-size: 0.75rem; font-weight: 600; color: var(--color-gray-400); text-transform: uppercase; }

        /* Recommendations */
        .section-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        
        .recommendation-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 3rem; }
        .rec-card {
            background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--color-gray-100);
            transition: all 0.2s;
        }
        .rec-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--color-primary-100); }
        .rec-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--color-gray-50); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1rem; color: var(--color-primary); }
        .rec-card h3 { font-size: 1rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem; }
        .rec-card p { font-size: 0.85rem; color: var(--color-gray-500); line-height: 1.5; }

        /* Action Buttons */
        .action-group { display: flex; gap: 1rem; justify-content: center; border-top: 1px solid var(--color-gray-200); padding-top: 2rem; }
        .btn-action { padding: 0.875rem 2rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; transition: all 0.2s; text-decoration: none; font-size: 0.95rem; }
        
        .btn-download { background: var(--color-primary); color: #fff; border: none; }
        .btn-download:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

        .btn-back { background: #fff; color: var(--color-gray-700); border: 2px solid var(--color-gray-200); }
        .btn-back:hover { background: var(--color-gray-50); border-color: var(--color-gray-300); }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .main-result-card { grid-template-columns: 1fr; padding: 2rem; text-align: center; }
            .main-result-card::before { width: 100%; height: 6px; top: 0; left: 0; }
            .circular-progress { margin: 0 auto; }
            .recommendation-grid { grid-template-columns: 1fr; }
            .action-group { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="topbar__title">Hasil Diagnosis</div>
        </div>
        <div style="font-size: 0.875rem; color: var(--color-gray-500);">Laporan Medis #BX-<?= date('Ymd') ?></div>
    </header>

    <main class="result-container">
        <div class="result-header">
            <div class="header-icon">🔥</div>
            <div class="header-text">
                <h1>Hasil Deteksi Burnout Anda</h1>
                <p>Berdasarkan analisis sistem pakar terhadap gejala yang Anda laporkan.</p>
            </div>
        </div>

        <div class="main-result-card">
            <div class="result-info">
                <h2>Tingkat Burnout</h2>
                <div class="level-label"><?= $label ?></div>
                <p class="condition-desc"><?= $desc ?></p>
            </div>
            <div class="circular-progress">
                <svg viewBox="0 0 180 180">
                    <circle class="bg" cx="90" cy="90" r="80"></circle>
                    <circle class="fg" cx="90" cy="90" r="80"></circle>
                </svg>
                <div class="progress-val">
                    <span class="percent"><?= $confidence ?>%</span>
                    <span class="txt">Keyakinan</span>
                </div>
            </div>
        </div>

        <h2 class="section-title">✨ Rekomendasi Penanganan</h2>
        <div class="recommendation-grid">
            <div class="rec-card">
                <div class="rec-icon">🧘</div>
                <h3>Konseling Psikolog</h3>
                <p>Sangat disarankan untuk berkonsultasi dengan profesional guna mendapatkan penanganan emosional yang tepat.</p>
            </div>
            <div class="rec-card">
                <div class="rec-icon">✈️</div>
                <h3>Ambil Cuti Terencana</h3>
                <p>Istirahat total selama beberapa hari dapat membantu memulihkan energi fisik dan mental Anda.</p>
            </div>
            <div class="rec-card">
                <div class="rec-icon">🤝</div>
                <h3>Diskusi dengan HRD</h3>
                <p>Komunikasikan beban kerja Anda dengan HRD atau atasan untuk mencari solusi penyesuaian tugas.</p>
            </div>
        </div>

        <div class="action-group">
            <a href="#" class="btn-action btn-download">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Unduh Hasil (PDF)
            </a>
            <a href="dashboard.php" class="btn-action btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </main>
</div>

</body>
</html>
