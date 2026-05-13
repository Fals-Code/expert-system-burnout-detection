<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user        = $_SESSION['user'];
$nama        = $user['nama'];
$initials    = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'karyawan';

// Ambil semua karyawan beserta status deteksi terakhir dari store
$karyawan_list = get_all_karyawan();

// Hitung statistik
$stats = ['TINGGI' => 0, 'SEDANG' => 0, 'RENDAH' => 0, 'TIDAK ADA' => 0, 'Belum Deteksi' => 0];
foreach ($karyawan_list as $k) {
    $lvl = $k['last_level'];
    if (isset($stats[$lvl])) $stats[$lvl]++;
    else $stats['Belum Deteksi']++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Karyawan – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php
        $page_title = "Data Karyawan";
        include '../includes/topbar.php';
    ?>

    <main class="page-content">

        <!-- Stat Cards -->
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; margin-bottom:1.5rem;">
            <?php
            $stat_items = [
                ['label' => 'Burnout Tinggi',    'count' => $stats['TINGGI'],        'color' => '#DC3545', 'icon' => '🔴'],
                ['label' => 'Burnout Sedang',    'count' => $stats['SEDANG'],        'color' => '#F59E0B', 'icon' => '🟡'],
                ['label' => 'Burnout Rendah',    'count' => $stats['RENDAH'],        'color' => '#3B82F6', 'icon' => '🔵'],
                ['label' => 'Belum / Tidak Burnout', 'count' => $stats['TIDAK ADA'] + $stats['Belum Deteksi'], 'color' => '#10B981', 'icon' => '🟢'],
            ];
            foreach ($stat_items as $s): ?>
            <div class="content-card" style="text-align:center; padding:1rem 0.75rem;">
                <div style="font-size:1.75rem;"><?= $s['icon'] ?></div>
                <div style="font-size:1.75rem; font-weight:900; color:<?= $s['color'] ?>;"><?= $s['count'] ?></div>
                <div style="font-size:0.75rem; color:var(--color-gray-500); line-height:1.4;"><?= $s['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="content-card">
            <div class="card-header" style="margin-bottom: 0;">
                <h2 class="card-title">Daftar Seluruh Karyawan</h2>
            </div>

            <div class="table-responsive">
                <table class="data-table" id="karyawanTable">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Divisi / Posisi</th>
                            <th>Deteksi Terakhir</th>
                            <th>Tingkat Burnout</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($karyawan_list)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:3rem; color:var(--color-gray-400);">
                                Belum ada karyawan terdaftar di sistem.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($karyawan_list as $k):
                            $badge_level = $k['last_level'];
                            $badge_class = strtolower(str_replace(' ', '-', $badge_level));
                            if ($badge_level === 'Belum Deteksi') $badge_class = 'default';
                        ?>
                        <tr>
                            <td data-label="Karyawan">
                                <div class="user-info" style="display:flex; align-items:center; gap:0.75rem;">
                                    <div class="user-avatar" style="width:36px; height:36px; border-radius:50%; background:var(--color-primary-50); color:var(--color-primary); font-size:0.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <?= strtoupper(substr($k['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($k['nama']) ?></div>
                                        <div style="font-size:0.75rem; color:var(--color-gray-400);"><?= htmlspecialchars($k['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Divisi">
                                <div style="font-weight:600;"><?= htmlspecialchars($k['divisi']) ?></div>
                                <div style="font-size:0.75rem; color:var(--color-gray-400);"><?= htmlspecialchars($k['posisi']) ?></div>
                            </td>
                            <td data-label="Deteksi Terakhir" style="color: var(--color-gray-500); font-size:0.85rem;">
                                <?= htmlspecialchars($k['last_deteksi']) ?>
                            </td>
                            <td data-label="Tingkat Burnout">
                                <?php if ($k['last_level'] === 'Belum Deteksi'): ?>
                                <span style="font-size:0.8rem; color:var(--color-gray-400); font-style:italic;">Belum Deteksi</span>
                                <?php else: ?>
                                <span class="badge badge-<?= strtolower($k['last_level']) ?>" style="background:<?= $k['last_color'] ?>20; color:<?= $k['last_color'] ?>; border:1px solid <?= $k['last_color'] ?>40;">
                                    <?= htmlspecialchars($k['last_level']) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Aksi">
                                <a href="detail_karyawan.php?id=<?= urlencode($k['id']) ?>" class="btn-detail">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('karyawanTable')) {
        new simpleDatatables.DataTable("#karyawanTable", {
            searchable: true,
            fixedHeight: false,
            perPage: 10,
            labels: {
                placeholder: "Cari karyawan...",
                perPage: "data per halaman",
                noRows: "Tidak ada data karyawan ditemukan",
                info: "Menampilkan {start} - {end} dari {rows} karyawan",
            }
        });
    }
});
</script>
</body>
</html>
