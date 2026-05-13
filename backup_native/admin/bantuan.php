<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user     = $_SESSION['user'];
$nama     = $user['nama'];
$active_menu = 'bantuan';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Bantuan Admin – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 class="page-title" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Pusat Bantuan Admin</h1>
                <p style="color: var(--color-gray-500); font-size: 1.1rem;">Panduan konfigurasi sistem dan manajemen basis pengetahuan.</p>
            </div>

            <div class="content-card">
                <h2 class="card-title">⚙️ Manajemen Basis Pengetahuan</h2>
                <div style="line-height: 1.8; color: var(--color-gray-600);">
                    <p>Sebagai admin, Anda memiliki kendali penuh atas "otak" sistem ini. Beberapa poin penting:</p>
                    <ul>
                        <li><strong>Gejala:</strong> Pastikan setiap gejala memiliki bobot antara 0.1 hingga 1.0.</li>
                        <li><strong>Aturan:</strong> Gunakan format kode gejala yang benar (Gxxx) saat menyusun aturan diagnosis.</li>
                        <li><strong>Certainty Factor Pakar:</strong> Nilai ini menentukan seberapa kuat sebuah aturan mendukung diagnosis tertentu.</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
