<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
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
    <title>Bantuan & FAQ HRD – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 class="page-title" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Pusat Bantuan HRD</h1>
                <p style="color: var(--color-gray-500); font-size: 1.1rem;">Panduan monitoring dan manajemen kesehatan mental organisasi.</p>
            </div>

            <!-- FAQ Section -->
            <h2 style="font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem;">Pertanyaan Umum HRD (FAQ)</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <details class="content-card" style="padding: 0;">
                    <summary style="padding: 1.25rem; font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        Bagaimana cara membaca skor Certainty Factor?
                        <span style="font-size: 1.2rem;">+</span>
                    </summary>
                    <div style="padding: 0 1.25rem 1.25rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6;">
                        Certainty Factor (CF) menunjukkan tingkat keyakinan sistem. Nilai di atas 0.8 (80%) menandakan indikasi yang sangat kuat. Sebagai HRD, fokuslah pada karyawan dengan CF tinggi karena mereka membutuhkan intervensi paling segera.
                    </div>
                </details>

                <details class="content-card" style="padding: 0;">
                    <summary style="padding: 1.25rem; font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        Kapan notifikasi "Kritis" dikirimkan?
                        <span style="font-size: 1.2rem;">+</span>
                    </summary>
                    <div style="padding: 0 1.25rem 1.25rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6;">
                        Notifikasi kritis otomatis dikirimkan ke dashboard Anda setiap kali ada karyawan yang menyelesaikan diagnosis dengan hasil level <strong>TINGGI</strong>. Anda disarankan untuk segera melakukan tindak lanjut personal.
                    </div>
                </details>
            </div>
        </div>
    </main>
</div>

<style>
details summary::-webkit-details-marker { display: none; }
details[open] summary span { transform: rotate(45deg); }
summary span { transition: transform 0.3s ease; }
</style>

</body>
</html>
