<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();
require_once '../includes/security.php';

$user = $_SESSION['user'];
$active_menu = 'profil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF Validation Failed!");
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $new_nama = trim($_POST['nama'] ?? '');
        
        if (empty($new_nama)) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Nama tidak boleh kosong.'];
        } else {
            foreach ($_SESSION['bx_store']['users'] as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['nama'] = $new_nama;
                    $_SESSION['user'] = $u;
                    break;
                }
            }
            unset($u);
            append_log($new_nama, 'UPDATE_PROFILE', $user['id'], "Memperbarui profil mandiri (Admin).");
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Profil berhasil diperbarui.'];
        }
        header('Location: profil.php');
        exit();

    } elseif ($action === 'update_password') {
        $old_pw = $_POST['current_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $cfm_pw = $_POST['confirm_password'] ?? '';

        if (!password_verify($old_pw, $user['password'])) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Password saat ini salah.'];
        } elseif ($new_pw !== $cfm_pw) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Konfirmasi password tidak cocok.'];
        } elseif (strlen($new_pw) < 8) {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Password baru minimal 8 karakter.'];
        } else {
            update_user_password($user['email'], $new_pw);
            foreach ($_SESSION['bx_store']['users'] as $u) {
                if ($u['id'] === $user['id']) {
                    $_SESSION['user'] = $u;
                    break;
                }
            }
            append_log($user['nama'], 'UPDATE_PASSWORD', $user['id'], "Mengubah password akun (Admin).");
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Password berhasil diubah.'];
        }
        header('Location: profil.php');
        exit();
    }
}

$page_title  = "Profil Admin";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Admin – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>
    <main class="page-content">
        <?php include '../includes/profil_form.php'; ?>
    </main>
</div>
</body>
</html>
