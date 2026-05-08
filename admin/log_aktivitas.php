<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'log_aktivitas';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Mock Data Audit Logs
$logs = [
    ['id' => 125, 'user' => 'Admin Budi', 'action' => 'UPDATE_GEJALA', 'entity' => 'G005', 'desc' => 'Mengubah bobot gejala "Merasa Hampa" dari 0.8 ke 1.0', 'time' => 'Baru saja'],
    ['id' => 124, 'user' => 'Admin Budi', 'action' => 'CREATE_ATURAN', 'entity' => 'R007', 'desc' => 'Menambahkan aturan baru untuk deteksi burnout berat', 'time' => '10 menit yang lalu'],
    ['id' => 123, 'user' => 'Sistem', 'action' => 'DB_BACKUP', 'entity' => 'Database', 'desc' => 'Auto-backup database mingguan berhasil dilakukan', 'time' => '2 jam yang lalu'],
    ['id' => 122, 'user' => 'Admin Budi', 'action' => 'DELETE_USER', 'entity' => 'Karyawan-09', 'desc' => 'Menghapus akun karyawan yang sudah tidak aktif', 'time' => '5 jam yang lalu'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 2rem; flex: 1; }

        .log-container { background: #fff; border-radius: 20px; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); overflow: hidden; }
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table th { background: var(--color-gray-50); text-align: left; padding: 1.25rem 1.5rem; font-size: 0.8rem; font-weight: 700; color: var(--color-gray-500); text-transform: uppercase; border-bottom: 1px solid var(--color-gray-200); }
        .log-table td { padding: 1.25rem 1.5rem; font-size: 0.9rem; border-bottom: 1px solid var(--color-gray-50); vertical-align: top; }
        
        .action-pill { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 800; font-size: 0.7rem; color: #fff; text-transform: uppercase; }
        .act-update { background: #0D6EFD; }
        .act-create { background: #198754; }
        .act-delete { background: #DC3545; }
        .act-system { background: #6C757D; }

        .log-user { font-weight: 700; color: var(--color-primary); }
        .log-time { color: var(--color-gray-400); font-size: 0.8rem; display: block; margin-top: 0.25rem; }
        .log-desc { color: var(--color-gray-700); line-height: 1.5; max-width: 500px; }
        .log-entity { font-family: 'Courier New', monospace; font-weight: 700; background: var(--color-gray-50); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Log Aktivitas Sistem</div>
        <button onclick="location.reload()" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
            Refresh
        </button>
    </header>

    <main class="page-content">
        <div class="log-container">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width: 200px;">Aktor & Waktu</th>
                        <th style="width: 150px;">Aksi</th>
                        <th style="width: 150px;">Entitas</th>
                        <th>Deskripsi Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                    <?php 
                        $pill_class = 'act-system';
                        if (strpos($l['action'], 'UPDATE') !== false) $pill_class = 'act-update';
                        if (strpos($l['action'], 'CREATE') !== false) $pill_class = 'act-create';
                        if (strpos($l['action'], 'DELETE') !== false) $pill_class = 'act-delete';
                    ?>
                    <tr>
                        <td>
                            <div class="log-user"><?= $l['user'] ?></div>
                            <span class="log-time"><?= $l['time'] ?></span>
                        </td>
                        <td><span class="action-pill <?= $pill_class ?>"><?= $l['action'] ?></span></td>
                        <td><span class="log-entity"><?= $l['entity'] ?></span></td>
                        <td><div class="log-desc"><?= $l['desc'] ?></div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
