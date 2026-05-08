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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 2rem; flex: 1; max-width: 900px; }

        .settings-card { background: #fff; border-radius: 20px; padding: 2.5rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
        .setting-group { margin-bottom: 2rem; border-bottom: 1px solid var(--color-gray-50); padding-bottom: 1.5rem; }
        .setting-group:last-child { border-bottom: none; }
        
        .setting-info { margin-bottom: 1rem; }
        .setting-label { display: block; font-size: 1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.25rem; }
        .setting-desc { font-size: 0.85rem; color: var(--color-gray-500); }

        .form-input { width: 100%; max-width: 400px; padding: 0.8rem 1rem; border-radius: 10px; border: 1.5px solid var(--color-gray-200); font-family: inherit; font-size: 0.9rem; margin-top: 0.5rem; }
        
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--color-accent); }
        input:checked + .slider:before { transform: translateX(24px); }

        .btn-save { background: var(--color-accent); color: #fff; padding: 0.8rem 2.5rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: 0.2s; box-shadow: var(--shadow-accent); }
        .btn-save:hover { transform: translateY(-2px); background: var(--color-accent-dark); }
    </style>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Pengaturan Global</div>
    </header>

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
