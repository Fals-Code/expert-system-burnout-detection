<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'riwayat';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Ambil hasil deteksi terbaru dari session jika ada
$hasil_ada = isset($_SESSION['hasil_deteksi']);
$history = [
    ['tanggal' => '15 Mar 2025', 'tingkat' => 'Sedang', 'skor' => 2, 'color' => '#FFC107'],
    ['tanggal' => '10 Feb 2025', 'tingkat' => 'Sedang', 'skor' => 2, 'color' => '#FFC107'],
    ['tanggal' => '05 Jan 2025', 'tingkat' => 'Rendah', 'skor' => 1, 'color' => '#28A745'],
    ['tanggal' => '20 Des 2024', 'tingkat' => 'Rendah', 'skor' => 1, 'color' => '#28A745'],
];

if ($hasil_ada) {
    array_unshift($history, [
        'tanggal' => $_SESSION['hasil_deteksi']['tanggal'],
        'tingkat' => $_SESSION['hasil_deteksi']['level'],
        'skor' => ($_SESSION['hasil_deteksi']['level'] === 'TINGGI' ? 3 : ($_SESSION['hasil_deteksi']['level'] === 'SEDANG' ? 2 : 1)),
        'color' => $_SESSION['hasil_deteksi']['color']
    ]);
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
                <a href="deteksi.php" class="btn-cta">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Mulai Deteksi Baru
                </a>
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
                                    <div class="timeline-date"><?= $h['tanggal'] ?></div>
                                    <div class="timeline-level"><?= $h['tingkat'] ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-<?= strtolower($h['tingkat']) ?>">Burnout <?= $h['tingkat'] ?></span>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="laporan.php?tgl=<?= urlencode($h['tanggal']) ?>" class="btn-report">
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
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var options = {
                            series: [{
                                name: "Skor Burnout",
                                data: [1, 1, 2, 2, 3] // Sesuaikan data dengan dummy (Rendah=1, Sedang=2, Tinggi=3)
                            }],
                            chart: {
                                height: 250,
                                type: 'area',
                                zoom: { enabled: false },
                                toolbar: { show: false },
                                fontFamily: 'inherit'
                            },
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 3, colors: ['var(--color-primary)'] },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.4,
                                    opacityTo: 0.05,
                                    stops: [0, 90, 100],
                                    colorStops: [{ offset: 0, color: 'var(--color-primary)', opacity: 0.4 }, { offset: 100, color: 'var(--color-primary)', opacity: 0.05 }]
                                }
                            },
                            markers: {
                                size: 5,
                                colors: ['#fff'],
                                strokeColors: 'var(--color-primary)',
                                strokeWidth: 2,
                                hover: { size: 7 }
                            },
                            xaxis: {
                                categories: ['Des', 'Jan', 'Feb', 'Mar', 'Mei'],
                                labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                min: 0, max: 4,
                                tickAmount: 4,
                                labels: {
                                    formatter: function (val) {
                                        if(val === 1) return 'Rendah';
                                        if(val === 2) return 'Sedang';
                                        if(val === 3) return 'Tinggi';
                                        return '';
                                    },
                                    style: { colors: 'var(--color-gray-500)', fontSize: '12px' }
                                }
                            },
                            grid: {
                                borderColor: 'var(--color-gray-100)',
                                strokeDashArray: 4,
                                yaxis: { lines: { show: true } }
                            },
                            tooltip: {
                                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
                                y: { formatter: function (val) { return val === 1 ? 'Rendah' : (val === 2 ? 'Sedang' : 'Tinggi'); } }
                            }
                        };

                        var chart = new ApexCharts(document.querySelector("#trendChart"), options);
                        chart.render();
                    });
                    </script>
                    </div>
                    <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--color-gray-500); line-height: 1.5;">
                        <p>💡 <strong>Analisis:</strong> Tingkat burnout Anda menunjukkan tren meningkat sejak Februari. Disarankan untuk mengambil istirahat terencana.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        </main>
    </div>

</body>
</html>
