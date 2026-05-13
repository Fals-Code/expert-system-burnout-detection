<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user        = $_SESSION['user'];
$nama        = $user['nama'];
$active_menu = 'basis_pengetahuan';
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Ambil Knowledge Base terbaru dari DB
$kb = include '../config/mock_db.php';
$gejala_list = $kb['gejala'];
$aturan_list = $kb['aturan'];

require_once '../includes/security.php';

$feedback = '';
$feedback_type = 'success';
$db = getDBConnection();

// ── Handler Aksi (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }
    $action = $_POST['action'] ?? '';

    try {
        // ── GEJALA ──
        if ($action === 'add_gejala') {
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama_g = trim($_POST['nama'] ?? '');
            $stmt = $db->prepare("INSERT INTO gejala (kode, nama, kategori, bobot) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kode, $nama_g, $_POST['kategori'], $_POST['bobot']]);
            append_log($user['id'], 'CREATE_GEJALA', $kode, "Menambahkan gejala baru: $nama_g");
            $feedback = "Gejala $kode berhasil ditambahkan.";

        } elseif ($action === 'update_gejala') {
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama_g = trim($_POST['nama'] ?? '');
            $stmt = $db->prepare("UPDATE gejala SET nama = ?, kategori = ?, bobot = ? WHERE kode = ?");
            $stmt->execute([$nama_g, $_POST['kategori'], $_POST['bobot'], $kode]);
            append_log($user['id'], 'UPDATE_GEJALA', $kode, "Memperbarui gejala: $nama_g");
            $feedback = "Gejala $kode berhasil diperbarui.";

        } elseif ($action === 'delete_gejala') {
            $kode = $_POST['kode'] ?? '';
            $stmt = $db->prepare("DELETE FROM gejala WHERE kode = ?");
            $stmt->execute([$kode]);
            append_log($user['id'], 'DELETE_GEJALA', $kode, "Menghapus gejala");
            $feedback = "Gejala $kode berhasil dihapus.";

        // ── ATURAN ──
        } elseif ($action === 'add_aturan') {
            $diagnosa_str = $_POST['diagnosa'] ?? 'BURNOUT RENDAH';
            $gejala_codes = array_map('trim', explode(',', $_POST['gejala'] ?? ''));
            $cf_pakar = (float)($_POST['cf_pakar'] ?? 0.5);

            // Get diagnosa_id
            $stmtD = $db->prepare("SELECT id FROM diagnosa WHERE nama = ?");
            $stmtD->execute([$diagnosa_str]);
            $diag = $stmtD->fetch();
            $diagnosa_id = $diag['id'] ?? 1;

            $db->beginTransaction();
            // 1. Create Aturan
            $new_kode = 'R' . str_pad(count($aturan_list) + 1, 3, '0', STR_PAD_LEFT);
            $stmtA = $db->prepare("INSERT INTO aturan (kode, diagnosa_id, cf_pakar) VALUES (?, ?, ?)");
            $stmtA->execute([$new_kode, $diagnosa_id, $cf_pakar]);
            $aturan_id = $db->lastInsertId();

            // 2. Link Gejala
            $stmtLink = $db->prepare("INSERT INTO aturan_gejala (aturan_id, gejala_id) SELECT ?, id FROM gejala WHERE kode = ?");
            foreach ($gejala_codes as $gc) {
                $stmtLink->execute([$aturan_id, $gc]);
            }
            $db->commit();
            append_log($user['id'], 'CREATE_ATURAN', $new_kode, "Menambahkan aturan baru untuk $diagnosa_str");
            $feedback = "Aturan $new_kode berhasil ditambahkan.";

        } elseif ($action === 'update_aturan') {
            $rule_kode = $_POST['id'] ?? '';
            $diagnosa_str = $_POST['diagnosa'] ?? 'BURNOUT RENDAH';
            $gejala_codes = array_map('trim', explode(',', $_POST['gejala'] ?? ''));
            $cf_pakar = (float)($_POST['cf_pakar'] ?? 0.5);

            $stmtD = $db->prepare("SELECT id FROM diagnosa WHERE nama = ?");
            $stmtD->execute([$diagnosa_str]);
            $diag = $stmtD->fetch();
            $diagnosa_id = $diag['id'] ?? 1;

            $db->beginTransaction();
            // 1. Update Aturan
            $stmtA = $db->prepare("UPDATE aturan SET diagnosa_id = ?, cf_pakar = ? WHERE kode = ?");
            $stmtA->execute([$diagnosa_id, $cf_pakar, $rule_kode]);
            
            // Get internal ID
            $stmtID = $db->prepare("SELECT id FROM aturan WHERE kode = ?");
            $stmtID->execute([$rule_kode]);
            $aturan_id = $stmtID->fetchColumn();

            // 2. Refresh Gejala Links
            $db->prepare("DELETE FROM aturan_gejala WHERE aturan_id = ?")->execute([$aturan_id]);
            $stmtLink = $db->prepare("INSERT INTO aturan_gejala (aturan_id, gejala_id) SELECT ?, id FROM gejala WHERE kode = ?");
            foreach ($gejala_codes as $gc) {
                $stmtLink->execute([$aturan_id, $gc]);
            }
            $db->commit();
            append_log($user['id'], 'UPDATE_ATURAN', $rule_kode, "Memperbarui aturan untuk $diagnosa_str");
            $feedback = "Aturan $rule_kode berhasil diperbarui.";

        } elseif ($action === 'delete_aturan') {
            $rule_kode = $_POST['id'] ?? '';
            $stmt = $db->prepare("DELETE FROM aturan WHERE kode = ?");
            $stmt->execute([$rule_kode]);
            append_log($user['id'], 'DELETE_ATURAN', $rule_kode, "Menghapus aturan");
            $feedback = "Aturan $rule_kode berhasil dihapus.";
        }

        header('Location: admin_knowledge.php?ok=' . urlencode($feedback));
        exit();

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        header('Location: admin_knowledge.php?err=' . urlencode("Gagal: " . $e->getMessage()));
        exit();
    }
}
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

        <?php if (isset($_GET['ok'])): ?>
        <div class="alert-inline alert-inline--success" style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ <?= htmlspecialchars($_GET['ok']) ?>
        </div>
        <?php elseif (isset($_GET['err'])): ?>
        <div class="alert-inline alert-inline--error" style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#FFF5F5; border:1px solid #FECACA; color:#991B1B;">
            ❌ <?= htmlspecialchars($_GET['err']) ?>
        </div>
        <?php endif; ?>

        <div class="segmented-tabs">
            <button class="tab-item active" onclick="switchTab('gejala', this)">
                Gejala (Fakta) — <?= count($gejala_list) ?>
            </button>
            <button class="tab-item" onclick="switchTab('aturan', this)">
                Aturan (Rules) — <?= count($aturan_list) ?>
            </button>
        </div>

        <!-- Section Gejala -->
        <div id="section-gejala" class="content-card">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Daftar Gejala Burnout</h2>
                <button class="btn-add" onclick="openAddGejala()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Gejala
                </button>
            </div>
            <div class="table-container">
                <table class="premium-table" id="table-gejala">
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
                            <td style="font-weight: 600;"><?= htmlspecialchars($g['nama']) ?></td>
                            <td><span class="badge-pill cat-<?= $g['kategori'] ?>"><?= ucfirst($g['kategori']) ?></span></td>
                            <td style="font-weight: 700;"><?= number_format($g['bobot'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit Gejala"
                                        onclick="openEditGejala(<?= htmlspecialchars(json_encode(['id'=>$g['kode'], 'nama'=>$g['nama'], 'kategori'=>$g['kategori'], 'bobot'=>$g['bobot']])) ?>)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form method="POST" style="display:inline;" class="form-delete">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_gejala">
                                        <input type="hidden" name="kode" value="<?= $g['kode'] ?>">
                                        <button type="button" class="btn-icon btn-delete" title="Hapus Gejala" onclick="confirmDelete(this, 'gejala <?= $g['kode'] ?>')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Aturan -->
        <div id="section-aturan" class="content-card" style="display: none;">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">Aturan Backward Chaining</h2>
                <button class="btn-add" onclick="openAddAturan()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Aturan
                </button>
            </div>
            <div class="table-container">
                <table class="premium-table" id="table-aturan">
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
                            <td>
                                <span style="font-weight:700; color:<?= $a['color'] ?>;">
                                    <?= htmlspecialchars($a['diagnosa']) ?>
                                </span>
                            </td>
                            <td><?= implode('', array_map(fn($gj) => "<span class=\"badge-chip\">{$gj}</span>", $a['gejala'])) ?></td>
                            <td style="font-weight: 700; color: var(--color-accent);"><?= number_format($a['cf_pakar'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit Aturan"
                                        onclick="openEditAturan(<?= htmlspecialchars(json_encode($a)) ?>)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form method="POST" style="display:inline;" class="form-delete">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_aturan">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="button" class="btn-icon btn-delete" title="Hapus Aturan" onclick="confirmDelete(this, 'aturan <?= $a['id'] ?>')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
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

<!-- ─── Modal Gejala ─── -->
<div class="modal-overlay" id="gejalaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="gejalaModalTitle">Tambah Gejala</h3>
            <button class="modal-close" onclick="closeModal('gejalaModal')">&times;</button>
        </div>
        <form method="POST" id="gejalaForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="gejalaAction" value="add_gejala">
            <div class="form-group">
                <label class="form-label">Kode Gejala</label>
                <input type="text" name="kode" id="gejalaKode" class="form-input" placeholder="G021" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Gejala</label>
                <input type="text" name="nama" id="gejalaNama" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="kategori" id="gejalaKategori" class="form-input">
                    <option value="fisik">Fisik</option>
                    <option value="emosional">Emosional</option>
                    <option value="perilaku">Perilaku</option>
                    <option value="kognitif">Kognitif</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Bobot Pakar</label>
                <input type="number" name="bobot" id="gejalaBobot" step="0.05" max="1" min="0" class="form-input" required>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none;">Simpan</button>
        </form>
    </div>
</div>

<!-- ─── Modal Aturan ─── -->
<div class="modal-overlay" id="aturanModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="aturanModalTitle">Tambah Aturan</h3>
            <button class="modal-close" onclick="closeModal('aturanModal')">&times;</button>
        </div>
        <form method="POST" id="aturanForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="aturanAction" value="add_aturan">
            <input type="hidden" name="id" id="aturanId" value="">
            <div class="form-group">
                <label class="form-label">Diagnosa Target</label>
                <select name="diagnosa" id="aturanDiagnosa" class="form-input">
                    <option value="Burnout Tinggi">Burnout Tinggi</option>
                    <option value="Burnout Sedang">Burnout Sedang</option>
                    <option value="Burnout Rendah">Burnout Rendah</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kumpulan Gejala (kode, pisahkan koma)</label>
                <input type="text" name="gejala" id="aturanGejala" class="form-input" placeholder="G001, G002" required>
            </div>
            <div class="form-group">
                <label class="form-label">CF Pakar</label>
                <input type="number" name="cf_pakar" id="aturanCF" step="0.05" max="1" min="0" class="form-input" required>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none;">Simpan Aturan</button>
        </form>
    </div>
</div>

<script>
    function switchTab(target, el) {
        document.querySelectorAll('.tab-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('section-gejala').style.display = target === 'gejala' ? 'block' : 'none';
        document.getElementById('section-aturan').style.display = target === 'aturan' ? 'block' : 'none';
    }

    function openModal(id) {
        const m = document.getElementById(id);
        m.style.display = 'flex';
        setTimeout(() => m.classList.add('active'), 10);
    }
    function closeModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('active');
        setTimeout(() => m.style.display = 'none', 300);
    }

    function openAddGejala() {
        document.getElementById('gejalaModalTitle').textContent = 'Tambah Gejala';
        document.getElementById('gejalaAction').value = 'add_gejala';
        document.getElementById('gejalaForm').reset();
        document.getElementById('gejalaKode').readOnly = false;
        openModal('gejalaModal');
    }
    function openEditGejala(d) {
        document.getElementById('gejalaModalTitle').textContent = 'Edit Gejala ' + d.id;
        document.getElementById('gejalaAction').value = 'update_gejala';
        document.getElementById('gejalaKode').value = d.id;
        document.getElementById('gejalaKode').readOnly = true;
        document.getElementById('gejalaNama').value = d.nama;
        document.getElementById('gejalaKategori').value = d.kategori;
        document.getElementById('gejalaBobot').value = d.bobot;
        openModal('gejalaModal');
    }

    function openAddAturan() {
        document.getElementById('aturanModalTitle').textContent = 'Tambah Aturan';
        document.getElementById('aturanAction').value = 'add_aturan';
        document.getElementById('aturanForm').reset();
        openModal('aturanModal');
    }
    function openEditAturan(d) {
        document.getElementById('aturanModalTitle').textContent = 'Edit Aturan ' + d.id;
        document.getElementById('aturanAction').value = 'update_aturan';
        document.getElementById('aturanId').value = d.id;
        document.getElementById('aturanDiagnosa').value = d.diagnosa;
        document.getElementById('aturanGejala').value = d.gejala.join(', ');
        document.getElementById('aturanCF').value = d.cf_pakar;
        openModal('aturanModal');
    }

    window.onclick = function(e) { if(e.target.classList.contains('modal-overlay')) closeModal(e.target.id); }

    document.addEventListener('DOMContentLoaded', () => {
        new simpleDatatables.DataTable("#table-gejala");
        new simpleDatatables.DataTable("#table-aturan");
    });

    function confirmDelete(btn, item) {
        Swal.fire({
            title: 'Hapus?',
            text: 'Data ' + item + ' akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((res) => { if(res.isConfirmed) btn.closest('form').submit(); });
    }
</script>
</body>
</html>
