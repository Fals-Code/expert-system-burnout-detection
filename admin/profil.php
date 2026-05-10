<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'profil';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Administrator – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <?php include '../includes/toast.php'; ?>
        <div class="profile-grid">
            <aside class="profile-card">
                <div class="avatar-large"><?= $initials ?></div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);"><?= $nama ?></h2>
                <p style="font-size: 0.875rem; color: var(--color-gray-500); font-weight: 600; margin-bottom: 2rem;">Super Administrator</p>
                
                <div style="text-align: left; background: var(--color-gray-50); padding: 1rem; border-radius: 12px; font-size: 0.8rem; color: var(--color-gray-600);">
                    <p>🛡️ Akses Level: Full System Access</p>
                    <p>📅 Terakhir Login: <?= date('d/m/Y H:i') ?></p>
                </div>
            </aside>

            <section class="form-card">
                <form onsubmit="event.preventDefault(); showToast('Profil berhasil disimpan!', 'success');">
                    <h3 class="section-title">Informasi Akun</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($nama) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Admin</label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                    </div>

                    <h3 class="section-title">Ganti Password</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-input" placeholder="Min. 8 karakter">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-input" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <button type="submit" class="btn-save">Update Profil Admin</button>
                </form>
            </section>
        </div>
    </main>
</div>
</body>
</html>
