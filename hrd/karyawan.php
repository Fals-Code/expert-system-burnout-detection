<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'karyawan';

// Mock Data Karyawan Full
$karyawan_list = [
    ['id' => 101, 'nama' => 'Andi Wijaya', 'divisi' => 'IT', 'tanggal' => '10 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 102, 'nama' => 'Maria Ulfa', 'divisi' => 'Marketing', 'tanggal' => '09 Mei 2026', 'tingkat' => 'Sedang'],
    ['id' => 103, 'nama' => 'Bambang', 'divisi' => 'Finance', 'tanggal' => '08 Mei 2026', 'tingkat' => 'Rendah'],
    ['id' => 104, 'nama' => 'Citra', 'divisi' => 'HR', 'tanggal' => '07 Mei 2026', 'tingkat' => 'Sedang'],
    ['id' => 105, 'nama' => 'Dedi', 'divisi' => 'IT', 'tanggal' => '06 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 106, 'nama' => 'Eka', 'divisi' => 'Operasional', 'tanggal' => '05 Mei 2026', 'tingkat' => 'Rendah'],
    ['id' => 107, 'nama' => 'Farhan', 'divisi' => 'Marketing', 'tanggal' => '04 Mei 2026', 'tingkat' => 'Tinggi'],
    ['id' => 108, 'nama' => 'Gita', 'divisi' => 'Finance', 'tanggal' => '03 Mei 2026', 'tingkat' => 'Sedang'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 2rem; flex: 1; }

        .content-card { background: #fff; border-radius: 20px; padding: 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); }

        .search-box { display: flex; gap: 0.75rem; background: var(--color-gray-50); padding: 0.5rem 1rem; border-radius: 12px; border: 1.5px solid var(--color-gray-200); width: 300px; }
        .search-input { background: none; border: none; font-family: inherit; font-size: 0.9rem; width: 100%; outline: none; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.25rem 1rem; font-size: 0.8rem; font-weight: 700; color: var(--color-gray-400); border-bottom: 2px solid var(--color-gray-50); text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 1.25rem 1rem; font-size: 0.95rem; border-bottom: 1px solid var(--color-gray-50); color: var(--color-gray-700); }
        
        .user-info { display: flex; align-items: center; gap: 0.75rem; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-50); color: var(--color-primary); font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        
        .badge { padding: 0.4rem 0.8rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; }
        .badge--tinggi { background: #FFF5F5; color: #DC3545; }
        .badge--sedang { background: #FFFBEB; color: #D97706; }
        .badge--rendah { background: #F0FFF4; color: #10B981; }

        .btn-detail { color: var(--color-primary); font-weight: 700; text-decoration: underline; font-size: 0.85rem; }
    </style>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Data Karyawan</div>
        <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
    </header>

    <main class="page-content">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Daftar Seluruh Karyawan</h2>
                <div class="search-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-400);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" class="search-input" placeholder="Cari nama atau divisi...">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Divisi</th>
                            <th>Deteksi Terakhir</th>
                            <th>Tingkat Burnout</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($karyawan_list as $k): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?= substr($k['nama'], 0, 1) ?></div>
                                    <span style="font-weight: 600;"><?= $k['nama'] ?></span>
                                </div>
                            </td>
                            <td><?= $k['divisi'] ?></td>
                            <td style="color: var(--color-gray-500);"><?= $k['tanggal'] ?></td>
                            <td>
                                <span class="badge badge--<?= strtolower($k['tingkat']) ?>">
                                    <?= $k['tingkat'] ?>
                                </span>
                            </td>
                            <td><a href="detail_karyawan.php?id=<?= $k['id'] ?>" class="btn-detail">Lihat Detail</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
