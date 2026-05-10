<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'pengaturan';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Mock Settings
$settings = [
    'app_name' => 'BurnoutXpert',
    'maintenance_mode' => '0',
    'alert_threshold' => '0.85',
    'max_daily_test' => '3'
];
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
        $page_title = "Pengaturan";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <?php include '../includes/toast.php'; ?>
        <div class="settings-card">
            <form onsubmit="event.preventDefault(); showToast('Pengaturan berhasil disimpan!', 'success');">
                
                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Nama Aplikasi</label>
                        <p class="setting-desc">Nama sistem yang muncul di seluruh antarmuka.</p>
                    </div>
                    <input type="text" class="form-input" value="<?= $settings['app_name'] ?>">
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Maintenance Mode</label>
                        <p class="setting-desc">Nonaktifkan akses sementara untuk perbaikan sistem.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" <?= $settings['maintenance_mode'] == '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Alert Threshold (Certainty Factor)</label>
                        <p class="setting-desc">Ambang batas nilai keyakinan (0.0 - 1.0) untuk memicu notifikasi kritis ke HRD.</p>
                    </div>
                    <input type="number" step="0.01" class="form-input" value="<?= $settings['alert_threshold'] ?>">
                </div>

                <div class="setting-group">
                    <div class="setting-info">
                        <label class="setting-label">Batas Deteksi Harian</label>
                        <p class="setting-desc">Jumlah maksimal deteksi yang boleh dilakukan karyawan per hari.</p>
                    </div>
                    <input type="number" class="form-input" value="<?= $settings['max_daily_test'] ?>">
                </div>

                <button type="submit" class="btn-save">Simpan Pengaturan</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
