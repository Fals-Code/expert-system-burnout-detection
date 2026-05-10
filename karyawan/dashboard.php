<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user          = $_SESSION['user'];
$nama          = $user['nama'];

// Ambil data deteksi terakhir dari session jika ada
$hasil_ada = isset($_SESSION['hasil_deteksi']);
$status_burnout = $hasil_ada ? $_SESSION['hasil_deteksi']['label'] : 'Belum Ada Data';
$tanggal_deteksi = $hasil_ada ? $_SESSION['hasil_deteksi']['tanggal'] : '-';
$color_burnout = $hasil_ada ? $_SESSION['hasil_deteksi']['color'] : '#98AAC0';
$bg_burnout = $hasil_ada ? $_SESSION['hasil_deteksi']['bg_light'] : '#F1F4F7';

$active_menu   = 'dashboard';
// Inisial avatar dari nama
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Karyawan – BurnoutXpert</title>
    <meta name="description" content="Dashboard karyawan BurnoutXpert – pantau status burnout dan mulai deteksi sekarang.">
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<!-- ── MAIN WRAPPER ── -->
<div class="main-wrapper">

    <?php 
        $page_title = "Overview Dashboard";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <?php include '../includes/toast.php'; ?>

        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-card__text">
                <div class="welcome-card__greeting">Selamat datang kembali 👋</div>
                <div class="welcome-card__name">Halo, <span><?= htmlspecialchars($nama) ?></span>!</div>
                <div class="welcome-card__quote">
                    "Kesehatan mental adalah fondasi produktivitas. Setiap langkah kecil menuju keseimbangan adalah pencapaian besar."
                </div>
            </div>
            <div style="font-size: 4rem; position: relative; z-index: 1; animation: floatEmoji 3s ease-in-out infinite;">🧠</div>
        </div>

        <!-- Mood Check-in -->
        <div class="mood-container">
            <div class="mood-text">
                <h3 class="mood-title">Bagaimana perasaan Anda hari ini?</h3>
                <p style="font-size: 0.8rem; color: var(--color-gray-400); margin-top: 0.2rem;">Mood Anda membantu kami memantau kesejahteraan Anda.</p>
            </div>
            <div class="mood-options">
                <button class="mood-btn" onclick="setMood('happy', this)" title="Senang">😊</button>
                <button class="mood-btn" onclick="setMood('neutral', this)" title="Biasa saja">😐</button>
                <button class="mood-btn" onclick="setMood('tired', this)" title="Lelah">😫</button>
                <button class="mood-btn" onclick="setMood('stressed', this)" title="Stres">🤯</button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-card__icon" style="background: <?= $bg_burnout ?>;">⚠️</div>
                <div class="stat-card__body">
                    <div class="stat-card__label">Status Deteksi Terakhir</div>
                    <div style="margin-top:0.1rem;">
                        <span class="status-badge" style="background: <?= $bg_burnout ?>; color: <?= $color_burnout ?>; border: 1px solid <?= $color_burnout ?>40;">
                            <span class="status-badge__dot"></span>
                            <?= htmlspecialchars($status_burnout) ?>
                        </span>
                    </div>
                    <div class="stat-card__sub"><?= $hasil_ada ? 'Berdasarkan analisis terbaru' : 'Belum ada riwayat deteksi' ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--info">📅</div>
                <div class="stat-card__body">
                    <div class="stat-card__label">Tanggal Deteksi Terakhir</div>
                    <div class="stat-card__value"><?= htmlspecialchars($tanggal_deteksi) ?></div>
                    <div class="stat-card__sub">Deteksi rutin disarankan setiap bulan</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card__icon" style="background: var(--color-primary-50); color: var(--color-primary);">📊</div>
                <div class="stat-card__body">
                    <div class="stat-card__label">Total Deteksi</div>
                    <div class="stat-card__value"><?= $hasil_ada ? '1' : '0' ?></div>
                    <div class="stat-card__sub">Asesmen yang telah diselesaikan</div>
                </div>
            </div>

        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <div class="cta-card">
                <div class="cta-card__text">
                    <div class="cta-card__title">Siap untuk deteksi burnout terbaru?</div>
                    <div class="cta-card__desc">
                        Ikuti kuesioner singkat berbasis sistem pakar untuk mengetahui tingkat burnout Anda saat ini dan dapatkan rekomendasi penanganannya.
                    </div>
                </div>
                <a href="deteksi.php" class="btn-detect" id="btn-mulai-deteksi">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Mulai Deteksi Sekarang
                    <svg class="btn-detect__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </div>
        </div>

    </main>
</div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
    }

    function setMood(mood, btn) {
        document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const messages = {
            'happy': 'Senang mendengarnya! Pertahankan energi positif Anda. ✨',
            'neutral': 'Terima kasih telah berbagi perasaan Anda hari ini. 🧘‍♂️',
            'tired': 'Jangan lupa istirahat sejenak. Kesehatan Anda prioritas. ☕',
            'stressed': 'Tetap tenang. Kami di sini untuk membantu jika Anda butuh. ❤️'
        };
        showToast(messages[mood], 'info');
    }

    function updateDate() {
        const el = document.getElementById('current-date');
        if (!el) return;
        const now = new Date();
        el.textContent = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }
    updateDate();
</script>


</body>
</html>
