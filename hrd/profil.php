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
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        .page-content { padding: 2rem; flex: 1; }

        .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 2rem; align-items: start; }
        .profile-card { background: #fff; border-radius: 20px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); }
        .avatar-large { width: 120px; height: 120px; border-radius: 50%; background: var(--color-primary); color: #fff; font-size: 3rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 4px solid #fff; box-shadow: var(--shadow-md); }
        
        .form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); }
        .section-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-50); padding-bottom: 0.5rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label { font-size: 0.85rem; font-weight: 700; color: var(--color-gray-700); }
        .form-input { padding: 0.8rem 1rem; border-radius: 10px; border: 1.5px solid var(--color-gray-200); font-family: inherit; font-size: 0.9rem; transition: 0.2s; }
        .form-input:focus { border-color: var(--color-primary); outline: none; }
        
        .btn-save { background: var(--color-accent); color: #fff; padding: 0.8rem 2rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: 0.2s; }
        .btn-save:hover { background: var(--color-accent-dark); transform: translateY(-2px); }

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0 !important; }
            .profile-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .page-content { padding: 1rem; }
            .profile-card { padding: 2rem 1rem; }
            .form-card { padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; gap: 1rem; }
        }
    </style>
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
