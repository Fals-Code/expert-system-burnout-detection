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
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
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
        .chart-container { height: 220px; width: 100%; margin-top: 1rem; position: relative; }
        .chart-svg { width: 100%; height: 100%; overflow: visible; }
        .chart-line { 
            fill: none; stroke: var(--color-primary); stroke-width: 4; 
            stroke-linecap: round; stroke-linejoin: round; 
            stroke-dasharray: 1000; stroke-dashoffset: 1000;
            animation: drawLine 2s ease-out forwards;
        }
        .chart-area { fill: url(#chartGradient); opacity: 0; animation: fadeIn 1s ease-out 1.5s forwards; }
        .chart-point { fill: #fff; stroke: var(--color-primary); stroke-width: 3; opacity: 0; animation: fadeIn 0.5s ease-out forwards; }
        .chart-label { font-size: 10px; fill: var(--color-gray-400); font-weight: 700; }

        @keyframes drawLine { to { stroke-dashoffset: 0; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .btn-cta { background: var(--color-accent); color: #fff; padding: 0.7rem 1.25rem; border-radius: 12px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; transition: 0.2s; box-shadow: var(--shadow-accent); }
        .btn-cta:hover { transform: translateY(-2px); background: var(--color-accent-dark); }


        .btn-report { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 700; color: var(--color-primary); text-decoration: underline; }

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0 !important; }
            .content-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: center; text-align: center; gap: 1rem; }
            .btn-cta { width: 100%; justify-content: center; }
            .timeline { padding-left: 1.5rem; }
            .timeline-content { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .timeline-content > div:last-child { width: 100%; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-gray-50); padding-top: 0.5rem; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>

        <main style="padding: 0 2rem 2rem;">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
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
            <div style="background: #fff; border-radius: 24px; padding: 5rem 2rem; text-align: center; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); margin-top: 2rem;">
                <div style="font-size: 5rem; margin-bottom: 1.5rem;">🧘‍♂️</div>
                <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem;">Belum Ada Riwayat Deteksi</h2>
                <p style="color: var(--color-gray-500); font-size: 1.1rem; max-width: 500px; margin: 0 auto 2.5rem;">
                    Sepertinya Anda belum melakukan pengecekan kesehatan mental. Mulailah sekarang untuk memantau kondisi Anda.
                </p>
                <a href="deteksi.php" class="btn-cta" style="display: inline-flex; width: auto; padding: 1rem 2.5rem;">
                    Mulai Deteksi Sekarang
                </a>
            </div>
        <?php else: ?>
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
                            <defs>
                                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="var(--color-primary)" stop-opacity="0.2" />
                                    <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Grid Lines -->
                            <line x1="0" y1="130" x2="300" y2="130" stroke="#F1F4F7" stroke-width="1" />
                            <line x1="0" y1="80" x2="300" y2="80" stroke="#F1F4F7" stroke-width="1" />
                            <line x1="0" y1="30" x2="300" y2="30" stroke="#F1F4F7" stroke-width="1" />
                            
                            <text x="0" y="145" class="chart-label">Des</text>
                            <text x="75" y="145" class="chart-label">Jan</text>
                            <text x="150" y="145" class="chart-label">Feb</text>
                            <text x="225" y="145" class="chart-label">Mar</text>
                            <text x="280" y="145" class="chart-label">Mei</text>

                            <!-- Area Fill -->
                            <path class="chart-area" d="M 0 130 L 0 130 L 75 130 L 150 80 L 225 80 L 300 30 V 130 H 0 Z" />

                            <!-- Trend Line (Skor: Rendah=130, Sedang=80, Tinggi=30) -->
                            <path class="chart-line" d="M 0 130 L 75 130 L 150 80 L 225 80 L 300 30" />
                            
                            <!-- Points -->
                            <circle class="chart-point" cx="0" cy="130" r="5" style="animation-delay: 0.2s;" />
                            <circle class="chart-point" cx="75" cy="130" r="5" style="animation-delay: 0.5s;" />
                            <circle class="chart-point" cx="150" cy="80" r="5" style="animation-delay: 1.0s;" />
                            <circle class="chart-point" cx="225" cy="80" r="5" style="animation-delay: 1.5s;" />
                            <circle class="chart-point" cx="300" cy="30" r="5" style="animation-delay: 2.0s;" />
                        </svg>
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
