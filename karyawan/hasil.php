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

// Ambil hasil deteksi dari session
if (!isset($_SESSION['hasil_deteksi'])) {
    header('Location: deteksi.php');
    exit();
}

$hasil = $_SESSION['hasil_deteksi'];
$level      = $hasil['level'];
$confidence = $hasil['confidence'];
$label      = $hasil['label'];
$color      = $hasil['color'];
$bg_light   = $hasil['bg_light'];
$desc       = $hasil['desc'];
$gejala_terdeteksi = $hasil['gejala_terdeteksi'];
$rekomendasi       = $hasil['rekomendasi'];
$tanggal_deteksi   = $hasil['tanggal'];

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
        .circular-progress .fg { stroke: <?= $color ?>; stroke-dasharray: 502; stroke-dashoffset: 502; transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .progress-val { position: absolute; text-align: center; }
        .progress-val .percent { font-size: 2.25rem; font-weight: 800; color: var(--color-primary); display: block; }
        .progress-val .txt { font-size: 0.75rem; font-weight: 600; color: var(--color-gray-400); text-transform: uppercase; }

        /* ── Symptoms Pills ── */
        .symptoms-section { margin-bottom: 3rem; }
        .pill-group { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.25rem; }
        .pill { padding: 0.6rem 1.25rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 0.6rem; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); transition: 0.2s; }
        .pill:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .pill--red { background: #FFF5F5; color: #DC3545; }
        .pill--yellow { background: #FFFBEB; color: #D97706; }
        .pill-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

        /* ── Tooltip ── */
        .tooltip-trigger { cursor: help; border-bottom: 1.5px dashed var(--color-gray-300); position: relative; transition: 0.2s; }
        .tooltip-trigger:hover { color: var(--color-primary); border-color: var(--color-primary); }
        .tooltip-box {
            visibility: hidden; position: absolute; bottom: 140%; left: 50%; transform: translateX(-50%);
            width: 240px; background: #1E3A5F; color: #fff; text-align: center; padding: 1rem;
            border-radius: 12px; font-size: 0.75rem; line-height: 1.5; z-index: 100; opacity: 0; transition: 0.3s;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .tooltip-box::after { content: ""; position: absolute; top: 100%; left: 50%; margin-left: -8px; border-width: 8px; border-style: solid; border-color: #1E3A5F transparent transparent transparent; }
        .tooltip-trigger:hover .tooltip-box { visibility: visible; opacity: 1; }

        /* ── Accordion Recommendations ── */
        .recommendation-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 3rem; }
        .accordion-item { background: #fff; border: 1px solid var(--color-gray-100); border-radius: 20px; overflow: hidden; transition: 0.3s; }
        .accordion-item:hover { border-color: var(--color-primary-100); box-shadow: var(--shadow-md); }
        .accordion-header { padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
        .accordion-left { display: flex; align-items: center; gap: 1.5rem; }
        .priority-badge { font-size: 0.65rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 6px; background: var(--color-primary); color: #fff; text-transform: uppercase; letter-spacing: 0.05em; }
        .accordion-header h3 { font-size: 1.1rem; font-weight: 700; color: var(--color-primary); }
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: #fafafa; }
        .accordion-content-inner { padding: 1.5rem 2rem 2rem 5rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6; }
        .accordion-item.active .accordion-content { max-height: 350px; }
        .chevron { transition: 0.3s; color: var(--color-gray-400); }
        .accordion-item.active .chevron { transform: rotate(180deg); color: var(--color-primary); }

        /* ── Next Steps Timeline ── */
        .next-steps-timeline { margin-top: 5rem; padding: 4rem 3rem; background: linear-gradient(135deg, #1E3A5F 0%, #162B46 100%); border-radius: 32px; color: #fff; text-align: center; }
        .timeline-header h2 { font-size: 1.75rem; font-weight: 800; margin-bottom: 1rem; color: #fff; }
        .timeline-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3rem; margin-top: 4rem; position: relative; }
        .timeline-grid::before { content: ''; position: absolute; top: 25px; left: 15%; right: 15%; height: 2px; background: rgba(255,255,255,0.1); z-index: 0; }
        .timeline-item-wrap { position: relative; z-index: 1; }
        .timeline-step { width: 50px; height: 50px; border-radius: 50%; background: var(--color-accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800; margin: 0 auto 1.5rem; border: 4px solid #1E3A5F; }
        .timeline-item-wrap h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; }
        .timeline-item-wrap p { font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.5; margin-bottom: 1.5rem; }
        .timeline-action-btn { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 0.75rem 1.25rem; border-radius: 12px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; }
        .timeline-action-btn:hover { background: #fff; color: var(--color-primary); transform: translateY(-3px); }

        .section-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .action-group { display: flex; gap: 1rem; justify-content: center; border-top: 1px solid var(--color-gray-200); padding-top: 2rem; }
        .btn-action { padding: 0.875rem 2rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; transition: all 0.2s; text-decoration: none; font-size: 0.95rem; }
        .btn-download { background: var(--color-primary); color: #fff; border: none; }
        .btn-download:hover { background: var(--color-primary-dark); transform: translateY(-2px); }
        .btn-back { background: #fff; color: var(--color-gray-700); border: 2px solid var(--color-gray-200); }
        .btn-back:hover { background: var(--color-gray-50); border-color: var(--color-gray-300); }

        @media (max-width: 992px) {
            .timeline-grid { grid-template-columns: 1fr; gap: 3rem; }
            .timeline-grid::before { display: none; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .main-result-card { grid-template-columns: 1fr; padding: 2rem; text-align: center; }
            .main-result-card::before { width: 100%; height: 6px; top: 0; left: 0; }
            .circular-progress { margin: 0 auto; }
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
                    <circle class="fg" id="progressCircle" cx="90" cy="90" r="80"></circle>
                </svg>
                <div class="progress-val">
                    <span class="percent" id="confidenceCounter">0%</span>
                    <span class="txt tooltip-trigger">
                        Akurasi Analisis
                        <span class="tooltip-box">Persentase ini menunjukkan seberapa kuat sistem mengidentifikasi pola burnout dari jawaban Anda.</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Gejala yang Teridentifikasi -->
        <div class="symptoms-section">
            <h2 class="section-title">🔍 Gejala yang Teridentifikasi</h2>
            <div class="pill-group">
                <?php if (empty($gejala_terdeteksi)): ?>
                    <p style="color: var(--color-gray-400); font-size: 0.9rem; font-style: italic;">Tidak ada gejala spesifik yang terdeteksi.</p>
                <?php else: ?>
                    <?php foreach ($gejala_terdeteksi as $g): ?>
                        <div class="pill" style="background: <?= $bg_light ?>; color: <?= $color ?>;">
                            <span class="pill-dot"></span> <?= htmlspecialchars($g) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="section-title">✨ Rekomendasi Penanganan</h2>
        <div class="recommendation-list">
            <?php foreach ($rekomendasi as $index => $rec): ?>
            <div class="accordion-item <?= $index === 0 ? 'active' : '' ?>">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <div class="accordion-left">
                        <span class="priority-badge">Prioritas <?= $index + 1 ?></span>
                        <div class="rec-icon" style="margin-bottom:0;"><?= $rec['icon'] ?></div>
                        <h3><?= htmlspecialchars($rec['judul']) ?></h3>
                    </div>
                    <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="accordion-content">
                    <div class="accordion-content-inner">
                        <?= htmlspecialchars($rec['isi']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="action-group">
            <a href="laporan.php?tgl=<?= urlencode($tanggal_deteksi) ?>" class="btn-action btn-download" target="_blank">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Unduh PDF
            </a>
            <a href="dashboard.php" class="btn-action btn-back">
                Dashboard
            </a>
        </div>

        <!-- Langkah Selanjutnya Timeline -->
        <div class="next-steps-timeline">
            <div class="timeline-header">
                <h2>Langkah Selanjutnya</h2>
                <p>Ikuti panduan ini untuk memulai proses pemulihan Anda</p>
            </div>
            <div class="timeline-grid">
                <div class="timeline-item-wrap">
                    <div class="timeline-step">1</div>
                    <h4>Simpan Laporan</h4>
                    <p>Unduh hasil deteksi ini untuk referensi pribadi atau diskusi medis.</p>
                    <a href="laporan.php?tgl=<?= urlencode($tanggal_deteksi) ?>" class="timeline-action-btn" target="_blank">Download Laporan</a>
                </div>
                <div class="timeline-item-wrap">
                    <div class="timeline-step">2</div>
                    <h4>Konseling</h4>
                    <p>Jadwalkan sesi pertama dengan psikolog untuk evaluasi lebih mendalam.</p>
                    <button class="timeline-action-btn" onclick="alert('Fitur pencarian psikolog akan segera hadir!')">Cari Psikolog</button>
                </div>
                <div class="timeline-item-wrap">
                    <div class="timeline-step">3</div>
                    <h4>Follow-up</h4>
                    <p>Lakukan pemeriksaan rutin setiap 30 hari untuk memantau progres Anda.</p>
                    <button class="timeline-action-btn" onclick="alert('Pengingat telah diset untuk 30 hari ke depan.')">Set Pengingat</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Animation for circular progress
    function animateProgress() {
        const target = <?= $confidence ?>;
        const circle = document.getElementById('progressCircle');
        const counter = document.getElementById('confidenceCounter');
        const duration = 2000;
        const startTime = performance.now();
        
        const circumference = 502; // 2 * pi * 80

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out expo
            const easedProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            
            const currentValue = Math.floor(easedProgress * target);
            counter.innerText = currentValue + '%';
            
            const offset = circumference * (1 - (easedProgress * target / 100));
            circle.style.strokeDashoffset = offset;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        circle.style.strokeDasharray = circumference;
        requestAnimationFrame(update);
    }

    function toggleAccordion(header) {
        const item = header.parentElement;
        const wasActive = item.classList.contains('active');
        
        document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
        
        if (!wasActive) {
            item.classList.add('active');
        }
    }

    window.addEventListener('DOMContentLoaded', animateProgress);
</script>

</body>
</html>
