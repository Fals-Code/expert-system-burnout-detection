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

// ── Handler POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $new_id = 'U' . str_pad(count($_SESSION['bx_store']['users']) + 1, 3, '0', STR_PAD_LEFT);
        $email  = strtolower(trim($_POST['email'] ?? ''));

        // Validasi duplikat email
        $exists = false;
        foreach ($_SESSION['bx_store']['users'] as $u) {
            if (strtolower($u['email']) === $email) { $exists = true; break; }
        }

        if (!$exists && !empty($email)) {
            $_SESSION['bx_store']['users'][] = [
                'id'       => $new_id,
                'nama'     => trim($_POST['nama'] ?? ''),
                'email'    => $email,
                'password' => trim($_POST['password'] ?? 'password123'),
                'role'     => $_POST['role'] ?? 'karyawan',
                'divisi'   => trim($_POST['divisi'] ?? ''),
                'posisi'   => trim($_POST['posisi'] ?? ''),
            ];
            append_log($nama, 'CREATE_USER', $new_id, "Menambahkan pengguna baru: \"" . trim($_POST['nama']) . "\" (" . ($_POST['role'] ?? '') . ")");
            header('Location: kelola_pengguna.php?ok=Pengguna+berhasil+ditambahkan.');
        } else {
            header('Location: kelola_pengguna.php?err=Email+sudah+terdaftar+atau+kosong.');
        }
        exit();

    } elseif ($action === 'update_user') {
        $uid = $_POST['user_id'] ?? '';
        foreach ($_SESSION['bx_store']['users'] as &$u) {
            if ($u['id'] === $uid) {
                $u['nama']   = trim($_POST['nama'] ?? $u['nama']);
                $u['role']   = $_POST['role']   ?? $u['role'];
                $u['divisi'] = trim($_POST['divisi'] ?? $u['divisi']);
                $u['posisi'] = trim($_POST['posisi'] ?? $u['posisi']);
                // Hanya update password jika diisi
                if (!empty(trim($_POST['password'] ?? ''))) {
                    $u['password'] = trim($_POST['password']);
                }
                break;
            }
        }
        unset($u);
        append_log($nama, 'UPDATE_USER', $uid, "Memperbarui data pengguna ID: {$uid}");
        header('Location: kelola_pengguna.php?ok=Data+pengguna+berhasil+diperbarui.');
        exit();

    } elseif ($action === 'delete_user') {
        $uid = $_POST['user_id'] ?? '';
        // Tidak boleh hapus diri sendiri
        if ($uid === ($user['id'] ?? '')) {
            header('Location: kelola_pengguna.php?err=Tidak+dapat+menghapus+akun+sendiri.');
            exit();
        }
        $deleted_name = '';
        foreach ($_SESSION['bx_store']['users'] as $u) {
            if ($u['id'] === $uid) { $deleted_name = $u['nama']; break; }
        }
        $_SESSION['bx_store']['users'] = array_values(
            array_filter($_SESSION['bx_store']['users'], fn($u) => $u['id'] !== $uid)
        );
        append_log($nama, 'DELETE_USER', $uid, "Menghapus pengguna: \"{$deleted_name}\"");
        header('Location: kelola_pengguna.php?ok=Pengguna+berhasil+dihapus.');
        exit();
    }
}

$users = $_SESSION['bx_store']['users'];
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
        <div style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#F0FFF4; border:1px solid #BBF7D0; color:#065F46;">
            ✅ <?= htmlspecialchars($_GET['ok']) ?>
        </div>
        <?php elseif (isset($_GET['err'])): ?>
        <div style="margin-bottom:1rem; padding:0.75rem 1.25rem; border-radius:10px; background:#FFF5F5; border:1px solid #FECACA; color:#991B1B;">
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
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Divisi / Posisi</th>
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
                            <td style="font-size:0.85rem;">
                                <?= htmlspecialchars($u['divisi']) ?>
                                <?= !empty($u['posisi']) ? '<br><span style="color:var(--color-gray-400);font-size:0.75rem;">' . htmlspecialchars($u['posisi']) . '</span>' : '' ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon btn-edit" title="Edit User"
                                        onclick='openEditUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <?php if ($u['id'] !== ($user['id'] ?? '')): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pengguna <?= htmlspecialchars(addslashes($u['nama'])) ?>?')">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Hapus User">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span title="Tidak dapat menghapus akun sendiri" style="opacity:0.3; cursor:not-allowed; padding:0.4rem;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </span>
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

<!-- ─── Modal Add/Edit User ─── -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="userModalTitle">Tambah Pengguna Baru</h3>
            <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form method="POST" id="userForm">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action"  id="userAction"  value="add_user">
            <input type="hidden" name="user_id" id="userEditId"  value="">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" name="nama" id="userNama" class="form-input" placeholder="Nama lengkap..." required>
            </div>
            <div class="form-group" id="emailGroup">
                <label class="form-label">Email <span style="color:red">*</span></label>
                <input type="email" name="email" id="userEmail" class="form-input" placeholder="email@domain.com">
            </div>
            <div class="form-group">
                <label class="form-label" id="pwLabel">Password <span style="color:red">*</span></label>
                <input type="text" name="password" id="userPassword" class="form-input" placeholder="Min. 6 karakter">
                <small id="pwHint" style="color:var(--color-gray-400);">Kosongkan untuk tidak mengubah password.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" id="userRole" class="form-input">
                    <option value="karyawan">Karyawan</option>
                    <option value="hrd">HRD</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Divisi</label>
                    <input type="text" name="divisi" id="userDivisi" class="form-input" placeholder="IT, Marketing...">
                </div>
                <div class="form-group">
                    <label class="form-label">Posisi</label>
                    <input type="text" name="posisi" id="userPosisi" class="form-input" placeholder="Jabatan...">
                </div>
            </div>
            <button type="submit" class="btn-submit" style="background:var(--color-primary);color:#fff;width:100%;padding:0.75rem;border-radius:10px;font-weight:700;border:none;cursor:pointer;">
                Simpan Pengguna
            </button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openAddUser() {
        document.getElementById('userModalTitle').textContent = 'Tambah Pengguna Baru';
        document.getElementById('userAction').value   = 'add_user';
        document.getElementById('userEditId').value   = '';
        document.getElementById('userNama').value     = '';
        document.getElementById('userEmail').value    = '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userRole').value     = 'karyawan';
        document.getElementById('userDivisi').value   = '';
        document.getElementById('userPosisi').value   = '';
        document.getElementById('emailGroup').style.display = '';
        document.getElementById('pwLabel').innerHTML  = 'Password <span style="color:red">*</span>';
        document.getElementById('pwHint').style.display = 'none';
        document.getElementById('userEmail').required = true;
        openModal('userModal');
    }

    function openEditUser(data) {
        document.getElementById('userModalTitle').textContent = 'Edit Pengguna: ' + data.nama;
        document.getElementById('userAction').value   = 'update_user';
        document.getElementById('userEditId').value   = data.id;
        document.getElementById('userNama').value     = data.nama;
        document.getElementById('userEmail').value    = data.email;
        document.getElementById('userPassword').value = '';
        document.getElementById('userRole').value     = data.role;
        document.getElementById('userDivisi').value   = data.divisi || '';
        document.getElementById('userPosisi').value   = data.posisi || '';
        document.getElementById('emailGroup').style.display = 'none'; // Email tidak bisa diubah
        document.getElementById('pwLabel').innerHTML  = 'Password Baru';
        document.getElementById('pwHint').style.display = '';
        document.getElementById('userEmail').required = false;
        openModal('userModal');
    }

    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) e.target.style.display = 'none';
    });
</script>
</body>
</html>
