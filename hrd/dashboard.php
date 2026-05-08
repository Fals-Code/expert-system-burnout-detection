<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama_hrd = $user['nama'] . " - HRD Manager";

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
    <style>
        body { background: var(--color-gray-50); }

        /* ── Navbar ── */
        .navbar {
            background: var(--color-primary); color: white; padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-md);
        }
        .navbar-brand { font-size: 1.5rem; font-weight: 800; }
        .navbar-brand span { color: var(--color-accent); }
        .navbar-user { display: flex; align-items: center; gap: 1rem; }
        .user-info { text-align: right; }
        .user-name { display: block; font-size: 0.9rem; font-weight: 700; }
        .user-role { display: block; font-size: 0.75rem; color: var(--color-accent-light); }
        .logout-btn { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.8rem; transition: 0.2s; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); }

        /* ── Container ── */
        .dashboard-container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }

        /* ── Summary Cards ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); }
        .stat-label { font-size: 0.75rem; font-weight: 700; color: var(--color-gray-400); text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
        .stat-value { font-size: 2rem; font-weight: 800; color: var(--color-primary); line-height: 1; }
        .stat-card.tinggi { border-top: 4px solid var(--color-error); }
        .stat-card.sedang { border-top: 4px solid var(--color-warning); }
        .stat-card.rendah { border-top: 4px solid var(--color-success); }
        .stat-card.total { border-top: 4px solid var(--color-primary); }

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
        .bar-segment { width: 100%; transition: height 0.5s ease; }
        .bar-label { font-size: 0.7rem; font-weight: 700; color: var(--color-gray-500); }
        
        .legend { display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem; }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 600; color: var(--color-gray-600); }
        .legend-color { width: 12px; height: 12px; border-radius: 3px; }

        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .main-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">Burnout<span>Xpert</span></div>
        <div class="navbar-user">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($nama_hrd) ?></span>
                <span class="user-role">Manajer HRD</span>
            </div>
            <a href="../logout.php" class="logout-btn">Keluar</a>
        </div>
    </nav>

    <div class="dashboard-container">
        
        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <span class="stat-label">Total Karyawan</span>
                <span class="stat-value">125</span>
            </div>
            <div class="stat-card tinggi">
                <span class="stat-label">Burnout Tinggi</span>
                <span class="stat-value">12</span>
            </div>
            <div class="stat-card sedang">
                <span class="stat-label">Burnout Sedang</span>
                <span class="stat-value">34</span>
            </div>
            <div class="stat-card rendah">
                <span class="stat-label">Burnout Rendah</span>
                <span class="stat-value">79</span>
            </div>
        </div>

        <div class="main-grid">
            
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
                                <td><a href="#" class="btn-detail">Lihat Detail</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Distribusi Chart -->
            <div class="content-card">
                <h2 class="card-title" style="margin-bottom: 1rem;">Distribusi per Divisi</h2>
                <div class="chart-container">
                    <?php foreach ($chart_data as $div => $vals): 
                        $max = 3; // Skala maksimal untuk visual
                        $h1 = ($vals[0] / $max) * 100; // Tinggi
                        $h2 = ($vals[1] / $max) * 100; // Sedang
                        $h3 = ($vals[2] / $max) * 100; // Rendah
                    ?>
                    <div class="chart-bar-group">
                        <div class="bar-stack" style="height: 150px;">
                            <div class="bar-segment" style="height: <?= $h1 ?>%; background: var(--color-error);"></div>
                            <div class="bar-segment" style="height: <?= $h2 ?>%; background: var(--color-warning);"></div>
                            <div class="bar-segment" style="height: <?= $h3 ?>%; background: var(--color-success);"></div>
                        </div>
                        <span class="bar-label"><?= $div ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="legend">
                    <div class="legend-item"><div class="legend-color" style="background: var(--color-error);"></div> Tinggi</div>
                    <div class="legend-item"><div class="legend-color" style="background: var(--color-warning);"></div> Sedang</div>
                    <div class="legend-item"><div class="legend-color" style="background: var(--color-success);"></div> Rendah</div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function filterTable(status, btn) {
            // Update Active Button
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter Rows
            const rows = document.querySelectorAll('#employeeTable tbody tr');
            rows.forEach(row => {
                if (status === 'Semua' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
