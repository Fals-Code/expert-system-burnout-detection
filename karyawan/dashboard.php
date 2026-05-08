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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan – BurnoutXpert</title>
    <meta name="description" content="Dashboard karyawan BurnoutXpert – pantau status burnout dan mulai deteksi sekarang.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
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

        /* ── Top Header ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
            box-shadow: var(--shadow-sm);
        }

        .topbar__left { display: flex; flex-direction: column; gap: 2px; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
        .topbar__breadcrumb { font-size: 0.75rem; color: var(--color-gray-400); font-weight: 500; }

        .topbar__right { display: flex; align-items: center; gap: 1rem; }

        .topbar__date {
            font-size: 0.8rem; color: var(--color-gray-500); font-weight: 500;
            background: var(--color-gray-50);
            border: 1px solid var(--color-gray-200);
            padding: 0.35rem 0.85rem;
            border-radius: 8px;
        }

        .topbar__avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-dark) 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; font-weight: 700; color: #fff;
            cursor: pointer;
            border: 2px solid var(--color-accent-50);
            transition: transform 0.2s;
        }

        .topbar__avatar:hover { transform: scale(1.08); }

        .topbar__name { font-size: 0.875rem; font-weight: 700; color: var(--color-gray-700); }

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

        .welcome-card::before {
            content: '';
            position: absolute; top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(244,132,95,0.18) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute; bottom: -40px; left: 30%;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
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

        .welcome-card__emoji {
            font-size: 4rem;
            position: relative; z-index: 1;
            animation: floatEmoji 3s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes floatEmoji {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

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

        .btn-detect::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-detect:hover::before { opacity: 1; }

        .btn-detect:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(244,132,95,0.48);
            color: #fff;
        }

        .btn-detect:active { transform: translateY(0); }

        .btn-detect__arrow { transition: transform 0.2s; }
        .btn-detect:hover .btn-detect__arrow { transform: translateX(4px); }

        /* ── Animations ── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Mobile Hamburger ── */
        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 40;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .page-content { padding: 1.25rem; }
            .welcome-card { padding: 1.5rem; }
            .welcome-card__name { font-size: 1.3rem; }
            .welcome-card__emoji { font-size: 2.5rem; }
            .cta-card { flex-direction: column; align-items: flex-start; }
            .btn-detect { width: 100%; justify-content: center; }
            .topbar { padding: 0.85rem 1.25rem; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<!-- ── MAIN WRAPPER ── -->
<div class="main-wrapper">

    <header class="topbar">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="topbar__left">
                <div class="topbar__title">Dashboard</div>
                <div class="topbar__breadcrumb">BurnoutXpert › Karyawan › Dashboard</div>
            </div>
        </div>
        <div class="topbar__right">
            <div class="topbar__date" id="current-date"><?= date('l, d F Y') ?></div>
            <div style="display:flex;align-items:center;gap:0.6rem;">
                <div class="topbar__name"><?= htmlspecialchars($nama) ?></div>
                <div class="topbar__avatar" title="<?= htmlspecialchars($nama) ?>"><?= htmlspecialchars($initials) ?></div>
            </div>
        </div>
    </header>

    <main class="page-content">

        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-card__text">
                <div class="welcome-card__greeting">Selamat datang kembali 👋</div>
                <div class="welcome-card__name">Halo, <span><?= htmlspecialchars($nama) ?></span>!</div>
                <div class="welcome-card__quote">
                    "Kesehatan mental adalah fondasi produktivitas. Setiap langkah kecil menuju keseimbangan adalah pencapaian besar."
                </div>
            </div>
            <div class="welcome-card__emoji">🧠</div>
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
