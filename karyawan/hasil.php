<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$page_title = "Hasil Analisis";
$active_menu = 'deteksi';
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
    <title>Hasil Diagnosis – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

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
                <div class="level-label" style="color: <?= $color ?>;"><?= $label ?></div>
                <p class="condition-desc"><?= $desc ?></p>
            </div>
            <div class="circular-progress">
                <svg viewBox="0 0 180 180">
                    <circle class="bg" cx="90" cy="90" r="80"></circle>
                    <circle class="fg" id="progressCircle" cx="90" cy="90" r="80" style="stroke: <?= $color ?>;"></circle>
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
