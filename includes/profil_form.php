<?php
/**
 * Unified Profile Form Component
 * Included by: admin/profil.php, hrd/profil.php, karyawan/profil.php
 */
if (!isset($user)) {
    die('Access denied');
}

$nama = $user['nama'];
$email = $user['email'];
$divisi = $user['divisi'] ?? '-';
$posisi = $user['posisi'] ?? '-';
$role = $user['role'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Handle Feedback
$feedback = $_SESSION['feedback'] ?? null;
unset($_SESSION['feedback']);
?>

<div class="profile-container">
    <?php if ($feedback): ?>
        <div class="alert alert--<?= $feedback['type'] ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: 12px; background: <?= $feedback['type'] === 'success' ? '#F0FFF4' : '#FFF5F5' ?>; border: 1px solid <?= $feedback['type'] === 'success' ? '#BBF7D0' : '#FECACA' ?>; color: <?= $feedback['type'] === 'success' ? '#065F46' : '#991B1B' ?>;">
            <?= $feedback['type'] === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($feedback['message']) ?>
        </div>
    <?php endif; ?>

    <div class="profile-header-card">
        <div class="profile-avatar-large" style="position: relative; cursor: pointer;" onclick="document.getElementById('photoInput').click()">
            <?php if (!empty($user['photo'])): ?>
                <img src="<?= $user['photo'] ?>" style="width: 100%; height: 100%; border-radius: 24px; object-fit: cover;">
            <?php else: ?>
                <?= htmlspecialchars($initials) ?>
            <?php endif; ?>
            <div style="position: absolute; bottom: -5px; right: -5px; background: #fff; color: var(--color-primary); border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                📷
            </div>
        </div>
        <form id="photoForm" method="POST" action="profil.php" enctype="multipart/form-data" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="update_photo">
            <input type="file" name="photo" id="photoInput" onchange="document.getElementById('photoForm').submit()" accept="image/*">
        </form>
        <div class="profile-header-info">
            <h1 class="profile-name"><?= htmlspecialchars($nama) ?></h1>
            <p class="profile-role-badge"><?= strtoupper($role) ?> &bull; <?= htmlspecialchars($divisi) ?></p>
        </div>
    </div>

    <div class="profile-grid">
        <!-- Personal Information -->
        <div class="content-card">
            <h2 class="card-title">Informasi Pribadi</h2>
            <form method="POST" action="profil.php" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($email) ?>" readonly style="background: var(--color-gray-50);">
                    <small style="color: var(--color-gray-400);">Email tidak dapat diubah untuk keamanan akun.</small>
                </div>
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Divisi</label>
                        <input type="text" class="form-input" value="<?= htmlspecialchars($divisi) ?>" readonly style="background: var(--color-gray-50);">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Posisi</label>
                        <input type="text" class="form-input" value="<?= htmlspecialchars($posisi) ?>" readonly style="background: var(--color-gray-50);">
                    </div>
                </div>
                <button type="submit" class="btn-cta" style="width: 100%; justify-content: center;">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Security -->
        <div class="content-card">
            <h2 class="card-title">Keamanan Akun</h2>
            <form method="POST" action="profil.php" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="update_password">
                <div class="form-group">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Min. 8 karakter" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="btn-cta" style="width: 100%; justify-content: center; background: var(--color-primary);">Ganti Password</button>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container { max-width: 1000px; margin: 0 auto; }
.profile-header-card { 
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    padding: 3rem; border-radius: 24px; display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; color: white;
    box-shadow: 0 10px 30px rgba(30, 58, 95, 0.15);
}
.profile-avatar-large {
    width: 100px; height: 100px; border-radius: 24px; background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 800; border: 2px solid rgba(255,255,255,0.3);
}
.profile-name { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
.profile-role-badge { font-size: 1rem; opacity: 0.8; font-weight: 600; }
.profile-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; }
.profile-form { display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1rem; }
@media (max-width: 768px) {
    .profile-grid { grid-template-columns: 1fr; }
    .profile-header-card { flex-direction: column; text-align: center; padding: 2rem; }
}
</style>
