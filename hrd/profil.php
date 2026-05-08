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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil HRD – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
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

        #toast { position: fixed; top: 20px; right: 20px; background: #28A745; color: #fff; padding: 1rem 2rem; border-radius: 12px; font-weight: 700; transform: translateX(200%); transition: 0.4s; z-index: 1000; }
        #toast.show { transform: translateX(0); }
    </style>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Profil Pengguna</div>
        <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
    </header>

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
                <form onsubmit="event.preventDefault(); showToast();">
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

<div id="toast">Profil berhasil diperbarui! ✅</div>

<script>
    function showToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
</script>

</body>
</html>
