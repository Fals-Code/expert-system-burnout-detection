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
    <title>Log Aktivitas – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Log Aktivitas";
        ob_start(); ?>
        <button onclick="location.reload()" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
            <span>Refresh</span>
        </button>
        <?php 
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php'; 
    ?>

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
