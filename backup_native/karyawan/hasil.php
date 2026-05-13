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
$tracing           = $hasil['tracing'] ?? [];
$bc_trace          = $hasil['bc_trace'] ?? [];



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
            <button type="button" onclick="openTracingModal()" class="btn-action" style="background:var(--color-primary); color:white; border:none; cursor:pointer; padding:0.8rem 1.5rem; border-radius:50px; font-weight:700; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Detail Kalkulasi
            </button>
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
                    <?php if ($level === 'TINGGI'): ?>
                        <a href="mailto:hrd@burnoutxpert.com?subject=Permintaan Jadwal Konseling - <?= urlencode($nama) ?>&body=Halo Tim HRD,%0A%0ASaya <?= urlencode($nama) ?> ingin mengajukan jadwal konseling terkait hasil deteksi kesehatan mental saya.%0A%0ATerima kasih." class="timeline-action-btn" style="text-decoration:none; display:inline-block; text-align:center; background:var(--color-error); color:white; border:none;">Ajukan Konseling ke HRD</a>
                    <?php else: ?>
                        <button class="timeline-action-btn" onclick="alert('Fitur pencarian psikolog akan segera hadir!')">Cari Psikolog</button>
                    <?php endif; ?>
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

<!-- Modal Tracing -->
<div class="modal-overlay" id="modalTracing">
    <div class="modal-content" style="background:white; border-radius:16px; width:90%; max-width:600px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="modal-header" style="padding:1.5rem; border-bottom:1px solid var(--color-gray-200); display:flex; justify-content:space-between; align-items:center; background:var(--color-primary); color:white;">
            <h3 style="margin:0; font-size:1.2rem; font-weight:700;">Transparansi Perhitungan Pakar</h3>
            <button type="button" onclick="closeTracingModal()" style="background:transparent; border:none; color:white; font-size:1.5rem; cursor:pointer; padding:0; line-height:1;">&times;</button>
        </div>
        <div class="modal-body" style="padding:1.5rem; overflow-y:auto; background:#f8fafc;">

            <?php if (!empty($bc_trace)): ?>
            <!-- BC CHAIN: Alur Backward Chaining -->
            <div style="background:white; padding:1.25rem; border-radius:12px; border:1px solid var(--color-gray-200); margin-bottom:1rem;">
                <h4 style="margin:0 0 1rem 0; color:var(--color-primary); font-size:1rem;">🔄 Alur Backward Chaining</h4>
                <div style="position:relative; padding-left: 1.5rem;">
                    <div style="position:absolute; left:6px; top:0; bottom:0; width:2px; background:var(--color-gray-100);"></div>
                    <?php foreach ($bc_trace as $idx => $trace): ?>
                    <div style="position:relative; margin-bottom:1rem;">
                        <div style="position:absolute; left:-1.5rem; top:4px; width:12px; height:12px; border-radius:50%; border:2px solid white; background:<?= $trace['confirmed'] ? '#10B981' : '#DC3545' ?>; box-shadow:0 0 0 2px <?= $trace['confirmed'] ? '#10B981' : '#DC354530' ?>;"></div>
                        <div style="font-size:0.85rem;">
                            <span style="font-weight:700; color:var(--color-primary);">Langkah <?= $idx + 1 ?>:</span>
                            Uji hipotesis <strong><?= htmlspecialchars($trace['goal']) ?></strong> (<?= htmlspecialchars($trace['rule_id']) ?>)
                        </div>
                        <div style="font-size:0.8rem; margin-top:0.2rem; color:<?= $trace['confirmed'] ? '#10B981' : '#DC3545' ?>; font-weight:600;">
                            <?= htmlspecialchars($trace['note']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($tracing)): ?>
                <div style="background:white; padding:1.25rem; border-radius:12px; border:1px solid var(--color-gray-200); margin-bottom:1rem;">
                    <h4 style="margin:0 0 0.75rem 0; color:var(--color-primary); font-size:1rem;">📐 Rule Dominan yang Terkonfirmasi</h4>
                    <p style="margin:0; font-size:0.95rem;">Kode Rule: <strong style="color:var(--color-accent);"><?= htmlspecialchars($tracing['rule_id'] ?? '-') ?></strong></p>
                    <p style="margin:0.25rem 0 0 0; font-size:0.95rem;">Bobot Kepastian Pakar (CF Pakar): <strong><?= number_format($tracing['cf_pakar'] ?? 0, 2) ?></strong></p>
                </div>

                <div style="background:white; padding:1.25rem; border-radius:12px; border:1px solid var(--color-gray-200); margin-bottom:1rem;">
                    <h4 style="margin:0 0 0.75rem 0; color:var(--color-primary); font-size:1rem;">2. Rincian Gejala & Bobot Jawaban (CF User)</h4>
                    <ul style="margin:0; padding-left:1.2rem; font-size:0.9rem; color:var(--color-gray-600); line-height:1.6;">
                        <?php foreach ($tracing['details'] as $detail): ?>
                            <li><?= htmlspecialchars($detail) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--color-gray-200);">
                        <p style="margin:0; font-size:0.95rem;">Rata-rata Bobot Gejala (Avg CF Gejala): <strong><?= number_format($tracing['avg_gejala_cf'] ?? 0, 2) ?></strong></p>
                    </div>
                </div>

                <div style="background:var(--color-primary); color:white; padding:1.25rem; border-radius:12px;">
                    <h4 style="margin:0 0 0.5rem 0; font-size:1rem; color:rgba(255,255,255,0.9);">3. Hasil Akhir (Final CF)</h4>
                    <p style="margin:0; font-size:0.9rem; line-height:1.5;">Formula: Avg CF Gejala &times; CF Pakar</p>
                    <p style="margin:0.5rem 0 0 0; font-size:1.1rem; font-weight:700;">
                        <?= number_format($tracing['avg_gejala_cf'] ?? 0, 2) ?> &times; <?= number_format($tracing['cf_pakar'] ?? 0, 2) ?> = <?= number_format(($tracing['avg_gejala_cf'] ?? 0) * ($tracing['cf_pakar'] ?? 0), 2) ?>
                    </p>
                    <p style="margin:0.5rem 0 0 0; font-size:0.85rem; color:rgba(255,255,255,0.7);">*Nilai final ini kemudian dikonversi menjadi persentase (<?= $confidence ?>%).</p>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:2rem; color:var(--color-gray-500);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem; opacity:0.5;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p style="margin:0;">Sistem tidak menemukan gejala signifikan untuk dianalisis (CF &lt; 0.2).</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem; border-top:1px solid var(--color-gray-200); background:white; display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeTracingModal()" style="background:var(--color-gray-100); color:var(--color-gray-600); border:none; padding:0.6rem 1.2rem; border-radius:8px; font-weight:600; cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openTracingModal() {
        const modal = document.getElementById('modalTracing');
        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeTracingModal() {
        const modal = document.getElementById('modalTracing');
        if (modal) {
            modal.classList.remove('active');
        }
    }

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
