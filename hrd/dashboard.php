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
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* ── Main Wrapper ── */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }



        /* ── Page Content ── */
        .page-content {
            padding: 2rem;
            flex: 1;
        }

        .dashboard-container { width: 100%; margin: 0; padding: 0; }

        /* ── Summary Cards ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-100);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
        
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .stat-info { display: flex; flex-direction: column; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--color-primary); line-height: 1.2; }
        .stat-label { font-size: 0.7rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; letter-spacing: 0.05em; }

        .bg-blue { background: var(--color-primary-50); color: var(--color-primary); }
        .bg-red { background: var(--color-error-bg); color: var(--color-error); }
        .bg-orange { background: var(--color-warning-bg); color: #856404; }
        .bg-green { background: var(--color-success-bg); color: var(--color-success); }


        /* ── Layout Grid ── */
        .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }

        /* ── Table Section ── */
        .content-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); padding: 1.5rem; margin-bottom: 1.5rem; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .card-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
        
        .filter-group { display: flex; gap: 0.5rem; }
        .filter-btn { padding: 0.4rem 1rem; border-radius: 8px; border: 1px solid var(--color-gray-200); background: #fff; color: var(--color-gray-600); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .filter-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }
        .filter-btn.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.8rem; font-weight: 700; color: var(--color-gray-400); border-bottom: 2px solid var(--color-gray-50); text-transform: uppercase; }
        td { padding: 1rem; font-size: 0.9rem; border-bottom: 1px solid var(--color-gray-50); }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-tinggi { background: var(--color-error-bg); color: var(--color-error); }
        .badge-sedang { background: var(--color-warning-bg); color: #856404; }
        .badge-rendah { background: var(--color-success-bg); color: var(--color-success); }
        
        .btn-detail { color: var(--color-primary); font-weight: 700; font-size: 0.8rem; text-decoration: underline; }

        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
        .chart-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 992px) {
            .main-grid { grid-template-columns: 1fr; }
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <canvas id="trendChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="card-title" style="margin-bottom: 1.5rem;">Distribusi per Divisi</h3>
                <canvas id="divisiChart"></canvas>
            </div>
        </div>

        <div class="main-grid" style="margin-top: 1.5rem;">
            
            <!-- Daftar Karyawan -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Monitoring Karyawan</h2>
                    <div class="filter-group">
                        <button class="filter-btn active" onclick="filterTable('Semua', this)">Semua</button>
                        <button class="filter-btn" onclick="filterTable('Tinggi', this)">Tinggi</button>
                        <button class="filter-btn" onclick="filterTable('Sedang', this)">Sedang</button>
                        <button class="filter-btn" onclick="filterTable('Rendah', this)">Rendah</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="employeeTable">
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
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($k['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($k['divisi']) ?></td>
                                <td><?= htmlspecialchars($k['tanggal']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($k['tingkat']) ?>">
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
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const rows = document.querySelectorAll('#employeeTable tbody tr');
            rows.forEach(row => {
                row.style.display = (status === 'Semua' || row.getAttribute('data-status') === status) ? '' : 'none';
            });
        }

        // Chart.js Implementation
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei'],
                datasets: [{
                    label: 'Kasus Burnout',
                    data: [5, 8, 12, 10, 15],
                    borderColor: '#F4845F',
                    backgroundColor: 'rgba(244,132,95,0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const ctxDivisi = document.getElementById('divisiChart').getContext('2d');
        new Chart(ctxDivisi, {
            type: 'bar',
            data: {
                labels: ['IT', 'Mkt', 'Fin', 'HR', 'Ops'],
                datasets: [
                    { label: 'Tinggi', data: [2, 1, 0, 0, 0], backgroundColor: '#DC3545' },
                    { label: 'Sedang', data: [0, 1, 1, 1, 0], backgroundColor: '#FFC107' },
                    { label: 'Rendah', data: [0, 0, 1, 0, 1], backgroundColor: '#28A745' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true } }
            }
        });
    </script>

</body>
</html>
