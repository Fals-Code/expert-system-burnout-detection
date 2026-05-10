<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
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
    <title>Profil Saya – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main class="page-content">
            <div class="profile-grid">
            
            <!-- Left Column: Profile Card -->
            <aside class="profile-card">
                <div class="avatar-container">
                    <div class="avatar-large"><?= $initials ?></div>
                    <button class="btn-change-photo">Ganti Foto</button>
                </div>
                <h1 class="profile-name"><?= $nama ?></h1>
                <p class="profile-title">Software Engineer - Engineering</p>
                <span class="status-badge">Burnout Tinggi</span>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <span class="stat-val">5x</span>
                        <span class="stat-lbl">Total Deteksi</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val">12 Apr</span>
                        <span class="stat-lbl">Terakhir</span>
                    </div>
                </div>
            </aside>

            <!-- Right Column: Form Edit -->
            <main class="form-card">
                <form id="profileForm" onsubmit="return handleSave(event)">
                    <h2 class="section-title">Informasi Pribadi</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($nama) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-input" value="0812-3456-7890">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Divisi</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($user['divisi']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-input" value="Software Engineer" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="text" class="form-input" value="1 Januari 2023" disabled>
                        </div>
                    </div>

                    <h2 class="section-title">Keamanan</h2>
                    <div class="form-grid" style="margin-bottom: 1.5rem;">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-input" placeholder="Masukkan password saat ini">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-input" placeholder="Min. 8 karakter">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-input" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-cancel">Batal</button>
                        <button type="submit" class="btn btn-save">Simpan Perubahan</button>
                    </div>
                </form>
            </main>

            </div>
        </main>
    </div>

    <?php include '../includes/toast.php'; ?>
    <script>
        function handleSave(e) {
            e.preventDefault();
            showToast('Profil berhasil diperbarui!', 'success');
            return false;
        }
    </script>


</body>
</html>
