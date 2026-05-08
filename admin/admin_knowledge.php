<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$active_menu = 'gejala';

// Mock Gejala Data
$gejala_list = [
    ['kode' => 'G01', 'nama' => 'Kelelahan Emosional', 'kategori' => 'Emosional', 'status' => 'Aktif'],
    ['kode' => 'G02', 'nama' => 'Mudah Marah', 'kategori' => 'Emosional', 'status' => 'Aktif'],
    ['kode' => 'G03', 'nama' => 'Sakit Kepala Berulang', 'kategori' => 'Fisik', 'status' => 'Aktif'],
    ['kode' => 'G04', 'nama' => 'Sulit Tidur (Insomnia)', 'kategori' => 'Fisik', 'status' => 'Aktif'],
    ['kode' => 'G05', 'nama' => 'Menarik Diri dari Sosial', 'kategori' => 'Perilaku', 'status' => 'Aktif'],
    ['kode' => 'G06', 'nama' => 'Menunda-nunda Pekerjaan', 'kategori' => 'Perilaku', 'status' => 'Aktif'],
    ['kode' => 'G07', 'nama' => 'Hilang Minat Hobi', 'kategori' => 'Emosional', 'status' => 'Aktif'],
    ['kode' => 'G08', 'nama' => 'Gangguan Pencernaan', 'kategori' => 'Fisik', 'status' => 'Aktif'],
    ['kode' => 'G09', 'nama' => 'Sering Bolos Kerja', 'kategori' => 'Perilaku', 'status' => 'Aktif'],
    ['kode' => 'G10', 'nama' => 'Merasa Tidak Berharga', 'kategori' => 'Emosional', 'status' => 'Aktif'],
];

// Mock Rules Data
$rules = [
    ['if' => 'G01, G02, G07, G10', 'then' => 'Burnout Tinggi'],
    ['if' => 'G03, G04, G08', 'then' => 'Burnout Sedang'],
    ['if' => 'G05, G06, G09', 'then' => 'Burnout Rendah'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Basis Pengetahuan – Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px; min-height: 100vh; background: #1E3A5F;
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand { padding: 2rem 1.5rem; font-size: 1.25rem; font-weight: 800; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-brand span { color: var(--color-accent); }
        
        .sidebar-nav { flex: 1; padding: 1.5rem 0.75rem; }
        .nav-label { font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 1rem 0.75rem; }
        .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.8rem 1rem; border-radius: 10px; color: rgba(255,255,255,0.6); font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-item.active { background: var(--color-accent); color: #fff; }
        .nav-item svg { width: 18px; height: 18px; }

        /* ── Main Content ── */
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 90; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
        
        .page-content { padding: 2rem; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .btn-add { background: var(--color-primary); color: #fff; padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; transition: 0.2s; }
        .btn-add:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

        /* ── Tables ── */
        .content-card { background: #fff; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); padding: 1.5rem; margin-bottom: 2rem; }
        .card-title { font-size: 1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.25rem; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.75rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; border-bottom: 2px solid var(--color-gray-50); }
        td { padding: 1rem; font-size: 0.9rem; color: var(--color-gray-700); border-bottom: 1px solid var(--color-gray-50); }
        
        .badge { padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-aktif { background: var(--color-success-bg); color: var(--color-success); }
        .badge-kategori { background: var(--color-primary-50); color: var(--color-primary); }

        .btn-icon { background: none; border: none; cursor: pointer; color: var(--color-gray-400); transition: 0.2s; }
        .btn-icon:hover { color: var(--color-primary); }
        .btn-delete:hover { color: var(--color-error); }

        /* ── Modal ── */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal.open { display: flex; }
        .modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 500px; padding: 2rem; box-shadow: var(--shadow-xl); animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .modal-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-gray-400); }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--color-gray-700); margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: var(--color-primary); }

        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
        .btn-cancel { background: var(--color-gray-100); color: var(--color-gray-600); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-save { background: var(--color-primary); color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">Burnout<span>Xpert</span></div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="dashboard.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard Admin
            </a>
            <a href="admin_knowledge.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Kelola Gejala
            </a>
            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                Kelola Aturan
            </a>
            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Kelola Pengguna
            </a>
            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                Laporan
            </a>
        </nav>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar__title">Kelola Basis Pengetahuan</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: var(--color-gray-500);"><?= $user['nama'] ?> (Admin)</div>
        </header>

        <main class="page-content">
            
            <div class="section-header">
                <h2 class="card-title" style="margin-bottom:0;">Basis Pengetahuan</h2>
                <button class="btn-add" onclick="openModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Gejala
                </button>
            </div>

            <!-- TABEL GEJALA -->
            <div class="content-card">
                <h3 class="card-title">Daftar Gejala Burnout</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Gejala</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gejala_list as $g): ?>
                            <tr>
                                <td><strong><?= $g['kode'] ?></strong></td>
                                <td><?= htmlspecialchars($g['nama']) ?></td>
                                <td><span class="badge badge-kategori"><?= $g['kategori'] ?></span></td>
                                <td><span class="badge badge-aktif"><?= $g['status'] ?></span></td>
                                <td>
                                    <button class="btn-icon" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                    <button class="btn-icon btn-delete" title="Hapus"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL ATURAN (RULES) -->
            <div class="content-card">
                <h3 class="card-title">Aturan Keputusan (Rules)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kondisi (IF Symptoms)</th>
                                <th>Hasil (THEN Diagnosis)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $r): ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--color-primary);">IF <?= $r['if'] ?></td>
                                <td><strong>THEN <?= $r['then'] ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH GEJALA -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Tambah Gejala Baru</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form>
                <div class="form-group">
                    <label class="form-label">Kode Gejala</label>
                    <input type="text" class="form-control" placeholder="Contoh: G11">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Gejala</label>
                    <input type="text" class="form-control" placeholder="Masukkan gejala...">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select class="form-control">
                        <option>Emosional</option>
                        <option>Fisik</option>
                        <option>Perilaku</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control">
                        <option>Aktif</option>
                        <option>Nonaktif</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-save">Simpan Gejala</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addModal').classList.add('open');
        }
        function closeModal() {
            document.getElementById('addModal').classList.remove('open');
        }
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('addModal')) {
                closeModal();
            }
        }
    </script>

</body>
</html>
