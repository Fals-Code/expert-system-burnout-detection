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
    <title>Kelola Pengguna – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Manajemen Pengguna";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Daftar Pengguna Sistem</h2>
                <button class="btn-add" onclick="openModal('userModal')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah User
                </button>
            </div>

            <div style="overflow-x: auto; min-height: 200px; position: relative;">
                <!-- Skeleton Loader -->
                <div id="tableSkeleton" style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
                    <div class="skeleton sk-text" style="height: 40px;"></div>
                    <div class="skeleton sk-text" style="height: 60px;"></div>
                    <div class="skeleton sk-text" style="height: 60px;"></div>
                    <div class="skeleton sk-text" style="height: 60px;"></div>
                </div>

                <!-- Empty State (Hidden by default) -->
                <?php if (empty($users)): ?>
                <div id="emptyState" style="display: none; text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">👥</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem;">Belum Ada Pengguna</h3>
                    <p style="color: var(--color-gray-500); font-size: 0.9rem; margin-bottom: 1.5rem;">Sistem saat ini belum memiliki data pengguna yang terdaftar.</p>
                    <button class="btn-add" onclick="openModal('userModal')" style="margin: 0 auto;">
                        Tambah User Pertama
                    </button>
                </div>
                <?php endif; ?>

                <!-- Data Table -->
                <table id="dataTable" style="display: none;">
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
                                    <button class="btn-icon btn-edit" title="Edit User" onclick="openModal('userModal')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="btn-icon btn-delete" title="Hapus User" onclick="if(confirm('Hapus pengguna ini?')) alert('User berhasil dihapus (Mock)')">
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

<!-- Add/Edit User Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Manajemen Pengguna</h3>
            <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Data berhasil disimpan (Mock)'); closeModal('userModal');">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-input" placeholder="Masukkan nama..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" placeholder="contoh@burnoutxpert.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-input">
                    <option value="karyawan">Karyawan</option>
                    <option value="hrd">HRD</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Divisi</label>
                <input type="text" class="form-input" placeholder="Masukkan divisi...">
            </div>
            <button type="submit" class="btn-submit">Simpan Data</button>
        </form>
    </div>
</div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }

        // Simulate Data Loading for Skeleton effect
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                const skeleton = document.getElementById('tableSkeleton');
                const table = document.getElementById('dataTable');
                const emptyState = document.getElementById('emptyState');
                const hasData = <?= empty($users) ? 'false' : 'true' ?>;
                
                if (skeleton) skeleton.style.display = 'none';
                
                if (hasData && table) {
                    table.style.display = 'table';
                    table.style.animation = 'fadeInPage 0.4s ease-out';
                } else if (!hasData && emptyState) {
                    emptyState.style.display = 'block';
                    emptyState.style.animation = 'fadeInPage 0.4s ease-out';
                }
            }, 800); // 800ms artificial delay for demonstration
        });
    </script>
</body>
</html>
