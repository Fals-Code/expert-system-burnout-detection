<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
require_once '../config/data_store.php';
bx_init_store();

$user = $_SESSION['user'];
$user_id = $user['id'];
$nama = $user['nama'];
$active_menu = 'notifikasi';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// POST: tandai semua dibaca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    mark_notifications_read($user_id);
    header('Location: notifikasi.php?ok=1');
    exit();
}

// Ambil notifikasi dari DB
$notifications = get_user_notifications($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Notifikasi – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main class="page-content">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">Pusat Notifikasi</h1>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn-mark-read" style="background: var(--color-primary)15; color: var(--color-primary); border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Tandai Semua Sudah Dibaca</button>
                </form>
            </div>

            <!-- Notif List -->
            <div class="notif-list" id="notifList">
                <?php if (empty($notifications)): ?>
                    <div style="text-align: center; padding: 4rem 2rem; color: var(--color-gray-400);">
                        <p>Belum ada notifikasi untuk Anda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <div class="notif-card <?= !$n['is_read'] ? 'unread' : '' ?>" style="display: flex; gap: 1rem; padding: 1.25rem; background: #fff; border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--color-gray-100); transition: all 0.3s ease;">
                        <div class="notif-card__icon" style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; background: <?= $n['color'] ?: '#3B82F6' ?>15; color: <?= $n['color'] ?: '#3B82F6' ?>">
                            <?= $n['icon'] ?: '🔔' ?>
                        </div>
                        <div class="notif-card__body" style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-gray-800);"><?= htmlspecialchars($n['title']) ?></h3>
                                <span style="font-size: 0.75rem; color: var(--color-gray-400);"><?= date('d M, H:i', strtotime($n['created_at'])) ?></span>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--color-gray-600); line-height: 1.5;"><?= htmlspecialchars($n['message']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        .notif-card.unread {
            border-left: 4px solid var(--color-primary) !important;
            background: var(--color-primary)05 !important;
        }
        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</body>
</html>
