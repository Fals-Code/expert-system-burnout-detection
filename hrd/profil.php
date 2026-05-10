<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
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
    <title>Profil HRD – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div class="profile-grid">
            <aside class="profile-card">
                <div class="avatar-large"><?= $initials ?></div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);"><?= $nama ?></h2>
                <p style="font-size: 0.875rem; color: var(--color-gray-500); font-weight: 600; margin-bottom: 2rem;">HR Manager - Human Resources</p>
                
                <div style="text-align: left; background: var(--color-gray-50); padding: 1rem; border-radius: 12px; font-size: 0.8rem; color: var(--color-gray-600);">
                    <p>🛡️ Akses Level: HRD Administrator</p>
                    <p>📅 Bergabung: 15 Okt 2022</p>
                </div>
            </aside>

            <section class="form-card">
                <form onsubmit="event.preventDefault(); showToastLocal();">
                    <h3 class="section-title">Informasi Pribadi</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($nama) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Kerja</label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-input" value="0812-xxxx-xxxx">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Departemen</label>
                            <input type="text" class="form-input" value="Human Resources" disabled>
                        </div>
                    </div>

                    <h3 class="section-title">Keamanan</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-input" placeholder="Isi untuk mengganti">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-input" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </form>
            </section>
        </div>
    </main>
</div>

    <?php include '../includes/toast.php'; ?>
    <script>
        function showToastLocal() {
            showToast('Profil berhasil diperbarui!', 'success');
        }
    </script>


</body>
</html>
