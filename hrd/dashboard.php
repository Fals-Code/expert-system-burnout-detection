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
$active_menu = 'dashboard';

// Ambil statistik dari store
$karyawan_list = get_all_karyawan();
$total_karyawan = count($karyawan_list);

$stats = ['TINGGI' => 0, 'SEDANG' => 0, 'RENDAH' => 0, 'TIDAK ADA' => 0, 'Belum Deteksi' => 0];
$urgent_cases = [];

foreach ($karyawan_list as $k) {
    $lvl = $k['last_level'];
    if (isset($stats[$lvl])) $stats[$lvl]++;
    else $stats['Belum Deteksi']++;

    if ($lvl === 'TINGGI') {
        $urgent_cases[] = $k;
    }
}

// Ambil notifikasi unread
$unread_notif = count(array_filter($_SESSION['bx_store']['hrd_alerts'] ?? [], fn($a) => !($a['read'] ?? false)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>HRD Dashboard – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h1 class="page-title">Ringkasan Kesehatan Karyawan</h1>
            <div style="color: var(--color-gray-500); font-size: 0.9rem;">
                Terakhir update: <strong><?= date('d M Y, H:i') ?></strong>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem;">
            <div class="content-card stat-card" style="border-bottom: 4px solid var(--color-primary);">
                <div class="stat-info">
                    <div class="stat-value"><?= $total_karyawan ?></div>
                    <div class="stat-label">Total Karyawan</div>
                </div>
            </div>
            <div class="content-card stat-card" style="border-bottom: 4px solid #DC3545;">
                <div class="stat-info">
                    <div class="stat-value" style="color: #DC3545;"><?= $stats['TINGGI'] ?></div>
                    <div class="stat-label">Burnout Tinggi</div>
                </div>
            </div>
            <div class="content-card stat-card" style="border-bottom: 4px solid #F59E0B;">
                <div class="stat-info">
                    <div class="stat-value" style="color: #D97706;"><?= $stats['SEDANG'] ?></div>
                    <div class="stat-label">Burnout Sedang</div>
                </div>
            </div>
            <div class="content-card stat-card" style="border-bottom: 4px solid #10B981;">
                <div class="stat-info">
                    <div class="stat-value" style="color: #065F46;"><?= $unread_notif ?></div>
                    <div class="stat-label">Notifikasi Baru</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; margin-top: 1.5rem;">
            <!-- Distribusi Chart -->
            <div class="content-card">
                <h2 class="card-title">Distribusi Burnout</h2>
                <div id="burnoutDistribution" style="min-height: 250px;"></div>
            </div>

            <!-- Kasus Mendesak -->
            <div class="content-card">
                <div class="card-header" style="padding: 0; margin-bottom: 1rem;">
                    <h2 class="card-title">🚨 Kasus Mendesak</h2>
                    <a href="notifikasi.php" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700;">Lihat Semua</a>
                </div>
                <div class="urgent-list">
                    <?php if (empty($urgent_cases)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--color-gray-400);">
                        <div style="font-size: 2rem;">✅</div>
                        <p>Tidak ada kasus burnout tinggi saat ini.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($urgent_cases, 0, 4) as $u): ?>
                    <div class="urgent-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--color-gray-100);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="avatar-sm" style="width: 32px; height: 32px; border-radius: 50%; background: #FFF5F5; color: #DC3545; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;">
                                <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($u['nama']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-400);"><?= htmlspecialchars($u['divisi']) ?></div>
                            </div>
                        </div>
                        <a href="detail_karyawan.php?id=<?= urlencode($u['id']) ?>" class="btn-detail" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">Periksa</a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        series: [<?= $stats['TINGGI'] ?>, <?= $stats['SEDANG'] ?>, <?= $stats['RENDAH'] ?>, <?= $stats['TIDAK ADA'] + $stats['Belum Deteksi'] ?>],
        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
        labels: ['Tinggi', 'Sedang', 'Rendah', 'Normal/Belum'],
        colors: ['#DC3545', '#F59E0B', '#3B82F6', '#10B981'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: { show: true, label: 'Karyawan', fontSize: '14px', fontWeight: 600 }
                    }
                }
            }
        }
    };
    var chart = new ApexCharts(document.querySelector("#burnoutDistribution"), options);
    chart.render();
});
</script>
</body>
</html>
