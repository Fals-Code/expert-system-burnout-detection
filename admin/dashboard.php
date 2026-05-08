<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }

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

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        /* ── Page Content ── */
        .page-content { padding: 2rem; flex: 1; }

        /* ── Summary Cards ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-100);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
        
        .stat-icon {
            width: 54px; height: 54px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-info { display: flex; flex-direction: column; }
        .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--color-primary); line-height: 1.2; }
        .stat-label { font-size: 0.75rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; letter-spacing: 0.05em; }

        /* Colors for icons */
        .bg-blue { background: #E3F2FD; color: #1976D2; }
        .bg-purple { background: #F3E5F5; color: #7B1FA2; }
        .bg-green { background: #E8F5E9; color: #388E3C; }
        .bg-orange { background: #FFF3E0; color: #F57C00; }

        /* ── Quick Actions ── */
        .action-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.25rem; }
        .actions-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .action-card {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            border: 1px solid var(--color-gray-100);
            box-shadow: var(--shadow-sm);
            transition: 0.3s;
        }
        .action-card:hover { border-color: var(--color-accent); transform: translateY(-5px); box-shadow: var(--shadow-md); }
        .action-card__icon { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
        .action-card__name { display: block; font-size: 1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem; }
        .action-card__desc { display: block; font-size: 0.8rem; color: var(--color-gray-500); line-height: 1.5; }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .actions-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

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
                    <div class="topbar__title">Dashboard Admin</div>
                    <div class="topbar__breadcrumb">BurnoutXpert › Admin › Dashboard</div>
                </div>
            </div>
            <div style="font-size: 0.875rem; font-weight: 700; color: var(--color-gray-600);">
                <?= htmlspecialchars($user['nama']) ?> 🛡️
            </div>
                   <h2 class="action-title">Statistik Sistem</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">124</span>
                        <span class="stat-label">Total Pengguna</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-purple">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">12</span>
                        <span class="stat-label">Basis Gejala</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-green">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">458</span>
                        <span class="stat-label">Total Asesmen</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-orange">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">32</span>
                        <span class="stat-label">Kasus Kritis</span>
                    </div>
                </div>
            </div>

            <h2 class="action-title">Akses Cepat Pengelolaan</h2>
            <div class="actions-grid" style="grid-template-columns: repeat(2, 1fr);">
                <a href="kelola_pengguna.php" class="action-card">
                    <span class="action-card__icon">👥</span>
                    <span class="action-card__name">Kelola Pengguna</span>
                    <span class="action-card__desc">Tambah, edit, atau hapus akun Karyawan dan HRD.</span>
                </a>
                <a href="admin_knowledge.php" class="action-card">
                    <span class="action-card__icon">🧠</span>
                    <span class="action-card__name">Basis Pengetahuan</span>
                    <span class="action-card__desc">Atur gejala dan logika Backward Chaining sistem.</span>
                </a>
                <a href="laporan.php" class="action-card">
                    <span class="action-card__icon">📊</span>
                    <span class="action-card__name">Laporan Global</span>
                    <span class="action-card__desc">Pantau statistik distribusi burnout secara menyeluruh.</span>
                </a>
                <a href="profil.php" class="action-card">
                    <span class="action-card__icon">⚙️</span>
                    <span class="action-card__name">Pengaturan Profil</span>
                    <span class="action-card__desc">Update informasi akun administrator Anda.</span>
                </a>
            </div>i pengguna.</span>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
