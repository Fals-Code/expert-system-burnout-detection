<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = "Budi Santoso"; // As requested
$active_menu = 'profil';
$initials = "BS";
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
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar { width: 260px; background: var(--color-primary); min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 100; color: #fff; }
        .sidebar-brand { padding: 2rem 1.5rem; font-size: 1.25rem; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-nav { padding: 1.5rem 0.75rem; }
        .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.8rem 1rem; border-radius: 10px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem; }
        .nav-item.active { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-item svg { width: 18px; height: 18px; }

        .main-wrapper { margin-left: 260px; flex: 1; padding: 2rem; }

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

        @media (max-width: 992px) {
            .profile-grid { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">Burnout<span>Xpert</span></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="deteksi.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Mulai Deteksi
            </a>
            <a href="riwayat.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                Riwayat Hasil
            </a>
            <a href="profil.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profil Saya
            </a>
        </nav>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <div class="profile-grid">
            
            <!-- Left Column: Profile Card -->
            <aside class="profile-card">
                <div class="avatar-container">
                    <div class="avatar-large"><?= $initials ?></div>
                    <button class="btn-change-photo">Ganti Foto</button>
                </div>
                <h1 class="profile-name"><?= $nama ?></h1>
                <p class="profile-title">Staff IT - Teknologi Informasi</p>
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
                            <input type="text" class="form-input" value="Budi Santoso">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" class="form-input" value="budi.santoso@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-input" value="0812-3456-7890">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Divisi</label>
                            <input type="text" class="form-input" value="Teknologi Informasi" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-input" value="Staff IT" disabled>
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
