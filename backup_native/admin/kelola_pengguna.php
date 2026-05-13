<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();
require_once '../includes/security.php';

$user        = $_SESSION['user'];
$nama        = $user['nama'];
$active_menu = 'pengguna';
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

$db = getDBConnection();

// ── Handler POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_user') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $pw    = password_hash(trim($_POST['password'] ?? 'password'), PASSWORD_DEFAULT);
            $nama_u = trim($_POST['nama'] ?? '');
            $role  = $_POST['role'] ?? 'karyawan';
            $div_id = !empty($_POST['divisi_id']) ? $_POST['divisi_id'] : null;

            $stmt = $db->prepare("INSERT INTO users (nama, email, password, role, divisi_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama_u, $email, $pw, $role, $div_id]);
            
            append_log($user['id'], 'CREATE_USER', $email, "Menambahkan pengguna baru: $nama_u");
            header('Location: kelola_pengguna.php?ok=Pengguna+berhasil+ditambahkan.');
            exit();

        } elseif ($action === 'update_user') {
            $uid = $_POST['user_id'] ?? '';
            $nama_u = trim($_POST['nama'] ?? '');
            $role  = $_POST['role'] ?? 'karyawan';
            $div_id = !empty($_POST['divisi_id']) ? $_POST['divisi_id'] : null;

            if (!empty(trim($_POST['password'] ?? ''))) {
                $pw = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET nama = ?, role = ?, divisi_id = ?, password = ? WHERE id = ?");
                $stmt->execute([$nama_u, $role, $div_id, $pw, $uid]);
            } else {
                $stmt = $db->prepare("UPDATE users SET nama = ?, role = ?, divisi_id = ? WHERE id = ?");
                $stmt->execute([$nama_u, $role, $div_id, $uid]);
            }

            append_log($user['id'], 'UPDATE_USER', $uid, "Memperbarui data pengguna ID: $uid");
            header('Location: kelola_pengguna.php?ok=Data+pengguna+berhasil+diperbarui.');
            exit();

        } elseif ($action === 'delete_user') {
            $uid = $_POST['user_id'] ?? '';
            if ($uid == $user['id']) {
                header('Location: kelola_pengguna.php?err=Tidak+dapat+menghapus+akun+sendiri.');
                exit();
            }
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            append_log($user['id'], 'DELETE_USER', $uid, "Menghapus pengguna ID: $uid");
            header('Location: kelola_pengguna.php?ok=Pengguna+berhasil+dihapus.');
            exit();
        }
    } catch (Exception $e) {
        header('Location: kelola_pengguna.php?err=' . urlencode($e->getMessage()));
        exit();
    }
}

// Ambil Data
$stmtU = $db->query("SELECT u.*, d.nama as divisi_nama FROM users u LEFT JOIN divisi d ON u.divisi_id = d.id ORDER BY u.id DESC");
$users = $stmtU->fetchAll();

$stmtD = $db->query("SELECT * FROM divisi ORDER BY nama ASC");
$divisions = $stmtD->fetchAll();

$role_counts = array_count_values(array_column($users, 'role'));
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

        <?php if (isset($_GET['ok'])): ?>
        <div class="alert-inline alert-inline--success" style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ <?= htmlspecialchars($_GET['ok']) ?>
        </div>
        <?php elseif (isset($_GET['err'])): ?>
        <div class="alert-inline alert-inline--error" style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#FFF5F5; border:1px solid #FECACA; color:#991B1B;">
            ❌ <?= htmlspecialchars($_GET['err']) ?>
        </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem; margin-bottom:1.5rem;">
            <?php
            $stats = [
                ['label' => 'Karyawan', 'count' => $role_counts['karyawan'] ?? 0, 'icon' => '👤', 'color' => '#1E3A5F'],
                ['label' => 'HRD',      'count' => $role_counts['hrd']      ?? 0, 'icon' => '🧑‍💼', 'color' => '#F59E0B'],
                ['label' => 'Admin',    'count' => $role_counts['admin']    ?? 0, 'icon' => '🛡️', 'color' => '#DC3545'],
            ];
            foreach ($stats as $s): ?>
            <div class="content-card" style="text-align:center; padding:1rem;">
                <div style="font-size:2rem;"><?= $s['icon'] ?></div>
                <div style="font-size:1.75rem; font-weight:900; color:<?= $s['color'] ?>;"><?= $s['count'] ?></div>
                <div style="font-size:0.85rem; color:var(--color-gray-500);"><?= $s['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary);">
                    Daftar Pengguna Sistem
                    <span style="font-size:0.85rem; font-weight:500; color:var(--color-gray-400); margin-left:0.5rem;">(<?= count($users) ?> total)</span>
                </h2>
                <button class="btn-add" onclick="openAddUser()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah User
                </button>
            </div>

            <div class="table-container">
                <table class="premium-table" id="table-users">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                            <td style="font-family:monospace; font-size:0.8rem; color:var(--color-gray-400);"><?= $u['id'] ?></td>
                            <td style="font-weight: 700;"><?= htmlspecialchars($u['nama']) ?></td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge-pill role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($u['divisi_nama'] ?? '-') ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit User"
                                        onclick='openEditUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <?php if ($u['id'] != $user['id']): ?>
                                    <form method="POST" style="display:inline;" class="form-delete">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="button" class="btn-icon btn-delete" onclick="confirmDelete(this, '<?= htmlspecialchars(addslashes($u['nama'])) ?>')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
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

<!-- Modal User -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="userModalTitle">Tambah Pengguna</h3>
            <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form method="POST" id="userForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" id="userAction" value="add_user">
            <input type="hidden" name="user_id" id="userEditId" value="">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="userNama" class="form-input" required>
            </div>
            <div class="form-group" id="emailGroup">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="userEmail" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" id="pwLabel">Password</label>
                <input type="text" name="password" id="userPassword" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" id="userRole" class="form-input">
                    <option value="karyawan">Karyawan</option>
                    <option value="hrd">HRD</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Divisi</label>
                <select name="divisi_id" id="userDivisi" class="form-input">
                    <option value="">- Tanpa Divisi -</option>
                    <?php foreach ($divisions as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary); color:#fff; width:100%; padding:0.75rem; border-radius:10px; font-weight:700; border:none;">Simpan</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openAddUser() {
        document.getElementById('userModalTitle').textContent = 'Tambah Pengguna';
        document.getElementById('userAction').value = 'add_user';
        document.getElementById('userForm').reset();
        document.getElementById('emailGroup').style.display = 'block';
        document.getElementById('userEmail').required = true;
        openModal('userModal');
    }

    function openEditUser(d) {
        document.getElementById('userModalTitle').textContent = 'Edit Pengguna';
        document.getElementById('userAction').value = 'update_user';
        document.getElementById('userEditId').value = d.id;
        document.getElementById('userNama').value = d.nama;
        document.getElementById('userRole').value = d.role;
        document.getElementById('userDivisi').value = d.divisi_id || '';
        document.getElementById('emailGroup').style.display = 'none';
        document.getElementById('userEmail').required = false;
        openModal('userModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        new simpleDatatables.DataTable("#table-users");
    });

    function confirmDelete(btn, name) {
        Swal.fire({
            title: 'Hapus User?',
            text: 'Akun ' + name + ' akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus'
        }).then((res) => { if(res.isConfirmed) btn.closest('form').submit(); });
    }
</script>
</body>
</html>
