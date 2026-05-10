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
$active_menu = 'log_aktivitas';
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Tandai semua dibaca (POST action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_logs') {
    $_SESSION['bx_store']['logs'] = [];
    append_log($nama, 'CLEAR_LOGS', 'Admin', 'Log aktivitas dibersihkan oleh admin.');
    header('Location: log_aktivitas.php?ok=Log+berhasil+dibersihkan.');
    exit();
}

$logs  = $_SESSION['bx_store']['logs'] ?? [];
$total = count($logs);
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
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:0.8rem; color:var(--color-gray-400);"><?= $total ?> entri</span>
            <button onclick="location.reload()" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-weight:700; display:flex; align-items:center; gap:0.5rem; font-size:0.85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                Refresh
            </button>
            <?php if ($total > 0): ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus semua log aktivitas?')">
                <input type="hidden" name="action" value="clear_logs">
                <button type="submit" style="background:none; border:none; color:#DC3545; cursor:pointer; font-weight:700; display:flex; align-items:center; gap:0.5rem; font-size:0.85rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Bersihkan Log
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php';
    ?>

    <main class="page-content">

        <?php if (isset($_GET['ok'])): ?>
        <div style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ <?= htmlspecialchars($_GET['ok']) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($logs)): ?>
        <div class="content-card" style="text-align:center; padding:4rem;">
            <div style="font-size:3.5rem; margin-bottom:1rem;">📋</div>
            <h2 style="font-weight:800; color:var(--color-primary);">Belum Ada Log Aktivitas</h2>
            <p style="color:var(--color-gray-500); margin-top:0.5rem;">Log akan muncul saat pengguna melakukan aksi di sistem.</p>
        </div>
        <?php else: ?>
        <div class="log-container">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 200px;">Aktor & Waktu</th>
                        <th style="width: 170px;">Aksi</th>
                        <th style="width: 130px;">Entitas</th>
                        <th>Deskripsi Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                    <?php
                        $pill_class = 'act-system';
                        $action_upper = strtoupper($l['action'] ?? '');
                        if (str_contains($action_upper, 'UPDATE')) $pill_class = 'act-update';
                        if (str_contains($action_upper, 'CREATE')) $pill_class = 'act-create';
                        if (str_contains($action_upper, 'DELETE') || str_contains($action_upper, 'CLEAR')) $pill_class = 'act-delete';
                        if (str_contains($action_upper, 'LOGIN'))  $pill_class = 'act-create';
                        if (str_contains($action_upper, 'DETEKSI')) $pill_class = 'act-update';
                        if (str_contains($action_upper, 'ALERT'))   $pill_class = 'act-delete';
                    ?>
                    <tr>
                        <td style="color:var(--color-gray-400); font-size:0.8rem;"><?= $l['id'] ?></td>
                        <td>
                            <div class="log-user"><?= htmlspecialchars($l['user']) ?></div>
                            <span class="log-time"><?= htmlspecialchars($l['time']) ?></span>
                        </td>
                        <td><span class="action-pill <?= $pill_class ?>"><?= htmlspecialchars($l['action']) ?></span></td>
                        <td><span class="log-entity"><?= htmlspecialchars($l['entity']) ?></span></td>
                        <td><div class="log-desc"><?= htmlspecialchars($l['desc']) ?></div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
