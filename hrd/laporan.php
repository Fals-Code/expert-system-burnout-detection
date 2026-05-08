<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));
$active_menu = 'laporan';

// Mock Data Laporan per Divisi
$laporan_divisi = [
    ['divisi' => 'IT Engineering', 'total' => 25, 'tinggi' => 5, 'sedang' => 12, 'rendah' => 8],
    ['divisi' => 'Marketing', 'total' => 18, 'tinggi' => 2, 'sedang' => 6, 'rendah' => 10],
    ['divisi' => 'Finance', 'total' => 12, 'tinggi' => 1, 'sedang' => 4, 'rendah' => 7],
    ['divisi' => 'Human Resources', 'total' => 10, 'tinggi' => 0, 'sedang' => 3, 'rendah' => 7],
    ['divisi' => 'Operational', 'total' => 30, 'tinggi' => 4, 'sedang' => 9, 'rendah' => 17],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Burnout – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .page-content { padding: 2rem; flex: 1; }

        .content-card { background: #fff; border-radius: 20px; padding: 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .card-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; }

        /* Table Style */
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { text-align: left; padding: 1rem; font-size: 0.8rem; font-weight: 700; color: var(--color-gray-400); border-bottom: 2px solid var(--color-gray-50); text-transform: uppercase; }
        td { padding: 1rem; font-size: 0.95rem; border-bottom: 1px solid var(--color-gray-50); color: var(--color-gray-700); }
        
        .badge-count { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 800; font-size: 0.75rem; }
        .badge-tinggi { background: var(--color-error-bg); color: var(--color-error); }
        .badge-sedang { background: var(--color-warning-bg); color: #856404; }
        .badge-rendah { background: var(--color-success-bg); color: var(--color-success); }

        .btn-print { background: var(--color-accent); color: #fff; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; border: none; font-size: 0.9rem; transition: 0.2s; }
        .btn-print:hover { background: var(--color-accent-dark); transform: translateY(-2px); }

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0 !important; }
        }

        @media (max-width: 768px) {
            .page-content { padding: 1rem; }
            .content-card { padding: 1.25rem; }
            .topbar__actions { width: 100%; display: flex; gap: 0.5rem; justify-content: space-between; }
            .btn-print { flex: 1; padding: 0.6rem; font-size: 0.8rem; justify-content: center; }
            th, td { padding: 0.75rem 0.5rem; font-size: 0.85rem; }
        }

        @media print {
            .sidebar, .topbar, .btn-print { display: none !important; }
            .main-wrapper { margin-left: 0; }
            .content-card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Laporan Divisi";
        ob_start(); ?>
        <div class="topbar__actions" style="display:flex; gap:0.75rem; align-items:center;">
            <button class="btn-print" style="background:#28A745;" onclick="showToast('Ekspor Excel sedang disiapkan...', 'info')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Excel</span>
            </button>
            <button class="btn-print" onclick="window.print()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak PDF</span>
            </button>
        </div>
        <?php 
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <?php include '../includes/toast.php'; ?>

        <div class="content-card">
            <h2 class="card-title">Rekapitulasi Deteksi - Mei 2026</h2>
            <p style="color: var(--color-gray-500); font-size: 0.9rem; margin-bottom: 2rem;">Laporan ini merangkum tingkat burnout karyawan di setiap divisi untuk periode berjalan.</p>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Divisi</th>
                            <th>Total Asesmen</th>
                            <th>Burnout Tinggi</th>
                            <th>Burnout Sedang</th>
                            <th>Burnout Rendah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_divisi as $l): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--color-primary);"><?= $l['divisi'] ?></td>
                            <td><?= $l['total'] ?> orang</td>
                            <td><span class="badge-count badge-tinggi"><?= $l['tinggi'] ?></span></td>
                            <td><span class="badge-count badge-sedang"><?= $l['sedang'] ?></span></td>
                            <td><span class="badge-count badge-rendah"><?= $l['rendah'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <h2 class="card-title">💡 Analisis Singkat HRD</h2>
            <div style="background: var(--color-gray-50); padding: 1.5rem; border-radius: 12px; font-size: 0.95rem; color: var(--color-gray-700); line-height: 1.6;">
                <p>Divisi <strong>IT Engineering</strong> dan <strong>Operational</strong> menunjukkan tingkat burnout tinggi terbanyak. Disarankan untuk meninjau kembali *deadline* proyek di divisi IT dan beban kerja operasional di lapangan pada kuartal depan.</p>
            </div>
        </div>
    </main>
</div>

</body>
</html>
