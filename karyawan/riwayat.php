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
