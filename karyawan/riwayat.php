<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user     = $_SESSION['user'];
$user_id  = $user['id'] ?? 'U_DEMO_K';
$nama     = $user['nama'];
$active_menu = 'riwayat';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Ambil riwayat dari store (terbaru di depan)
$history = get_user_history($user_id);

// Fitur Pencarian
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $history = array_filter($history, function($h) use ($q) {
        $search = strtolower($q);
        return str_contains(strtolower($h['label'] ?? ''), $search) || 
               str_contains(strtolower($h['tanggal'] ?? ''), $search) ||
               str_contains(strtolower($h['id'] ?? ''), $search);
    });
}

// Siapkan data chart dari riwayat nyata
$chart_data       = [];
$chart_categories = [];

$skor_map = ['TINGGI' => 3, 'SEDANG' => 2, 'RENDAH' => 1, 'TIDAK ADA' => 0];
// Ambil maks 6 entri terbaru, balik urutan (terlama dulu) untuk chart
$chart_entries = array_slice(array_reverse($history), 0, 6);
foreach ($chart_entries as $h) {
    $chart_data[]       = $skor_map[$h['level']] ?? 0;
    $chart_categories[] = date('d M', strtotime(str_replace(' ', '-', $h['tanggal'])));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Deteksi – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main class="page-content">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h1 class="page-title">Riwayat Deteksi Burnout</h1>
                    <p style="color: var(--color-gray-500); font-size: 0.9rem;">Pantau tren kondisi kesehatan mental Anda dari waktu ke waktu.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <form method="GET" action="" style="display: flex; align-items: center; background: #fff; border: 1px solid var(--color-gray-200); border-radius: 10px; padding: 0.25rem 0.75rem; width: 300px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-gray-400)" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari diagnosa atau tanggal..." style="border: none; outline: none; padding: 0.5rem; width: 100%; font-size: 0.85rem;">
                        <?php if($q): ?>
                            <a href="riwayat.php" style="color: var(--color-gray-400);">&times;</a>
                        <?php endif; ?>
                    </form>
                    <a href="deteksi.php" class="btn-cta">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Mulai Deteksi Baru
                    </a>
                </div>
            </div>

        <?php if (empty($history)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state__icon">🧘‍♂️</div>
                <h2 class="empty-state__title">Belum Ada Riwayat Deteksi</h2>
                <p class="empty-state__desc">
                    Sepertinya Anda belum melakukan pengecekan kesehatan mental. Mulailah sekarang untuk memantau kondisi Anda.
                </p>
                <a href="deteksi.php" class="btn-cta empty-state__action">
                    Mulai Deteksi Sekarang
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Timeline Riwayat -->
                <div class="content-card">
                    <h2 class="card-title">Timeline Aktivitas</h2>
                    <div class="timeline">
                        <?php foreach ($history as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background: <?= $h['color'] ?>;"></div>
                            <div class="timeline-content">
                                <div>
                                    <div class="timeline-date"><?= htmlspecialchars($h['tanggal']) ?></div>
                                    <div class="timeline-level"><?= htmlspecialchars($h['label']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--color-gray-400); margin-top:2px;">
                                        Keyakinan: <?= $h['confidence'] ?>%
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-<?= strtolower($h['level']) ?>"><?= $h['level'] === 'TIDAK ADA' ? 'Tidak Burnout' : $h['level'] ?></span>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="laporan.php?id=<?= urlencode($h['id']) ?>" class="btn-report">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                            Laporan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Trend Chart -->
                <div class="content-card">
                    <h2 class="card-title">Tren Kondisi</h2>
                    <div class="chart-container">
                        <div id="trendChart"></div>
                    </div>

                    <?php
                    // Analisis tren otomatis
                    $analisis = '';
                    if (count($history) >= 2) {
                        $latest = $skor_map[$history[0]['level']] ?? 0;
                        $prev   = $skor_map[$history[1]['level']] ?? 0;
                        if ($latest > $prev)       $analisis = '📈 Tingkat burnout Anda <strong>meningkat</strong> sejak deteksi terakhir. Segera ambil langkah pencegahan.';
                        elseif ($latest < $prev)   $analisis = '📉 Tingkat burnout Anda <strong>menurun</strong>. Pertahankan kebiasaan baik Anda!';
                        else                       $analisis = '📊 Tingkat burnout Anda <strong>stabil</strong> dibanding deteksi sebelumnya.';
                    } else {
                        $analisis = '📊 Lakukan deteksi lebih dari satu kali untuk melihat analisis tren Anda.';
                    }
                    ?>
                    <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--color-gray-500); line-height: 1.5;">
                        <p>💡 <strong>Analisis:</strong> <?= $analisis ?></p>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var chartData       = <?= json_encode($chart_data) ?>;
                        var chartCategories = <?= json_encode($chart_categories) ?>;

                        if (chartData.length === 0) return;

                        var options = {
                            series: [{ name: "Skor Burnout", data: chartData }],
                            chart: {
                                height: 250, type: 'area',
                                zoom: { enabled: false }, toolbar: { show: false }, fontFamily: 'inherit'
                            },
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 3, colors: ['var(--color-primary)'] },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100],
                                    colorStops: [
                                        { offset: 0, color: 'var(--color-primary)', opacity: 0.4 },
                                        { offset: 100, color: 'var(--color-primary)', opacity: 0.05 }
                                    ]
                                }
                            },
                            markers: {
                                size: 5, colors: ['#fff'],
                                strokeColors: 'var(--color-primary)', strokeWidth: 2, hover: { size: 7 }
                            },
                            xaxis: {
                                categories: chartCategories,
                                labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } },
                                axisBorder: { show: false }, axisTicks: { show: false }
                            },
                            yaxis: {
                                min: 0, max: 4, tickAmount: 4,
                                labels: {
                                    formatter: function (val) {
                                        if (val === 1) return 'Rendah';
                                        if (val === 2) return 'Sedang';
                                        if (val === 3) return 'Tinggi';
                                        return '';
                                    },
                                    style: { colors: 'var(--color-gray-500)', fontSize: '12px' }
                                }
                            },
                            grid: {
                                borderColor: 'var(--color-gray-100)', strokeDashArray: 4,
                                yaxis: { lines: { show: true } }
                            },
                            tooltip: {
                                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
                                y: {
                                    formatter: function (val) {
                                        if (val === 1) return 'Rendah';
                                        if (val === 2) return 'Sedang';
                                        if (val === 3) return 'Tinggi';
                                        return 'Tidak Burnout';
                                    }
                                }
                            }
                        };

                        var chart = new ApexCharts(document.querySelector("#trendChart"), options);
                        chart.render();
                    });
                    </script>
                </div>
            </div>

            <!-- Tabel Ringkasan -->
            <div class="content-card" style="margin-top: 1.5rem;">
                <h2 class="card-title">Ringkasan Semua Deteksi</h2>
                <div class="table-container">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>ID Laporan</th>
                                <th>Tanggal</th>
                                <th>Hasil Diagnosis</th>
                                <th>Tingkat Keyakinan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td style="font-family: monospace; font-size: 0.8rem; color: var(--color-gray-500);">
                                    <?= htmlspecialchars($h['id'] ?? '-') ?>
                                </td>
                                <td><?= htmlspecialchars($h['tanggal']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($h['level'] === 'TIDAK ADA' ? 'rendah' : $h['level']) ?>">
                                        <?= htmlspecialchars($h['label']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <div style="height:6px; width:80px; background:var(--color-gray-100); border-radius:99px; overflow:hidden;">
                                            <div style="height:100%; width:<?= $h['confidence'] ?>%; background:<?= $h['color'] ?>; border-radius:99px;"></div>
                                        </div>
                                        <span style="font-size:0.8rem; font-weight:700; color:<?= $h['color'] ?>;"><?= $h['confidence'] ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="laporan.php?id=<?= urlencode($h['id']) ?>" class="btn-detail" style="font-size:0.8rem; padding:0.35rem 0.85rem;">
                                        Lihat Laporan
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        </main>
    </div>

</body>
</html>
