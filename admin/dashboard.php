<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
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
    <style>
        body { background: var(--color-gray-50); min-height: 100vh; }
        .navbar { background: var(--color-primary); color: white; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-md); }
        .navbar-brand { font-size: 1.25rem; font-weight: 800; }
        .navbar-brand span { color: var(--color-accent); }
        .navbar-user { display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; }
        .role-chip { background: rgba(244,132,95,0.3); color: var(--color-accent-light); padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .logout-btn { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 6px 14px; border-radius: 8px; font-family: inherit; font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); }
        .main-content { max-width: 1000px; margin: 3rem auto; padding: 0 1.5rem; }
        .welcome-card { background: white; border-radius: 16px; padding: 2.5rem; box-shadow: var(--shadow-md); text-align: center; border-top: 4px solid var(--color-accent); }
        .welcome-card h1 { font-size: 1.75rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem; }
        .welcome-card p { color: var(--color-gray-500); font-size: 0.95rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .stat-card { background: var(--color-primary-50); border-radius: 12px; padding: 1.25rem; text-align: center; }
        .stat-card__number { font-size: 2rem; font-weight: 800; color: var(--color-primary); }
        .stat-card__label { font-size: 0.8rem; color: var(--color-gray-500); font-weight: 600; margin-top: 0.25rem; }
        .coming-soon { margin-top: 2rem; padding: 1.25rem; background: var(--color-accent-50); border-radius: 10px; border-left: 4px solid var(--color-accent); color: var(--color-accent-dark); font-size: 0.875rem; font-weight: 500; text-align: left; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Burnout<span>Xpert</span></div>
        <div class="navbar-user">
            <span><?= htmlspecialchars($user['nama']) ?></span>
            <span class="role-chip">Admin</span>
            <a href="../logout.php" class="logout-btn">Keluar</a>
        </div>
    </nav>
    <main class="main-content">
        <div class="welcome-card">
            <h1>⚙️ Dashboard Admin</h1>
            <p>Selamat datang, <strong><?= htmlspecialchars($user['nama']) ?></strong>. Anda memiliki akses penuh ke sistem.</p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__number">3</div>
                    <div class="stat-card__label">Total Pengguna</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__number">3</div>
                    <div class="stat-card__label">Role Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__number">0</div>
                    <div class="stat-card__label">Asesmen Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__number">0</div>
                    <div class="stat-card__label">Kasus Burnout</div>
                </div>
            </div>
            <div class="coming-soon">
                📋 <strong>Dashboard Admin</strong> sedang dalam pengembangan.<br>
                Fitur yang akan tersedia: Manajemen Pengguna, Basis Pengetahuan, Aturan Pakar, Log Sistem, Konfigurasi Aplikasi.
            </div>
        </div>
    </main>
</body>
</html>
