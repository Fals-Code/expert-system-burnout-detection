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

// Mock Rules Data (Backward Chaining Hypotheses)
$rules = [
    ['no' => 1, 'then' => 'Burnout Sangat Tinggi', 'if' => 'G01 AND G02 AND G03 AND G07 AND G08 AND G09'],
    ['no' => 2, 'then' => 'Burnout Tinggi',        'if' => 'G01 AND G02 AND G05 AND G07 AND G10'],
    ['no' => 3, 'then' => 'Burnout Sedang',        'if' => 'G01 AND G04 AND G06 AND G10'],
    ['no' => 4, 'then' => 'Burnout Rendah',        'if' => 'G04 AND G09'],
    ['no' => 5, 'then' => 'Normal',                'if' => 'Tidak ada rule yang terbukti'],
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

        /* ── Main Wrapper ── */
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
        }

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

<?php 
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
include '../includes/sidebar_admin.php'; 
?>

    <!-- MAIN WRAPPER -->
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
                <div class="topbar__title">Kelola Basis Pengetahuan</div>
            </div>
            <div style="font-size: 0.875rem; font-weight: 700; color: var(--color-gray-600);"><?= $user['nama'] ?> 🛡️</div>
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
                <div class="section-header" style="margin-bottom: 0.5rem;">
                    <h3 class="card-title" style="margin-bottom:0;">Aturan Keputusan (Rules)</h3>
                </div>
                <p style="font-size: 0.8rem; color: var(--color-gray-500); margin-bottom: 1.5rem;">
                    💡 Sistem membuktikan hipotesis secara berurutan dari no. 1 (tertinggi) ke bawah menggunakan metode Backward Chaining.
                </p>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 150px;">Urutan Pembuktian</th>
                                <th>Hasil (Hipotesis / THEN)</th>
                                <th>Kondisi (Premis / IF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $r): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: var(--color-primary);"><?= $r['no'] ?></td>
                                <td><strong><?= $r['then'] ?></strong></td>
                                <td style="font-family: monospace; color: var(--color-primary); font-size: 0.85rem;"><?= $r['if'] ?></td>
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
