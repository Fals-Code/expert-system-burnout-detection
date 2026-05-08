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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Global – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 2rem; flex: 1; }

        .card { background: #fff; border-radius: 20px; padding: 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .card-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; }

        .grid-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .mini-card { background: var(--color-gray-50); padding: 1.5rem; border-radius: 16px; text-align: center; }
        .mini-val { display: block; font-size: 1.5rem; font-weight: 800; color: var(--color-primary); }
        .mini-lbl { font-size: 0.75rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; }

        .bar-container { margin-bottom: 1.5rem; }
        .bar-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; }
        .bar-outer { background: var(--color-gray-100); height: 10px; border-radius: 99px; overflow: hidden; }
        .bar-inner { height: 100%; border-radius: 99px; }

        .btn-export { background: var(--color-primary); color: #fff; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
    </style>
</head>
<body>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Laporan Statistik Global</div>
        <button class="btn-export" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Export Laporan
        </button>
    </header>

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
