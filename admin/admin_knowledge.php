<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'basis_pengetahuan';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Mock Data Gejala
$gejala_list = [
    ['id' => 1, 'kode' => 'G001', 'nama' => 'Merasa kelelahan fisik setelah bekerja', 'kategori' => 'fisik', 'bobot' => 1.00],
    ['id' => 2, 'kode' => 'G002', 'nama' => 'Sakit kepala atau nyeri otot yang sering', 'kategori' => 'fisik', 'bobot' => 0.80],
    ['id' => 3, 'kode' => 'G003', 'nama' => 'Gangguan tidur (insomnia)', 'kategori' => 'fisik', 'bobot' => 0.90],
    ['id' => 4, 'kode' => 'G005', 'nama' => 'Merasa hampa dan tidak bersemangat', 'kategori' => 'emosional', 'bobot' => 1.00],
];

// Mock Data Aturan
$aturan_list = [
    ['kode' => 'R001', 'diagnosa' => 'Burnout Ringan', 'gejala' => 'G001, G002', 'cf' => 0.60],
    ['kode' => 'R002', 'diagnosa' => 'Burnout Sedang', 'gejala' => 'G001, G005, G006', 'cf' => 0.75],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Basis Pengetahuan – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Basis Pengetahuan";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="tabs">
            <div class="tab-item active" onclick="switchTab('gejala')">Gejala (Fakta)</div>
            <div class="tab-item" onclick="switchTab('aturan')">Aturan (Rules)</div>
        </div>

        <!-- Section Gejala -->
        <div id="section-gejala" class="content-card">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Daftar Gejala Burnout</h2>
                <button class="btn-add" onclick="openModal('gejalaModal')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Gejala
                </button>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Gejala</th>
                            <th>Kategori</th>
                            <th>Bobot Pakar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gejala_list as $g): ?>
                        <tr>
                            <td style="font-weight: 800; color: var(--color-primary);"><?= $g['kode'] ?></td>
                            <td style="font-weight: 600;"><?= $g['nama'] ?></td>
                            <td><span class="badge-cat cat-<?= $g['kategori'] ?>"><?= $g['kategori'] ?></span></td>
                            <td style="font-weight: 700;"><?= number_format($g['bobot'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" onclick="openModal('gejalaModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                                    <button class="btn-icon btn-delete" onclick="if(confirm('Hapus gejala ini?')) alert('Data berhasil dihapus (Mock)')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Aturan (Hidden by Default) -->
        <div id="section-aturan" class="content-card" style="display: none;">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Aturan Backward Chaining</h2>
                <button class="btn-add" onclick="openModal('aturanModal')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Aturan
                </button>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Diagnosa Target</th>
                            <th>Kumpulan Gejala</th>
                            <th>CF Pakar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aturan_list as $a): ?>
                        <tr>
                            <td style="font-weight: 800; color: var(--color-primary);"><?= $a['kode'] ?></td>
                            <td style="font-weight: 700;"><?= $a['diagnosa'] ?></td>
                            <td><code style="background: var(--color-gray-50); padding: 0.2rem 0.5rem; border-radius: 4px;"><?= $a['gejala'] ?></code></td>
                            <td style="font-weight: 700; color: var(--color-accent);"><?= number_format($a['cf'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" onclick="openModal('aturanModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                                    <button class="btn-icon btn-delete" onclick="if(confirm('Hapus aturan ini?')) alert('Data berhasil dihapus (Mock)')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
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

<!-- Modal Gejala -->
<div class="modal-overlay" id="gejalaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Manajemen Gejala</h3>
            <button class="modal-close" onclick="closeModal('gejalaModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Data berhasil disimpan (Mock)'); closeModal('gejalaModal');">
            <div class="form-group">
                <label class="form-label">Kode Gejala</label>
                <input type="text" class="form-input" placeholder="Misal: G001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Gejala</label>
                <input type="text" class="form-input" placeholder="Masukkan deskripsi gejala..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select class="form-input">
                    <option value="fisik">Fisik</option>
                    <option value="emosional">Emosional</option>
                    <option value="perilaku">Perilaku</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Bobot Pakar (0.0 - 1.0)</label>
                <input type="number" step="0.1" max="1" min="0" class="form-input" required>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none; cursor:pointer;">Simpan Data</button>
        </form>
    </div>
</div>

<!-- Modal Aturan -->
<div class="modal-overlay" id="aturanModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Manajemen Aturan</h3>
            <button class="modal-close" onclick="closeModal('aturanModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Data berhasil disimpan (Mock)'); closeModal('aturanModal');">
            <div class="form-group">
                <label class="form-label">Diagnosa Target</label>
                <select class="form-input">
                    <option value="Burnout Ringan">Burnout Ringan</option>
                    <option value="Burnout Sedang">Burnout Sedang</option>
                    <option value="Burnout Berat">Burnout Berat</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kumpulan Gejala (Pisahkan koma)</label>
                <input type="text" class="form-input" placeholder="Misal: G001, G002, G005" required>
            </div>
            <div class="form-group">
                <label class="form-label">Certainty Factor Pakar</label>
                <input type="number" step="0.01" max="1" min="0" class="form-input" required>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none; cursor:pointer;">Simpan Aturan</button>
        </form>
    </div>
</div>

<script>
    function switchTab(target) {
        document.querySelectorAll('.tab-item').forEach(item => item.classList.remove('active'));
        if (event) event.target.classList.add('active');

        if (target === 'gejala') {
            document.getElementById('section-gejala').style.display = 'block';
            document.getElementById('section-aturan').style.display = 'none';
        } else {
            document.getElementById('section-gejala').style.display = 'none';
            document.getElementById('section-aturan').style.display = 'block';
        }
    }

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
</script>
</body>
</html>
