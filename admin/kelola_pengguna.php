<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'pengguna';

// Mock Data Pengguna
$users = [
    ['id' => 1, 'nama' => 'Andi Wijaya', 'email' => 'andi@company.com', 'role' => 'karyawan', 'divisi' => 'IT'],
    ['id' => 2, 'nama' => 'Maria Ulfa', 'email' => 'maria@company.com', 'role' => 'karyawan', 'divisi' => 'Marketing'],
    ['id' => 3, 'nama' => 'Budi Santoso', 'email' => 'budi@company.com', 'role' => 'hrd', 'divisi' => 'Human Resources'],
    ['id' => 4, 'nama' => 'Admin Utama', 'email' => 'admin@burnout.com', 'role' => 'admin', 'divisi' => '-'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 2rem; flex: 1; }

        .card { background: #fff; border-radius: 20px; padding: 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        .btn-add { background: var(--color-primary); color: #fff; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; border-bottom: 2px solid var(--color-gray-50); color: var(--color-gray-400); font-size: 0.8rem; text-transform: uppercase; }
        td { padding: 1rem; border-bottom: 1px solid var(--color-gray-50); font-size: 0.9rem; }
        
        .role-badge { padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .role-admin { background: #EBF2FA; color: #1E3A5F; }
        .role-hrd { background: #E8F5E9; color: #2E7D32; }
        .role-karyawan { background: #FFF3E0; color: #EF6C00; }

        .actions { display: flex; gap: 0.5rem; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid var(--color-gray-200); background: #fff; transition: 0.2s; }
        .btn-edit:hover { background: var(--color-primary-50); color: var(--color-primary); border-color: var(--color-primary); }
        .btn-delete:hover { background: var(--color-error-bg); color: var(--color-error); border-color: var(--color-error); }
    </style>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Manajemen Pengguna</div>
        <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
    </header>

    <main class="page-content">
        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Daftar Pengguna Sistem</h2>
                <button class="btn-add" onclick="alert('Fitur tambah user akan dihubungkan ke backend database.')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah User
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Divisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= $u['nama'] ?></td>
                            <td><?= $u['email'] ?></td>
                            <td><span class="role-badge role-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                            <td><?= $u['divisi'] ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit User">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="btn-icon btn-delete" title="Hapus User" onclick="confirm('Hapus pengguna ini?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
