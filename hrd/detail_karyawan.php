<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama_hrd = $user['nama'];
$initials_hrd = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama_hrd), 0, 2)));
$active_menu = 'karyawan';

// Mock Data Karyawan (Sama dengan dashboard)
$karyawan_list = [
    101 => ['nama' => 'Andi Wijaya', 'divisi' => 'IT', 'tanggal' => '10 Mei 2026', 'tingkat' => 'Tinggi', 'email' => 'andi@company.com', 'posisi' => 'Senior Developer'],
    102 => ['nama' => 'Maria Ulfa', 'divisi' => 'Marketing', 'tanggal' => '09 Mei 2026', 'tingkat' => 'Sedang', 'email' => 'maria@company.com', 'posisi' => 'SEO Specialist'],
    103 => ['nama' => 'Bambang', 'divisi' => 'Finance', 'tanggal' => '08 Mei 2026', 'tingkat' => 'Rendah', 'email' => 'bambang@company.com', 'posisi' => 'Accountant'],
    104 => ['nama' => 'Citra', 'divisi' => 'HR', 'tanggal' => '07 Mei 2026', 'tingkat' => 'Sedang', 'email' => 'citra@company.com', 'posisi' => 'HR Generalist'],
    105 => ['nama' => 'Dedi', 'divisi' => 'IT', 'tanggal' => '06 Mei 2026', 'tingkat' => 'Tinggi', 'email' => 'dedi@company.com', 'posisi' => 'UI/UX Designer'],
    106 => ['nama' => 'Eka', 'divisi' => 'Operasional', 'tanggal' => '05 Mei 2026', 'tingkat' => 'Rendah', 'email' => 'eka@company.com', 'posisi' => 'Admin Ops'],
    107 => ['nama' => 'Farhan', 'divisi' => 'Marketing', 'tanggal' => '04 Mei 2026', 'tingkat' => 'Tinggi', 'email' => 'farhan@company.com', 'posisi' => 'Copywriter'],
    108 => ['nama' => 'Gita', 'divisi' => 'Finance', 'tanggal' => '03 Mei 2026', 'tingkat' => 'Sedang', 'email' => 'gita@company.com', 'posisi' => 'Tax Officer'],
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 101;
$k = isset($karyawan_list[$id]) ? $karyawan_list[$id] : $karyawan_list[101];
$initials_k = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $k['nama']), 0, 2)));

// Status Styling
$status_color = '#DC3545';
$status_bg = '#F8D7DA';
if ($k['tingkat'] === 'Sedang') { $status_color = '#F59E0B'; $status_bg = '#FFFBEB'; }
elseif ($k['tingkat'] === 'Rendah') { $status_color = '#10B981'; $status_bg = '#F0FFF4'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Karyawan – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .page-content { padding: 2rem; flex: 1; }

        .detail-grid { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }
        
        /* Card Kiri */
        .card-profile { background: #fff; border-radius: 20px; padding: 2.5rem 1.5rem; text-align: center; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
        .avatar-large { width: 100px; height: 100px; border-radius: 50%; background: var(--color-primary-50); color: var(--color-primary); font-size: 2.5rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 4px solid #fff; box-shadow: var(--shadow-md); }
        .profile-name { font-size: 1.5rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.25rem; }
        .profile-info { font-size: 0.875rem; color: var(--color-gray-500); margin-bottom: 1.5rem; font-weight: 600; }
        
        .status-badge { display: inline-block; padding: 0.5rem 1.5rem; border-radius: 99px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; background: <?= $status_bg ?>; color: <?= $status_color ?>; margin-bottom: 2rem; }

        .contact-info { text-align: left; background: var(--color-gray-50); padding: 1.25rem; border-radius: 12px; margin-top: 1rem; }
        .contact-item { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--color-gray-600); }
        .contact-item:last-child { margin-bottom: 0; }
        .contact-item svg { color: var(--color-gray-400); }

        /* Card Kanan */
        .content-card { background: #fff; border-radius: 20px; padding: 2rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .card-title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        
        .rec-box { background: <?= $status_bg ?>20; border-left: 4px solid <?= $status_color ?>; padding: 1.5rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem; }
        .rec-title { font-weight: 800; color: <?= $status_color ?>; margin-bottom: 0.5rem; font-size: 0.95rem; }
        .rec-text { font-size: 0.9rem; color: var(--color-gray-700); line-height: 1.6; }

        .btn-group { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn { padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.9rem; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 0.6rem; transition: 0.2s; border: none; }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { background: var(--color-primary-dark); transform: translateY(-2px); }
        .btn-outline { background: #fff; border: 1px solid var(--color-gray-200); color: var(--color-gray-600); }
        .btn-outline:hover { background: var(--color-gray-50); border-color: var(--color-primary); color: var(--color-primary); }

        .history-list { display: flex; flex-direction: column; gap: 1rem; }
        .history-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-radius: 12px; background: var(--color-gray-50); border: 1px solid var(--color-gray-100); }
        .history-date { font-weight: 700; color: var(--color-primary); font-size: 0.9rem; }
        .history-level { font-size: 0.8rem; font-weight: 800; }

        @media (max-width: 992px) {
            .detail-grid { grid-template-columns: 1fr; }
            .main-wrapper { margin-left: 0 !important; }
        }
        @media (max-width: 768px) {
            .page-content { padding: 1rem; }
            .card-profile { padding: 1.5rem; }
            .content-card { padding: 1.5rem; }
            .btn-group { gap: 0.75rem; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php 
        $page_title = "Detail Karyawan";
        include '../includes/topbar.php'; 
    ?>

    <main class="page-content">
        <div class="detail-grid">
            
            <!-- Sisi Kiri: Profil -->
            <aside>
                <div class="card-profile">
                    <div class="avatar-large"><?= $initials_k ?></div>
                    <h1 class="profile-name"><?= htmlspecialchars($k['nama']) ?></h1>
                    <p class="profile-info"><?= $k['posisi'] ?> • <?= $k['divisi'] ?></p>
                    
                    <span class="status-badge">Tingkat Burnout: <?= $k['tingkat'] ?></span>
                    
                    <div class="contact-info">
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <?= $k['email'] ?>
                        </div>
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            +62 812-xxxx-xxxx
                        </div>
                    </div>

                    <div class="btn-group" style="flex-direction: column;">
                        <button class="btn btn-primary" onclick="alert('Email undangan konseling terkirim ke <?= $k['email'] ?>')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Kirim Undangan Konseling
                        </button>
                        <button class="btn btn-outline" onclick="alert('Permintaan cuti sedang diproses untuk <?= $k['nama'] ?>')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Rekomendasikan Cuti
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Sisi Kanan: Analisis & Riwayat -->
            <section>
                <div class="content-card">
                    <h2 class="card-title">📝 Rekomendasi Aksi HRD</h2>
                    
                    <div class="rec-box">
                        <div class="rec-title">Diagnosis Sistem</div>
                        <p class="rec-text">Berdasarkan deteksi terakhir pada <?= $k['tanggal'] ?>, karyawan menunjukkan tingkat burnout <strong><?= $k['tingkat'] ?></strong>.</p>
                    </div>

                    <?php if ($k['tingkat'] === 'Tinggi'): ?>
                        <div class="rec-box">
                            <div class="rec-title">Aksi Prioritas: Intervensi Langsung</div>
                            <p class="rec-text">Segera jadwalkan pertemuan 1-on-1 untuk mendiskusikan beban kerja. Disarankan memberikan waktu istirahat minimal 3 hari kerja dan memindahkan sementara tanggung jawab proyek berat.</p>
                        </div>
                    <?php elseif ($k['tingkat'] === 'Sedang'): ?>
                        <div class="rec-box">
                            <div class="rec-title">Aksi Prioritas: Monitoring & Pendampingan</div>
                            <p class="rec-text">Perhatikan keterlibatan karyawan dalam rapat. Berikan apresiasi lebih atas kinerjanya dan diskusikan kemungkinan fleksibilitas waktu kerja (WFH atau jam fleksibel).</p>
                        </div>
                    <?php else: ?>
                        <div class="rec-box">
                            <div class="rec-title">Aksi Prioritas: Apresiasi & Pencegahan</div>
                            <p class="rec-text">Karyawan dalam kondisi stabil. Berikan feedback positif secara rutin untuk menjaga motivasi dan pastikan keseimbangan kerja tetap terjaga.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="content-card">
                    <h2 class="card-title">🕒 Riwayat Deteksi</h2>
                    <div class="history-list">
                        <div class="history-item">
                            <div class="history-date"><?= $k['tanggal'] ?></div>
                            <div class="history-level" style="color: <?= $status_color ?>;">BURNOUT <?= strtoupper($k['tingkat']) ?></div>
                        </div>
                        <div class="history-item" style="opacity: 0.6;">
                            <div class="history-date">10 April 2026</div>
                            <div class="history-level" style="color: #F59E0B;">BURNOUT SEDANG</div>
                        </div>
                        <div class="history-item" style="opacity: 0.4;">
                            <div class="history-date">12 Maret 2026</div>
                            <div class="history-level" style="color: #10B981;">BURNOUT RENDAH</div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>
</div>

</body>
</html>
