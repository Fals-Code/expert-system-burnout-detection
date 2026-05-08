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
    <style>
        /* ── Layout ── */
        body {
            background: var(--color-gray-50);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── Main Wrapper ── */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }



        /* ── Page Content ── */
        .page-content {
            padding: 2rem;
            flex: 1;
        }

        /* ── Welcome Card ── */
        .welcome-card {
            background: linear-gradient(135deg, var(--color-primary) 0%, #2A5080 60%, #1a4070 100%);
            border-radius: 18px;
            padding: 2rem 2.5rem;
            display: flex; align-items: center; justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 1.75rem;
            position: relative; overflow: hidden;
            box-shadow: 0 8px 32px rgba(30,58,95,0.22);
            animation: fadeSlideUp 0.5s ease both;
        }

        .welcome-card__text { position: relative; z-index: 1; }

        .welcome-card__greeting {
            font-size: 0.8rem; font-weight: 600;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 0.4rem;
        }

        .welcome-card__name {
            font-size: 1.6rem; font-weight: 800;
            color: #fff; letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .welcome-card__name span { color: var(--color-accent-light); }

        .welcome-card__quote {
            font-size: 0.875rem; color: rgba(255,255,255,0.7);
            font-style: italic; max-width: 380px; line-height: 1.5;
        }

        /* ── Mood Check-in ── */
        .mood-container { background: #fff; border-radius: 18px; padding: 1.5rem 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; }
        .mood-title { font-size: 1rem; font-weight: 800; color: var(--color-primary); }
        .mood-options { display: flex; gap: 1rem; }
        .mood-btn { width: 48px; height: 48px; border-radius: 12px; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); font-size: 1.5rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .mood-btn:hover { transform: translateY(-3px); border-color: var(--color-accent); background: var(--color-accent-50); }
        .mood-btn.active { background: var(--color-accent); border-color: var(--color-accent); color: #fff; box-shadow: var(--shadow-accent); }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
            animation: fadeSlideUp 0.55s 0.08s ease both;
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-100);
            display: flex; align-items: center; gap: 1.25rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative; overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-card__icon {
            width: 54px; height: 54px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.5rem;
        }

        .stat-card__icon--warning { background: #FFF8E1; }
        .stat-card__icon--info    { background: var(--color-primary-50); }

        .stat-card__body { flex: 1; min-width: 0; }

        .stat-card__label {
            font-size: 0.75rem; font-weight: 600;
            color: var(--color-gray-400);
            text-transform: uppercase; letter-spacing: 0.06em;
            margin-bottom: 0.35rem;
        }

        .stat-card__value {
            font-size: 1.15rem; font-weight: 800;
            color: var(--color-gray-800);
            line-height: 1.2;
        }

        .stat-card__sub {
            font-size: 0.75rem; color: var(--color-gray-400);
            margin-top: 0.25rem; font-weight: 500;
        }

        /* Status badge */
        .status-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.85rem;
            border-radius: 999px;
            font-size: 0.8rem; font-weight: 700;
        }

        .status-badge--dynamic {
            background: <?= $bg_burnout ?>;
            color: <?= $color_burnout ?>;
            border: 1px solid <?= $color_burnout ?>40;
        }

        .status-badge__dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
            animation: pulseDot 1.8s ease-in-out infinite;
        }

        @keyframes pulseDot {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.7); }
        }

        /* ── CTA Section ── */
        .cta-section { animation: fadeSlideUp 0.6s 0.15s ease both; }

        .cta-card {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 2.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-100);
            display: flex; align-items: center; justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .cta-card__title {
            font-size: 1.2rem; font-weight: 800;
            color: var(--color-gray-800); margin-bottom: 0.4rem;
        }

        .cta-card__desc {
            font-size: 0.875rem; color: var(--color-gray-500);
            max-width: 400px; line-height: 1.55;
        }

        .btn-detect {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 0.9rem 2rem;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-dark) 100%);
            color: #fff;
            font-family: inherit;
            font-size: 1rem; font-weight: 700;
            border: none; border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(244,132,95,0.38);
            transition: transform 0.2s, box-shadow 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
            letter-spacing: 0.01em;
            position: relative; overflow: hidden;
        }

        .btn-detect:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(244,132,95,0.48);
            color: #fff;
        }

        .btn-detect__arrow { transition: transform 0.2s; }
        .btn-detect:hover .btn-detect__arrow { transform: translateX(4px); }

        /* ── Animations ── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 768px) {
            .page-content { padding: 1.25rem; }
            .welcome-card { padding: 1.5rem; flex-direction: column; text-align: center; }
            .welcome-card__name { font-size: 1.3rem; }
            .cta-card { flex-direction: column; align-items: flex-start; }
            .btn-detect { width: 100%; justify-content: center; }

        }
    </style>
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
                        <span class="status-badge status-badge--dynamic">
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
