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

// Mock History Data
$history = [
    ['tanggal' => '12 Apr 2025', 'tingkat' => 'Tinggi', 'skor' => 3, 'color' => '#DC3545'],
    ['tanggal' => '15 Mar 2025', 'tingkat' => 'Sedang', 'skor' => 2, 'color' => '#FFC107'],
    ['tanggal' => '10 Feb 2025', 'tingkat' => 'Sedang', 'skor' => 2, 'color' => '#FFC107'],
    ['tanggal' => '05 Jan 2025', 'tingkat' => 'Rendah', 'skor' => 1, 'color' => '#28A745'],
    ['tanggal' => '20 Des 2024', 'tingkat' => 'Rendah', 'skor' => 1, 'color' => '#28A745'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Deteksi – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* ── Top Header ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .main-wrapper { margin-left: 0; padding: 0; }
            .hamburger { display: flex; }
        }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.5rem; font-weight: 800; color: var(--color-primary); }
        
        .content-grid { display: grid; grid-template-columns: 1fr 350px; gap: 2rem; }
        .card { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-100); }
        .card-title { font-size: 1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; }

        /* ── Timeline ── */
        .timeline { position: relative; padding-left: 2rem; }
        .timeline::before { content: ''; position: absolute; left: 0.35rem; top: 0; bottom: 0; width: 2px; background: var(--color-gray-100); }
        .timeline-item { position: relative; margin-bottom: 2rem; }
        .timeline-dot { position: absolute; left: -1.95rem; top: 0.25rem; width: 12px; height: 12px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px var(--color-gray-100); z-index: 2; }
        .timeline-content { display: flex; justify-content: space-between; align-items: center; }
        .timeline-date { font-size: 0.8rem; font-weight: 700; color: var(--color-gray-400); margin-bottom: 0.25rem; }
        .timeline-level { font-size: 1rem; font-weight: 800; color: var(--color-primary); }
        
        .badge { padding: 0.3rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-tinggi { background: #FFF5F5; color: #DC3545; }
        .badge-sedang { background: #FFFBEB; color: #D97706; }
        .badge-rendah { background: #F0FFF4; color: #28A745; }

        /* ── Trend Chart (SVG) ── */
        .chart-container { height: 200px; width: 100%; margin-top: 1rem; position: relative; }
        .chart-svg { width: 100%; height: 100%; overflow: visible; }
        .chart-line { fill: none; stroke: var(--color-primary); stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .chart-point { fill: #fff; stroke: var(--color-primary); stroke-width: 2; }
        .chart-label { font-size: 10px; fill: var(--color-gray-400); font-weight: 600; }

        .btn-report { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 700; color: var(--color-primary); text-decoration: underline; }

        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

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
                <div class="topbar__title" style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">Riwayat Deteksi</div>
            </div>
            <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
        </header>

        <main style="padding: 0 2rem 2rem;">
            <div class="page-header">
                <h1 class="page-title">Riwayat Deteksi Burnout</h1>
                <p style="color: var(--color-gray-500); font-size: 0.9rem;">Pantau tren kondisi kesehatan mental Anda dari waktu ke waktu.</p>
            </div>

        <div class="content-grid">
            
            <!-- Timeline Riwayat -->
            <div class="card">
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
            <div class="card">
                <h2 class="card-title">Tren Kondisi</h2>
                <div class="chart-container">
                    <svg class="chart-svg" viewBox="0 0 300 150">
                        <!-- Grid Lines -->
                        <line x1="0" y1="130" x2="300" y2="130" stroke="#F1F4F7" stroke-width="1" />
                        <line x1="0" y1="80" x2="300" y2="80" stroke="#F1F4F7" stroke-width="1" />
                        <line x1="0" y1="30" x2="300" y2="30" stroke="#F1F4F7" stroke-width="1" />
                        
                        <text x="0" y="145" class="chart-label">Des</text>
                        <text x="75" y="145" class="chart-label">Jan</text>
                        <text x="150" y="145" class="chart-label">Feb</text>
                        <text x="225" y="145" class="chart-label">Mar</text>
                        <text x="280" y="145" class="chart-label">Apr</text>

                        <!-- Trend Line (Skor: Rendah=130, Sedang=80, Tinggi=30) -->
                        <path class="chart-line" d="M 0 130 L 75 130 L 150 80 L 225 80 L 300 30" />
                        
                        <!-- Points -->
                        <circle class="chart-point" cx="0" cy="130" r="4" />
                        <circle class="chart-point" cx="75" cy="130" r="4" />
                        <circle class="chart-point" cx="150" cy="80" r="4" />
                        <circle class="chart-point" cx="225" cy="80" r="4" />
                        <circle class="chart-point" cx="300" cy="30" r="4" />
                    </svg>
                </div>
                <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--color-gray-500); line-height: 1.5;">
                    <p>💡 <strong>Analisis:</strong> Tingkat burnout Anda menunjukkan tren meningkat sejak Februari. Disarankan untuk mengambil istirahat terencana.</p>
                </div>
            </div>

        </div>
        </main>
    </div>

</body>
</html>
