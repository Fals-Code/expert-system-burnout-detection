<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user        = $_SESSION['user'];
$nama        = $user['nama'];
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'dashboard';

// Ambil statistik sistem
$users          = $_SESSION['bx_store']['users'];
$total_users    = count($users);
$total_karyawan = count(array_filter($users, fn($u) => $u['role'] === 'karyawan'));

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}
$total_gejala   = count($_SESSION['mock_kb']['gejala']);
$total_aturan   = count($_SESSION['mock_kb']['aturan']);

$logs = array_slice($_SESSION['bx_store']['logs'], 0, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <h1 class="page-title">Ringkasan Sistem</h1>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-top: 1rem;">
            <div class="content-card stat-card">
                <div class="stat-info">
                    <div class="stat-value"><?= $total_users ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
            <div class="content-card stat-card">
                <div class="stat-info">
                    <div class="stat-value"><?= $total_gejala ?></div>
                    <div class="stat-label">Total Gejala (Basis Pengetahuan)</div>
                </div>
            </div>
            <div class="content-card stat-card">
                <div class="stat-info">
                    <div class="stat-value"><?= $total_aturan ?></div>
                    <div class="stat-label">Total Aturan (Sistem Pakar)</div>
                </div>
            </div>
            <div class="content-card stat-card">
                <div class="stat-info">
                    <div class="stat-value"><?= count($_SESSION['bx_store']['logs']) ?></div>
                    <div class="stat-label">Total Log Aktivitas</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
            <!-- Log Terbaru -->
            <div class="content-card">
                <div class="card-header" style="padding: 0; margin-bottom: 1rem;">
                    <h2 class="card-title">📋 Log Aktivitas Terbaru</h2>
                    <a href="log_aktivitas.php" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700;">Lihat Semua</a>
                </div>
                <div class="log-mini-list">
                    <?php if (empty($logs)): ?>
                    <p style="text-align: center; color: var(--color-gray-400); padding: 2rem;">Belum ada aktivitas.</p>
                    <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--color-gray-100); font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="font-weight: 700; color: var(--color-primary);"><?= htmlspecialchars($l['user']) ?></span>
                            <span style="color: var(--color-gray-400); font-size: 0.75rem;"><?= $l['time'] ?></span>
                        </div>
                        <div style="color: var(--color-gray-600);"><?= htmlspecialchars($l['desc']) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Info -->
            <div class="content-card">
                <h2 class="card-title">⚙️ Status Lingkungan</h2>
                <div style="font-size: 0.9rem; line-height: 2;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                        <span style="color: var(--color-gray-500);">Versi PHP:</span>
                        <span style="font-weight: 600;"><?= PHP_VERSION ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                        <span style="color: var(--color-gray-500);">Mode Penyimpanan:</span>
                        <span style="font-weight: 600; color: var(--color-accent);">Session Persistent</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                        <span style="color: var(--color-gray-500);">Status Cache KB:</span>
                        <span style="font-weight: 600; color: #10B981;">Aktif</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-gray-500);">Zonawaktu:</span>
                        <span style="font-weight: 600;"><?= date_default_timezone_get() ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
