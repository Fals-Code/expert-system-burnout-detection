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

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}
require_once '../includes/security.php';

$feedback = '';
$feedback_type = 'success';

// ── Handler Aksi (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }
    $action = $_POST['action'] ?? '';

    // ── GEJALA ──
    if ($action === 'add_gejala') {
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        // Validasi duplikat kode
        $exists = false;
        foreach ($_SESSION['mock_kb']['gejala'] as $g) {
            if ($g['id'] === $kode) { $exists = true; break; }
        }
        if ($exists) {
            $feedback = "Kode gejala <strong>{$kode}</strong> sudah ada. Gunakan kode yang berbeda.";
            $feedback_type = 'error';
        } elseif (empty($kode) || empty(trim($_POST['nama'] ?? ''))) {
            $feedback = "Kode dan nama gejala tidak boleh kosong.";
            $feedback_type = 'error';
        } else {
            $_SESSION['mock_kb']['gejala'][] = [
                'id'       => $kode,
                'nama'     => trim($_POST['nama']),
                'kategori' => $_POST['kategori'] ?? 'fisik',
                'bobot'    => (float)($_POST['bobot'] ?? 0.5),
            ];
            append_log($nama, 'CREATE_GEJALA', $kode, "Menambahkan gejala baru: \"" . trim($_POST['nama']) . "\"");
            $feedback = "Gejala <strong>{$kode}</strong> berhasil ditambahkan.";
        }

    } elseif ($action === 'update_gejala') {
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        foreach ($_SESSION['mock_kb']['gejala'] as &$g) {
            if ($g['id'] === $kode) {
                $g['nama']     = trim($_POST['nama']);
                $g['kategori'] = $_POST['kategori'] ?? $g['kategori'];
                $g['bobot']    = (float)($_POST['bobot'] ?? $g['bobot']);
                break;
            }
        }
        unset($g);
        append_log($nama, 'UPDATE_GEJALA', $kode, "Memperbarui gejala: \"" . trim($_POST['nama']) . "\"");
        $feedback = "Gejala <strong>{$kode}</strong> berhasil diperbarui.";

    } elseif ($action === 'delete_gejala') {
        $kode = $_POST['kode'] ?? '';
        $nama_gejala = '';
        foreach ($_SESSION['mock_kb']['gejala'] as $g) {
            if ($g['id'] === $kode) { $nama_gejala = $g['nama']; break; }
        }
        $_SESSION['mock_kb']['gejala'] = array_values(
            array_filter($_SESSION['mock_kb']['gejala'], fn($g) => $g['id'] !== $kode)
        );
        append_log($nama, 'DELETE_GEJALA', $kode, "Menghapus gejala: \"{$nama_gejala}\"");
        $feedback = "Gejala <strong>{$kode}</strong> berhasil dihapus.";

    // ── ATURAN ──
    } elseif ($action === 'add_aturan') {
        $gejala_arr = array_map('trim', array_filter(explode(',', $_POST['gejala'] ?? '')));
        $diagnosa   = $_POST['diagnosa'] ?? 'BURNOUT RENDAH';

        $color_map = [
            'BURNOUT TINGGI' => ['color' => '#DC3545', 'bg' => '#FFF5F5'],
            'BURNOUT SEDANG' => ['color' => '#F59E0B', 'bg' => '#FFFBEB'],
            'BURNOUT RENDAH' => ['color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ];
        $cm = $color_map[$diagnosa] ?? ['color' => '#1E3A5F', 'bg' => '#F8FAFB'];

        $new_id = 'R' . str_pad(count($_SESSION['mock_kb']['aturan']) + 1, 3, '0', STR_PAD_LEFT);
        $_SESSION['mock_kb']['aturan'][] = [
            'id'       => $new_id,
            'diagnosa' => $diagnosa,
            'gejala'   => $gejala_arr,
            'cf_pakar' => (float)($_POST['cf_pakar'] ?? 0.5),
            'color'    => $cm['color'],
            'bg_light' => $cm['bg'],
            'desc'     => trim($_POST['desc'] ?? 'Hasil dari aturan kustom Admin.'),
        ];
        append_log($nama, 'CREATE_ATURAN', $new_id, "Menambahkan aturan baru untuk: {$diagnosa}");
        $feedback = "Aturan <strong>{$new_id}</strong> berhasil ditambahkan.";

    } elseif ($action === 'update_aturan') {
        $rule_id    = $_POST['id'] ?? '';
        $gejala_arr = array_map('trim', array_filter(explode(',', $_POST['gejala'] ?? '')));
        $diagnosa   = $_POST['diagnosa'] ?? 'BURNOUT RENDAH';

        $color_map = [
            'BURNOUT TINGGI' => ['color' => '#DC3545', 'bg' => '#FFF5F5'],
            'BURNOUT SEDANG' => ['color' => '#F59E0B', 'bg' => '#FFFBEB'],
            'BURNOUT RENDAH' => ['color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ];
        $cm = $color_map[$diagnosa] ?? ['color' => '#1E3A5F', 'bg' => '#F8FAFB'];

        foreach ($_SESSION['mock_kb']['aturan'] as &$a) {
            if ($a['id'] === $rule_id) {
                $a['diagnosa'] = $diagnosa;
                $a['gejala']   = $gejala_arr;
                $a['cf_pakar'] = (float)($_POST['cf_pakar'] ?? $a['cf_pakar']);
                $a['color']    = $cm['color'];
                $a['bg_light'] = $cm['bg'];
                $a['desc']     = trim($_POST['desc'] ?? $a['desc']);
                break;
            }
        }
        unset($a);
        append_log($nama, 'UPDATE_ATURAN', $rule_id, "Memperbarui aturan: {$diagnosa}");
        $feedback = "Aturan <strong>{$rule_id}</strong> berhasil diperbarui.";

    } elseif ($action === 'delete_aturan') {
        $rule_id = $_POST['id'] ?? '';
        $_SESSION['mock_kb']['aturan'] = array_values(
            array_filter($_SESSION['mock_kb']['aturan'], fn($a) => $a['id'] !== $rule_id)
        );
        append_log($nama, 'DELETE_ATURAN', $rule_id, "Menghapus aturan {$rule_id}");
        $feedback = "Aturan <strong>{$rule_id}</strong> berhasil dihapus.";
    }

    // Re-index array
    $_SESSION['mock_kb']['gejala'] = array_values($_SESSION['mock_kb']['gejala']);
    $_SESSION['mock_kb']['aturan'] = array_values($_SESSION['mock_kb']['aturan']);

    if (!str_contains($action, 'delete') && !str_contains($feedback_type, 'error')) {
        header('Location: admin_knowledge.php?ok=' . urlencode($feedback));
    } else {
        header('Location: admin_knowledge.php?err=' . urlencode($feedback));
    }
    exit();
}

$gejala_list = $_SESSION['mock_kb']['gejala'];
$aturan_list = $_SESSION['mock_kb']['aturan'];

// Encode data untuk JS (edit modal prefill)
$gejala_json = json_encode(array_column($gejala_list, null, 'id'));
$aturan_json = json_encode(array_column($aturan_list, null, 'id'));
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
                            <td style="font-weight: 600;"><?= htmlspecialchars($g['nama']) ?></td>
                            <td><span class="badge-pill cat-<?= $g['kategori'] ?>"><?= ucfirst($g['kategori']) ?></span></td>
                            <td style="font-weight: 700;"><?= number_format($g['bobot'], 2) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit Gejala"
                                        onclick="openEditGejala(<?= htmlspecialchars(json_encode($g)) ?>)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus gejala <?= $g['id'] ?> ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_gejala">
                                        <input type="hidden" name="kode" value="<?= $g['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Hapus Gejala">
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
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus aturan <?= $a['id'] ?> ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_aturan">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Hapus Aturan">
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

<!-- ─── Modal Gejala (Tambah / Edit) ─── -->
<div class="modal-overlay" id="gejalaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="gejalaModalTitle">Tambah Gejala Baru</h3>
            <button class="modal-close" onclick="closeModal('gejalaModal')">&times;</button>
        </div>
        <form method="POST" id="gejalaForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="gejalaAction" value="add_gejala">
            <div class="form-group">
                <label class="form-label">Kode Gejala <span style="color:red">*</span></label>
                <input type="text" name="kode" id="gejalaKode" class="form-input"
                    placeholder="Contoh: G021" pattern="G\d{3}" required>
                <small style="color:var(--color-gray-400);">Format: G + 3 digit angka (G021, G022, dst.)</small>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Gejala <span style="color:red">*</span></label>
                <input type="text" name="nama" id="gejalaNama" class="form-input"
                    placeholder="Deskripsi gejala yang dialami..." required>
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
                <label class="form-label">Bobot Pakar (0.0 – 1.0)</label>
                <input type="number" name="bobot" id="gejalaBobot" step="0.05" max="1" min="0"
                    class="form-input" placeholder="0.0 - 1.0" required>
                <small style="color:var(--color-gray-400);">Semakin tinggi bobot, semakin berpengaruh terhadap CF final.</small>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none; cursor:pointer;">
                Simpan Data Gejala
            </button>
        </form>
    </div>
</div>

<!-- ─── Modal Aturan (Tambah / Edit) ─── -->
<div class="modal-overlay" id="aturanModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="aturanModalTitle">Tambah Aturan Baru</h3>
            <button class="modal-close" onclick="closeModal('aturanModal')">&times;</button>
        </div>
        <form method="POST" id="aturanForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="aturanAction" value="add_aturan">
            <input type="hidden" name="id" id="aturanId" value="">
            <div class="form-group">
                <label class="form-label">Diagnosa Target</label>
                <select name="diagnosa" id="aturanDiagnosa" class="form-input">
                    <option value="BURNOUT TINGGI">Burnout Tinggi</option>
                    <option value="BURNOUT SEDANG">Burnout Sedang</option>
                    <option value="BURNOUT RENDAH">Burnout Rendah</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kumpulan Gejala (pisahkan koma)</label>
                <input type="text" name="gejala" id="aturanGejala" class="form-input"
                    placeholder="Contoh: G001, G006, G009, G011" required>
                <small style="color:var(--color-gray-400);">Gunakan kode gejala yang sudah terdaftar di tab Gejala.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Certainty Factor Pakar (0.0 – 1.0)</label>
                <input type="number" name="cf_pakar" id="aturanCF" step="0.05" max="1" min="0"
                    class="form-input" placeholder="0.0 - 1.0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Hasil</label>
                <textarea name="desc" id="aturanDesc" class="form-input" rows="3"
                    placeholder="Penjelasan singkat kondisi yang terdiagnosis..."></textarea>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none; cursor:pointer;">
                Simpan Aturan
            </button>
        </form>
    </div>
</div>

<script>
    // ── Tab Switching ──
    function switchTab(target, el) {
        document.querySelectorAll('.tab-item').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');
        document.getElementById('section-gejala').style.display = target === 'gejala' ? 'block' : 'none';
        document.getElementById('section-aturan').style.display = target === 'aturan' ? 'block' : 'none';
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // ── Gejala Modal ──
    function openAddGejala() {
        document.getElementById('gejalaModalTitle').textContent = 'Tambah Gejala Baru';
        document.getElementById('gejalaAction').value = 'add_gejala';
        document.getElementById('gejalaKode').value   = '';
        document.getElementById('gejalaKode').readOnly = false;
        document.getElementById('gejalaNama').value   = '';
        document.getElementById('gejalaKategori').value = 'fisik';
        document.getElementById('gejalaBobot').value  = '';
        openModal('gejalaModal');
    }

    function openEditGejala(data) {
        document.getElementById('gejalaModalTitle').textContent = 'Edit Gejala ' + data.id;
        document.getElementById('gejalaAction').value  = 'update_gejala';
        document.getElementById('gejalaKode').value    = data.id;
        document.getElementById('gejalaKode').readOnly = true; // Kode tidak bisa diubah saat edit
        document.getElementById('gejalaNama').value    = data.nama;
        document.getElementById('gejalaKategori').value = data.kategori;
        document.getElementById('gejalaBobot').value   = data.bobot;
        openModal('gejalaModal');
    }

    // ── Aturan Modal ──
    function openAddAturan() {
        document.getElementById('aturanModalTitle').textContent = 'Tambah Aturan Baru';
        document.getElementById('aturanAction').value  = 'add_aturan';
        document.getElementById('aturanId').value      = '';
        document.getElementById('aturanDiagnosa').value = 'BURNOUT TINGGI';
        document.getElementById('aturanGejala').value  = '';
        document.getElementById('aturanCF').value      = '';
        document.getElementById('aturanDesc').value    = '';
        openModal('aturanModal');
    }

    function openEditAturan(data) {
        document.getElementById('aturanModalTitle').textContent = 'Edit Aturan ' + data.id;
        document.getElementById('aturanAction').value   = 'update_aturan';
        document.getElementById('aturanId').value       = data.id;
        document.getElementById('aturanDiagnosa').value = data.diagnosa;
        document.getElementById('aturanGejala').value   = data.gejala.join(', ');
        document.getElementById('aturanCF').value       = data.cf_pakar;
        document.getElementById('aturanDesc').value     = data.desc || '';
        openModal('aturanModal');
    }

    // Tutup modal saat klik overlay
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.style.display = 'none';
        }
    });
</script>
</body>
</html>
