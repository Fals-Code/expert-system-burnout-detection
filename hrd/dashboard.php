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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HRD – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/favicon.php'; ?>
    <?php include '../includes/favicon.php'; ?>
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

        /* ── Top Header ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
            box-shadow: var(--shadow-sm);
        }

        .topbar__left { display: flex; flex-direction: column; gap: 2px; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
        .topbar__breadcrumb { font-size: 0.75rem; color: var(--color-gray-400); font-weight: 500; }

        .topbar__right { display: flex; align-items: center; gap: 1rem; }

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        /* ── Page Content ── */
        .page-content {
            padding: 2rem;
            flex: 1;
        }

        /* ── Container ── */
        /* ── Container ── */
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

        /* ── Chart Section ── */
        .chart-container { height: 300px; display: flex; align-items: flex-end; gap: 1.5rem; padding-top: 2rem; border-bottom: 1px solid var(--color-gray-200); position: relative; }
        .chart-bar-group { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
        .bar-stack { width: 40px; display: flex; flex-direction: column-reverse; border-radius: 4px 4px 0 0; overflow: hidden; }
        .bar-segment { width: 100%; animation: growUp 1s ease-out forwards; transform-origin: bottom; }
        .bar-label { font-size: 0.7rem; font-weight: 700; color: var(--color-gray-500); }
        
        @keyframes growUp {
            from { transform: scaleY(0); }
            to { transform: scaleY(1); }
        }
        
        .legend { display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem; }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 600; color: var(--color-gray-600); }
        .legend-color { width: 12px; height: 12px; border-radius: 3px; }

        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .main-grid { grid-template-columns: 1fr; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
        .chart-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="topbar__left">
                    <div class="topbar__title">Dashboard Monitoring HRD</div>
                    <div class="topbar__breadcrumb">BurnoutXpert › HRD › Dashboard</div>
                </div>
            </div>
            <div class="topbar__right">
                <button onclick="toggleTheme()" style="background:none; border:none; color:var(--color-primary); cursor:pointer; padding:0.5rem;" id="theme-toggle">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" id="sun-icon"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </button>
                <div style="display:flex;align-items:center;gap:0.6rem;">
                    <div class="topbar__name" style="font-size: 0.875rem; font-weight: 700; color: var(--color-gray-700);"><?= htmlspecialchars($nama) ?></div>
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800; border: 2px solid var(--color-accent-50);"><?= $initials ?></div>
                </div>
            </div>
        </header>

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
        // Theme Toggle Logic
        function toggleTheme() {
            const body = document.body;
            const theme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.querySelector('#theme-toggle svg');
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            if (isDark) {
                icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>';
            } else {
                icon.innerHTML = '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>';
            }
        }

        // Apply theme on load
        if (localStorage.getItem('theme') === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            updateThemeIcon();
        }

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
