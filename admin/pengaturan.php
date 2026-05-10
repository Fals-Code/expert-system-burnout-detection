<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();
require_once '../includes/security.php';

$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'pengaturan';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }

    $_SESSION['bx_store']['settings']['app_name']          = trim($_POST['app_name'] ?? 'BurnoutXpert');
    $_SESSION['bx_store']['settings']['maintenance_mode']  = isset($_POST['maintenance_mode']);
    $_SESSION['bx_store']['settings']['threshold_high']    = (float)($_POST['threshold_high'] ?? 0.8);
    $_SESSION['bx_store']['settings']['max_daily_test']    = (int)($_POST['max_daily_test'] ?? 3);

    append_log($nama, 'UPDATE_SETTINGS', 'SYSTEM', "Memperbarui konfigurasi sistem.");
    $success = "Pengaturan sistem berhasil disimpan.";
}

$settings = $_SESSION['bx_store']['settings'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan Sistem – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Pengaturan Sistem";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <?php include '../includes/toast.php'; ?>
        
        <?php if ($success): ?>
            <div style="margin-bottom:1.5rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="settings-card">
            <form method="POST" action="pengaturan.php">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Nama Aplikasi</label>
                        <p class="setting-desc">Nama sistem yang muncul di seluruh antarmuka.</p>
                    </div>
                    <input type="text" name="app_name" class="form-input" value="<?= htmlspecialchars($settings['app_name']) ?>">
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Maintenance Mode</label>
                        <p class="setting-desc">Nonaktifkan akses sementara untuk perbaikan sistem.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Alert Threshold (Certainty Factor)</label>
                        <p class="setting-desc">Ambang batas nilai keyakinan (0.0 - 1.0) untuk memicu notifikasi kritis ke HRD.</p>
                    </div>
                    <input type="number" name="threshold_high" step="0.01" min="0" max="1" class="form-input" value="<?= $settings['threshold_high'] ?>">
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Batas Deteksi Harian</label>
                        <p class="setting-desc">Jumlah maksimal deteksi yang boleh dilakukan karyawan per hari.</p>
                    </div>
                    <input type="number" name="max_daily_test" class="form-input" value="<?= $settings['max_daily_test'] ?? 3 ?>">
                </div>

                <button type="submit" class="btn-save">Simpan Pengaturan</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
