<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'dashboard';
$nama_hrd = $nama; // Keeping this for backward compatibility in case it's used elsewhere

// Mock Data Karyawan (8 orang)
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

// Stats Chart Mock (Tinggi, Sedang, Rendah)
$chart_data = [
    'IT' => [2, 0, 0],
    'Marketing' => [1, 1, 0],
    'Finance' => [0, 1, 1],
    'HR' => [0, 1, 0],
    'Operasional' => [0, 0, 1]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard HRD – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main class="page-content">
            <div class="dashboard-container">
        
        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-blue">👥</div>
                <div class="stat-info">
                    <span class="stat-value">125</span>
                    <span class="stat-label">Total Karyawan</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red">🔥</div>
                <div class="stat-info">
                    <span class="stat-value">12</span>
                    <span class="stat-label">Burnout Tinggi</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange">⚠️</div>
                <div class="stat-info">
                    <span class="stat-value">34</span>
                    <span class="stat-label">Burnout Sedang</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green">✅</div>
                <div class="stat-info">
                    <span class="stat-value">79</span>
                    <span class="stat-label">Burnout Rendah</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="card-title" style="margin-bottom: 1.5rem;">Tren Burnout Bulanan</h3>
                <div id="trendChart"></div>
            </div>
            <div class="chart-card">
                <h3 class="card-title" style="margin-bottom: 1.5rem;">Distribusi per Divisi</h3>
                <div id="divisiChart"></div>
            </div>
        </div>

        <div class="main-grid" style="margin-top: 1.5rem;">
            
            <!-- Daftar Karyawan -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Monitoring Karyawan</h2>
                    <div class="segmented-tabs">
                        <button class="tab-item active" onclick="filterTable('Semua', this)">Semua</button>
                        <button class="tab-item" onclick="filterTable('Tinggi', this)">Tinggi</button>
                        <button class="tab-item" onclick="filterTable('Sedang', this)">Sedang</button>
                        <button class="tab-item" onclick="filterTable('Rendah', this)">Rendah</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table id="employeeTable" class="premium-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Divisi</th>
                                <th>Tanggal Deteksi</th>
                                <th>Tingkat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($karyawan_list as $index => $k): ?>
                            <tr data-status="<?= $k['tingkat'] ?>">
                                <td data-label="No"><?= $index + 1 ?></td>
                                <td data-label="Nama"><strong><?= htmlspecialchars($k['nama']) ?></strong></td>
                                <td data-label="Divisi"><?= htmlspecialchars($k['divisi']) ?></td>
                                <td data-label="Tanggal Deteksi"><?= htmlspecialchars($k['tanggal']) ?></td>
                                <td data-label="Tingkat">
                                    <span class="badge-pill badge-<?= strtolower($k['tingkat']) ?>">
                                        <?= $k['tingkat'] ?>
                                    </span>
                                </td>
                                <td data-label="Aksi"><a href="detail_karyawan.php?id=<?= $k['id'] ?>" class="btn-detail">Lihat Detail</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card">
                <h2 class="card-title">Quick Action</h2>
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:1.5rem;">
                    <a href="laporan.php" class="filter-btn" style="text-align:center; display:block; text-decoration:none;">Download Laporan Bulanan</a>
                    <a href="notifikasi.php" class="filter-btn" style="text-align:center; display:block; text-decoration:none;">Kirim Blast Pengingat</a>
                </div>
            </div>
        </div>
            </div>
        </main>
    </div>

    <script>
        function filterTable(status, btn) {
            document.querySelectorAll('.tab-item').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const rows = document.querySelectorAll('#employeeTable tbody tr');
            rows.forEach(row => {
                row.style.display = (status === 'Semua' || row.getAttribute('data-status') === status) ? '' : 'none';
            });
        }

        // ApexCharts Implementation
        const commonOptions = {
            chart: { fontFamily: 'inherit', toolbar: { show: false } },
            tooltip: { theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light' }
        };

        // Trend Chart (Area/Line)
        const trendOptions = {
            ...commonOptions,
            series: [{ name: 'Kasus Burnout', data: [5, 8, 12, 10, 15] }],
            chart: { type: 'area', height: 250, ...commonOptions.chart },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3, colors: ['var(--color-accent)'] },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05,
                    colorStops: [{ offset: 0, color: 'var(--color-accent)', opacity: 0.4 }, { offset: 100, color: 'var(--color-accent)', opacity: 0.05 }]
                }
            },
            markers: { size: 5, colors: ['#fff'], strokeColors: 'var(--color-accent)', strokeWidth: 2 },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei'],
                labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: { labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } } },
            grid: { borderColor: 'var(--color-gray-100)', strokeDashArray: 4 }
        };
        new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

        // Divisi Chart (Stacked Bar)
        const divisiOptions = {
            ...commonOptions,
            series: [
                { name: 'Tinggi', data: [2, 1, 0, 0, 0] },
                { name: 'Sedang', data: [0, 1, 1, 1, 0] },
                { name: 'Rendah', data: [0, 0, 1, 0, 1] }
            ],
            chart: { type: 'bar', height: 250, stacked: true, ...commonOptions.chart },
            colors: ['var(--color-error)', 'var(--color-warning)', 'var(--color-success)'],
            plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '40%' } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['IT', 'Mkt', 'Fin', 'HR', 'Ops'],
                labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: { labels: { style: { colors: 'var(--color-gray-500)', fontSize: '12px' } } },
            grid: { borderColor: 'var(--color-gray-100)', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px', markers: { radius: 12 } }
        };
        new ApexCharts(document.querySelector("#divisiChart"), divisiOptions).render();
    </script>

</body>
</html>
