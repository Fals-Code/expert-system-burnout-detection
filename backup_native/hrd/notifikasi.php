<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user        = $_SESSION['user'];
$nama        = $user['nama'];
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'notifikasi';

// POST: tandai semua dibaca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    require_once '../includes/security.php';
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }
    foreach ($_SESSION['bx_store']['hrd_alerts'] as &$a) {
        $a['read'] = true;
    }
    unset($a);
    header('Location: notifikasi.php?ok=1');
    exit();
}

$alerts   = $_SESSION['bx_store']['hrd_alerts'] ?? [];
$unread   = count(array_filter($alerts, fn($a) => !$a['read']));
$all_notifs = [];

// Konversi hrd_alerts ke format notifikasi
foreach (array_reverse($alerts) as $a) {
    $all_notifs[] = [
        'type'   => $a['type'] ?? 'info',
        'title'  => $a['type'] === 'critical' ? '🚨 Burnout Tinggi Terdeteksi' : '📋 Notifikasi Sistem',
        'message'=> $a['message'],
        'time'   => date('d M Y H:i', $a['timestamp'] ?? time()),
        'unread' => !($a['read'] ?? false),
    ];
}

// Tambah notifikasi sistem statis (selalu tampil sebagai info)
$all_notifs[] = [
    'type'   => 'info',
    'title'  => '📊 Laporan Siap Diunduh',
    'message'=> 'Laporan rekapitulasi burnout dapat dilihat di halaman Laporan.',
    'time'   => date('d M Y', strtotime('-1 day')),
    'unread' => false,
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
        <div style="display:flex; align-items:center; gap:1rem;">
            <?php if ($unread > 0): ?>
            <span style="background:#DC3545; color:#fff; border-radius:99px; padding:0.2rem 0.6rem; font-size:0.75rem; font-weight:700;">
                <?= $unread ?> belum dibaca
            </span>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" style="background:none; border:none; color:var(--color-primary); font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:underline; display:flex; align-items:center; gap:4px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Tandai semua dibaca
                </button>
            </form>
            <?php else: ?>
            <span style="font-size:0.8rem; color:var(--color-gray-400);">Semua notifikasi sudah dibaca</span>
            <?php endif; ?>
        </div>
        <?php
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php';
    ?>

    <main class="page-content">

        <?php if (isset($_GET['ok'])): ?>
        <div style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ Semua notifikasi berhasil ditandai dibaca.
        </div>
        <?php endif; ?>

        <?php if (empty($all_notifs)): ?>
        <div class="content-card" style="text-align:center; padding:4rem;">
            <div style="font-size:3rem; margin-bottom:1rem;">🔔</div>
            <h2 style="font-weight:800; color:var(--color-primary);">Tidak Ada Notifikasi</h2>
            <p style="color:var(--color-gray-500); margin-top:0.5rem;">Notifikasi akan muncul saat ada karyawan dengan burnout tinggi terdeteksi.</p>
        </div>
        <?php else: ?>
        <div class="notif-container">
            <?php foreach ($all_notifs as $n): ?>
            <div class="notif-card <?= $n['unread'] ? 'unread' : '' ?>">
                <div class="notif-icon icon-<?= $n['type'] ?>">
                    <?php if ($n['type'] === 'critical'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <?php endif; ?>
                </div>
                <div class="notif-body">
                    <div class="notif-title"><?= $n['title'] ?></div>
                    <div class="notif-msg"><?= $n['message'] ?></div>
                    <span class="notif-time"><?= $n['time'] ?></span>
                </div>
                <?php if ($n['unread']): ?>
                <div style="width:8px; height:8px; border-radius:50%; background:#DC3545; flex-shrink:0; margin-left:auto; align-self:flex-start; margin-top:0.5rem;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
