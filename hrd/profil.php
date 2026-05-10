<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();
require_once '../includes/security.php';

$user     = $_SESSION['user'];
$user_id  = $user['id'];
$nama     = $user['nama'];
$role     = $user['role'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'profil';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $new_nama   = trim($_POST['nama']   ?? '');
        $new_divisi = trim($_POST['divisi'] ?? '');
        $new_posisi = trim($_POST['posisi'] ?? '');

        if (empty($new_nama)) {
            $error = "Nama lengkap tidak boleh kosong.";
        } else {
            // Update di store
            foreach ($_SESSION['bx_store']['users'] as &$u) {
                if ($u['id'] === $user_id) {
                    $u['nama']   = $new_nama;
                    $u['divisi'] = $new_divisi;
                    $u['posisi'] = $new_posisi;
                    $_SESSION['user'] = $u; // Update session
                    break;
                }
            }
            unset($u);
            append_log($new_nama, 'UPDATE_PROFILE', $user_id, "Memperbarui data profil mandiri.");
            $success = "Profil Anda berhasil diperbarui.";
            $nama = $new_nama; // Refresh variable for current page
        }

    } elseif ($action === 'update_password') {
        $old_pw = $_POST['old_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $cfm_pw = $_POST['cfm_password'] ?? '';

        if ($old_pw !== $user['password']) {
            $error = "Password lama salah.";
        } elseif ($new_pw !== $cfm_pw) {
            $error = "Konfirmasi password baru tidak cocok.";
        } elseif (strlen($new_pw) < 6) {
            $error = "Password baru minimal 6 karakter.";
        } else {
            // Update password
            update_user_password($user['email'], $new_pw);
            $_SESSION['user']['password'] = $new_pw; // Update session
            append_log($nama, 'UPDATE_PASSWORD', $user_id, "Mengubah password akun.");
            $success = "Password Anda berhasil diubah.";
        }
    }
}

// Tentukan sidebar berdasarkan role
$sidebar_path = "../includes/sidebar_" . $role . ".php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php if (file_exists($sidebar_path)) include $sidebar_path; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <h1 class="page-title">Pengaturan Profil</h1>

        <?php if ($success): ?>
        <div style="margin-bottom:1.5rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ <?= htmlspecialchars($success) ?>
        </div>
        <?php elseif ($error): ?>
        <div style="margin-bottom:1.5rem; padding:0.75rem 1.25rem; border-radius:10px; background:#FFF5F5; border:1px solid #FECACA; color:#991B1B;">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
            <!-- Avatar Card -->
            <aside>
                <div class="content-card" style="text-align: center; padding: 2.5rem 1.5rem;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.5rem;">
                        <?= $initials ?>
                    </div>
                    <h2 style="font-weight: 800; color: var(--color-primary);"><?= htmlspecialchars($nama) ?></h2>
                    <p style="color: var(--color-gray-500); margin-top: 0.25rem;"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge-pill" style="margin-top: 1rem; background: var(--color-primary-50); color: var(--color-primary); font-weight: 700;">
                        Role: <?= ucfirst($role) ?>
                    </span>
                    <div style="margin-top: 2rem; border-top: 1px solid var(--color-gray-100); padding-top: 1.5rem; text-align: left; font-size: 0.85rem;">
                        <div style="margin-bottom: 0.75rem; display: flex; justify-content: space-between;">
                            <span style="color: var(--color-gray-400);">ID User:</span>
                            <span style="font-family: monospace; font-weight: 600;"><?= $user_id ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--color-gray-400);">Status Akun:</span>
                            <span style="color: #10B981; font-weight: 600;">Aktif</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Edit Forms -->
            <section style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Data Diri -->
                <div class="content-card">
                    <h2 class="card-title">Informasi Pribadi</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($nama) ?>" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Divisi</label>
                                <input type="text" name="divisi" class="form-input" value="<?= htmlspecialchars($user['divisi'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Posisi</label>
                                <input type="text" name="posisi" class="form-input" value="<?= htmlspecialchars($user['posisi'] ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn-cta" style="width: auto; padding: 0.75rem 2rem;">Simpan Perubahan</button>
                    </form>
                </div>

                <!-- Ganti Password -->
                <div class="content-card">
                    <h2 class="card-title">Keamanan Akun</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="update_password">
                        <div class="form-group">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="old_password" class="form-input" placeholder="Masukkan password saat ini" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-input" placeholder="Min. 6 karakter" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="cfm_password" class="form-input" placeholder="Ulangi password baru" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-cta" style="background: var(--color-gray-800); width: auto; padding: 0.75rem 2rem;">Ganti Password</button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</div>

</body>
</html>
