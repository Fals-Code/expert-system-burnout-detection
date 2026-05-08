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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top Header ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .form-grid { grid-template-columns: 1fr; }
        }

        /* ── Profile Layout ── */
        .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 2rem; align-items: start; }

        /* ── Left Column: Profile Card ── */
        .profile-card { background: #fff; border-radius: 20px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: var(--shadow-md); border: 1px solid var(--color-gray-100); }
        
        .avatar-container { position: relative; width: 120px; height: 120px; margin: 0 auto 1.5rem; }
        .avatar-large { width: 100%; height: 100%; border-radius: 50%; background: var(--color-accent-50); color: var(--color-accent); font-size: 3rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn-change-photo { margin-top: 1rem; background: none; border: none; color: var(--color-primary); font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: underline; }

        .profile-name { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.25rem; }
        .profile-title { font-size: 0.875rem; color: var(--color-gray-500); font-weight: 600; margin-bottom: 1rem; }
        
        .status-badge { display: inline-block; padding: 0.4rem 1rem; border-radius: 99px; background: #FFF5F5; color: #DC3545; font-size: 0.75rem; font-weight: 700; margin-bottom: 2rem; }

        .stats-row { display: flex; justify-content: space-around; padding-top: 1.5rem; border-top: 1px solid var(--color-gray-50); }
        .stat-item { flex: 1; }
        .stat-val { display: block; font-size: 1rem; font-weight: 800; color: var(--color-primary); }
        .stat-lbl { display: block; font-size: 0.65rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; margin-top: 0.2rem; }

        /* ── Right Column: Form Edit ── */
        .form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-gray-100); }
        .section-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-50); padding-bottom: 0.5rem; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label { font-size: 0.85rem; font-weight: 700; color: var(--color-gray-700); }
        .form-input { padding: 0.8rem 1rem; border-radius: 10px; border: 1.5px solid var(--color-gray-200); font-family: inherit; font-size: 0.9rem; transition: 0.2s; }
        .form-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(30,58,95,0.05); }
        .form-input:disabled { background: var(--color-gray-50); cursor: not-allowed; color: var(--color-gray-400); }

        .btn-group { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; }
        .btn { padding: 0.8rem 2rem; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 0.9rem; transition: 0.2s; border: none; }
        .btn-save { background: var(--color-accent); color: #fff; box-shadow: var(--shadow-accent); }
        .btn-save:hover { background: var(--color-accent-dark); transform: translateY(-2px); }
        .btn-cancel { background: #fff; color: var(--color-gray-500); border: 1px solid var(--color-gray-200); }
        .btn-cancel:hover { background: var(--color-gray-50); color: var(--color-primary); }

        /* ── Toast Notification ── */
        #toast { position: fixed; top: 20px; right: 20px; background: #28A745; color: #fff; padding: 1rem 2rem; border-radius: 12px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transform: translateX(200%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); z-index: 1000; display: flex; align-items: center; gap: 0.75rem; }
        #toast.show { transform: translateX(0); }

        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Profil Saya</div>
            </div>
            <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
        </header>

        <main style="padding: 0 2rem 2rem;">
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

    <!-- Toast Notification -->
    <div id="toast">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        Profil berhasil diperbarui!
    </div>

    <script>
        function handleSave(e) {
            e.preventDefault();
            
            // Show toast
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
            
            return false;
        }
    </script>

</body>
</html>
