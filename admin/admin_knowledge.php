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

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}

// ── Handler Aksi (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_gejala') {
        $_SESSION['mock_kb']['gejala'][] = [
            'id' => $_POST['kode'],
            'nama' => $_POST['nama'],
            'kategori' => $_POST['kategori'],
            'bobot' => (float)$_POST['bobot']
        ];
    } elseif ($action === 'delete_gejala') {
        $kode = $_POST['kode'];
        $_SESSION['mock_kb']['gejala'] = array_filter($_SESSION['mock_kb']['gejala'], fn($g) => $g['id'] !== $kode);
    } elseif ($action === 'add_aturan') {
        $gejala_arr = array_map('trim', explode(',', $_POST['gejala']));
        $diagnosa = $_POST['diagnosa'];
        
        $color = '#1E3A5F'; $bg = '#F8FAFB'; $desc = '';
        if ($diagnosa === 'BURNOUT TINGGI') { $color = '#DC3545'; $bg = '#FFF5F5'; }
        elseif ($diagnosa === 'BURNOUT SEDANG') { $color = '#F59E0B'; $bg = '#FFFBEB'; }
        elseif ($diagnosa === 'BURNOUT RENDAH') { $color = '#10B981'; $bg = '#F0FFF4'; }
        
        $_SESSION['mock_kb']['aturan'][] = [
            'id' => 'R' . str_pad(count($_SESSION['mock_kb']['aturan']) + 1, 3, '0', STR_PAD_LEFT),
            'diagnosa' => $diagnosa,
            'gejala' => $gejala_arr,
            'cf_pakar' => (float)$_POST['cf_pakar'],
            'color' => $color,
            'bg_light' => $bg,
            'desc' => 'Hasil dari aturan kustom Admin.'
        ];
    } elseif ($action === 'delete_aturan') {
        $id = $_POST['id'];
        $_SESSION['mock_kb']['aturan'] = array_filter($_SESSION['mock_kb']['aturan'], fn($a) => $a['id'] !== $id);
    }
    
    // Re-index array
    $_SESSION['mock_kb']['gejala'] = array_values($_SESSION['mock_kb']['gejala']);
    $_SESSION['mock_kb']['aturan'] = array_values($_SESSION['mock_kb']['aturan']);
    
    header('Location: admin_knowledge.php');
    exit();
}

$gejala_list = $_SESSION['mock_kb']['gejala'];
$aturan_list = $_SESSION['mock_kb']['aturan'];
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
        <div class="segmented-tabs">
            <button class="tab-item active" onclick="switchTab('gejala')">Gejala (Fakta)</button>
            <button class="tab-item" onclick="switchTab('aturan')">Aturan (Rules)</button>
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
            <div class="table-container">
                <table class="premium-table">
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
                            <td style="font-weight: 800; color: var(--color-primary);"><?= $g['id'] ?></td>
                            <td style="font-weight: 600;"><?= $g['nama'] ?></td>
                            <td><span class="badge-pill cat-<?= $g['kategori'] ?>"><?= ucfirst($g['kategori']) ?></span></td>
                            <td style="font-weight: 700;"><?= number_format($g['bobot'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" onclick="openModal('gejalaModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus gejala ini?')">
                                        <input type="hidden" name="action" value="delete_gejala">
                                        <input type="hidden" name="kode" value="<?= $g['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                    </form>
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
            <div class="table-container">
                <table class="premium-table">
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
                            <td style="font-weight: 800; color: var(--color-primary);"><?= $a['id'] ?></td>
                            <td style="font-weight: 700;"><?= $a['diagnosa'] ?></td>
                            <td><?= implode('', array_map(fn($gj) => "<span class=\"badge-chip\">$gj</span>", $a['gejala'])) ?></td>
                            <td style="font-weight: 700; color: var(--color-accent);"><?= number_format($a['cf_pakar'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" onclick="openModal('aturanModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus aturan ini?')">
                                        <input type="hidden" name="action" value="delete_aturan">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                    </form>
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
        <form method="POST">
            <input type="hidden" name="action" value="add_gejala">
            <div class="form-group">
                <label class="form-label">Kode Gejala</label>
                <input type="text" name="kode" class="form-input" placeholder="Misal: G011" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Gejala</label>
                <input type="text" name="nama" class="form-input" placeholder="Masukkan deskripsi gejala..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-input">
                    <option value="fisik">Fisik</option>
                    <option value="emosional">Emosional</option>
                    <option value="perilaku">Perilaku</option>
                    <option value="kognitif">Kognitif</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Bobot Pakar (0.0 - 1.0)</label>
                <input type="number" name="bobot" step="0.05" max="1" min="0" class="form-input" required>
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
        <form method="POST">
            <input type="hidden" name="action" value="add_aturan">
            <div class="form-group">
                <label class="form-label">Diagnosa Target</label>
                <select name="diagnosa" class="form-input">
                    <option value="BURNOUT TINGGI">Burnout Tinggi</option>
                    <option value="BURNOUT SEDANG">Burnout Sedang</option>
                    <option value="BURNOUT RENDAH">Burnout Rendah</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kumpulan Gejala (Pisahkan koma)</label>
                <input type="text" name="gejala" class="form-input" placeholder="Misal: G001, G002, G005" required>
            </div>
            <div class="form-group">
                <label class="form-label">Certainty Factor Pakar</label>
                <input type="number" name="cf_pakar" step="0.05" max="1" min="0" class="form-input" required>
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

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    })
</script>
</body>
</html>
