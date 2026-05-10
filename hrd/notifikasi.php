<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'notifikasi';

// Mock Notifikasi
$notifications = [
    [
        'id' => 1,
        'type' => 'critical',
        'title' => 'Burnout Tinggi Terdeteksi',
        'message' => 'Andi Wijaya (IT) baru saja melakukan deteksi dengan hasil Burnout Tinggi.',
        'time' => '10 menit yang lalu',
        'unread' => true
    ],
    [
        'id' => 2,
        'type' => 'warning',
        'title' => 'Laporan Mingguan Siap',
        'message' => 'Laporan rekapitulasi burnout periode 1-7 Mei 2026 telah digenerate.',
        'time' => '2 jam yang lalu',
        'unread' => true
    ],
    [
        'id' => 3,
        'type' => 'info',
        'title' => 'Pembaruan Basis Pengetahuan',
        'message' => 'Admin telah memperbarui aturan Backward Chaining untuk akurasi lebih baik.',
        'time' => '1 hari yang lalu',
        'unread' => false
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Notifikasi – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Notifikasi";
        ob_start(); ?>
        <button class="btn-mark-all" onclick="alert('Semua ditandai dibaca (Mock)')" style="background:none; border:none; color:var(--color-primary); font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:underline;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Tandai semua dibaca</span>
        </button>
        <?php 
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="notif-container">
            <?php foreach ($notifications as $n): ?>
            <div class="notif-card <?= $n['unread'] ? 'unread' : '' ?>">
                <div class="notif-icon icon-<?= $n['type'] ?>">
                    <?php if ($n['type'] === 'critical'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php elseif ($n['type'] === 'warning'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <?php endif; ?>
                </div>
                <div class="notif-body">
                    <div class="notif-title"><?= $n['title'] ?></div>
                    <div class="notif-msg"><?= $n['message'] ?></div>
                    <div class="notif-time"><?= $n['time'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

</body>
</html>
