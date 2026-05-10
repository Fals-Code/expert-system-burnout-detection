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
    <title>Dashboard Admin – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main class="page-content">
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
            </div>
        </main>
    </div>
</body>
</html>
