<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'laporan';

// Mock Data Statistik Global
$stats = [
    'total_asesmen' => 458,
    'total_pengguna' => 124,
    'kasus_tinggi' => 32,
    'kasus_sedang' => 156,
    'kasus_rendah' => 270,
];

$distribusi_divisi = [
    ['divisi' => 'IT', 'total' => 120, 'tinggi' => 15],
    ['divisi' => 'Marketing', 'total' => 85, 'tinggi' => 8],
    ['divisi' => 'Finance', 'total' => 60, 'tinggi' => 3],
    ['divisi' => 'HR', 'total' => 45, 'tinggi' => 2],
    ['divisi' => 'Operational', 'total' => 148, 'tinggi' => 4],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Global – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Statistik Global";
        ob_start(); ?>
        <button class="btn-export" onclick="window.print()" style="background:var(--color-primary); color:#fff; border:none; padding:0.6rem 1.25rem; border-radius:10px; font-weight:700; display:flex; align-items:center; gap:0.6rem; cursor:pointer; font-size:0.875rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Export Laporan</span>
        </button>
        <?php 
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="grid-stats">
            <div class="mini-card">
                <span class="mini-val"><?= $stats['kasus_tinggi'] ?></span>
                <span class="mini-lbl" style="color: var(--color-error);">Kasus Tinggi</span>
            </div>
            <div class="mini-card">
                <span class="mini-val"><?= $stats['kasus_sedang'] ?></span>
                <span class="mini-lbl" style="color: var(--color-warning);">Kasus Sedang</span>
            </div>
            <div class="mini-card">
                <span class="mini-val"><?= $stats['kasus_rendah'] ?></span>
                <span class="mini-lbl" style="color: var(--color-success);">Kasus Rendah</span>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Distribusi Burnout Tinggi Per Divisi</h3>
            <?php foreach ($distribusi_divisi as $d): 
                $percent = ($d['tinggi'] / $stats['kasus_tinggi']) * 100;
            ?>
            <div class="bar-container">
                <div class="bar-header">
                    <span><?= $d['divisi'] ?></span>
                    <span><?= $d['tinggi'] ?> Kasus</span>
                </div>
                <div class="bar-outer">
                    <div class="bar-inner" style="width: <?= $percent ?>%; background: var(--color-primary);"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3 class="card-title">Ringkasan Sistem</h3>
            <div style="font-size: 0.95rem; color: var(--color-gray-600); line-height: 1.6;">
                <p>Sistem saat ini melayani <strong><?= $stats['total_pengguna'] ?></strong> pengguna terdaftar dengan total <strong><?= $stats['total_asesmen'] ?></strong> deteksi yang telah dilakukan sejak sistem diluncurkan. Rata-rata tingkat burnout organisasi berada pada level <strong>Sedang-Rendah</strong>.</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
