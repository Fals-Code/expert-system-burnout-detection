<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'hrd') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user      = $_SESSION['user'];
$nama_hrd  = $user['nama'];
$initials_hrd = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama_hrd), 0, 2)));
$active_menu  = 'karyawan';

// Ambil karyawan berdasarkan ?id=
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: karyawan.php');
    exit();
}

$karyawan = find_user_by_id($id);
if (!$karyawan || $karyawan['role'] !== 'karyawan') {
    header('Location: karyawan.php');
    exit();
}

$history = get_user_history($id);
$last    = $history[0] ?? null;

$level       = $last ? $last['level']  : 'Belum Deteksi';
$color       = $last ? $last['color']  : '#9CA3AF';
$bg_light    = $last ? $last['bg_light'] : '#F9FAFB';
$tanggal_last = $last ? $last['tanggal'] : '-';
$confidence  = $last ? $last['confidence'] : 0;

$initials_k = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $karyawan['nama']), 0, 2)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Karyawan – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/sidebar_hrd.php'; ?>

<div class="main-wrapper">
    <?php
        $page_title = "Detail Karyawan";
        ob_start(); ?>
        <a href="karyawan.php" style="font-size:0.85rem; color:var(--color-primary); font-weight:700; text-decoration:none;">
            ← Kembali ke Daftar
        </a>
        <?php
        $topbar_extra = ob_get_clean();
        include '../includes/topbar.php';
    ?>

    <main class="page-content">
        <div class="detail-grid">

            <!-- Sisi Kiri: Profil -->
            <aside>
                <div class="card-profile">
                    <div class="avatar-large"><?= $initials_k ?></div>
                    <h1 class="profile-name"><?= htmlspecialchars($karyawan['nama']) ?></h1>
                    <p class="profile-info">
                        <?= htmlspecialchars($karyawan['posisi'] ?? '-') ?> &bull;
                        <?= htmlspecialchars($karyawan['divisi'] ?? '-') ?>
                    </p>

                    <span class="status-badge" style="background: <?= $bg_light ?>; color: <?= $color ?>; border:1px solid <?= $color ?>40;">
                        <?= $level === 'TIDAK ADA' ? 'Tidak Burnout' : 'Burnout: ' . $level ?>
                    </span>

                    <?php if ($confidence > 0): ?>
                    <div style="margin:0.75rem 0; font-size:0.8rem; color:var(--color-gray-500);">
                        Keyakinan sistem: <strong style="color:<?= $color ?>;"><?= $confidence ?>%</strong>
                    </div>
                    <?php endif; ?>

                    <div class="contact-info">
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <?= htmlspecialchars($karyawan['email']) ?>
                        </div>
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Deteksi terakhir: <strong><?= $tanggal_last ?></strong>
                        </div>
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                            Total deteksi: <strong><?= count($history) ?> kali</strong>
                        </div>
                    </div>

                    <div class="btn-group" style="flex-direction: column; margin-top:1rem;">
                        <button class="btn btn-primary"
                            onclick="alert('Email undangan konseling terkirim ke <?= htmlspecialchars(addslashes($karyawan['email'])) ?>')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Kirim Undangan Konseling
                        </button>
                        <button class="btn btn-outline"
                            onclick="alert('Permintaan cuti sedang diproses untuk <?= htmlspecialchars(addslashes($karyawan['nama'])) ?>')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Rekomendasikan Cuti
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Sisi Kanan: Analisis & Riwayat -->
            <section>
                <!-- Rekomendasi Aksi HRD -->
                <div class="content-card">
                    <h2 class="card-title">📝 Rekomendasi Aksi HRD</h2>
                    <div class="rec-box">
                        <div class="rec-title">Diagnosis Sistem</div>
                        <?php if (!$last): ?>
                        <p class="rec-text">Karyawan ini belum pernah melakukan deteksi burnout. Dorong karyawan untuk segera melakukan deteksi mandiri.</p>
                        <?php else: ?>
                        <p class="rec-text">Berdasarkan deteksi terakhir pada <strong><?= $tanggal_last ?></strong>, karyawan menunjukkan tingkat burnout <strong style="color:<?= $color ?>;"><?= $level ?></strong> dengan keyakinan <strong><?= $confidence ?>%</strong>.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($level === 'TINGGI'): ?>
                    <div class="rec-box" style="border-left:3px solid #DC3545; background:#FFF5F5;">
                        <div class="rec-title" style="color:#DC3545;">⚠️ Aksi Prioritas: Intervensi Langsung</div>
                        <p class="rec-text">Segera jadwalkan pertemuan 1-on-1 untuk mendiskusikan beban kerja. Berikan waktu istirahat minimal 3 hari kerja dan pertimbangkan redistribusi tanggung jawab proyek yang berat.</p>
                    </div>
                    <?php elseif ($level === 'SEDANG'): ?>
                    <div class="rec-box" style="border-left:3px solid #F59E0B; background:#FFFBEB;">
                        <div class="rec-title" style="color:#D97706;">🟡 Aksi Prioritas: Monitoring & Pendampingan</div>
                        <p class="rec-text">Perhatikan keterlibatan karyawan dalam rapat. Berikan apresiasi lebih atas kinerjanya dan diskusikan kemungkinan fleksibilitas waktu kerja (WFH atau jam fleksibel).</p>
                    </div>
                    <?php elseif ($level === 'RENDAH'): ?>
                    <div class="rec-box" style="border-left:3px solid #3B82F6; background:#EFF6FF;">
                        <div class="rec-title" style="color:#1D4ED8;">🔵 Aksi Prioritas: Pencegahan Dini</div>
                        <p class="rec-text">Kondisi masih awal. Berikan edukasi tentang pentingnya work-life balance dan pantau secara berkala.</p>
                    </div>
                    <?php else: ?>
                    <div class="rec-box" style="border-left:3px solid #10B981; background:#F0FFF4;">
                        <div class="rec-title" style="color:#065F46;">✅ Aksi Prioritas: Apresiasi & Pencegahan</div>
                        <p class="rec-text">Karyawan dalam kondisi stabil. Berikan feedback positif secara rutin untuk menjaga motivasi dan pastikan keseimbangan kerja tetap terjaga.</p>
                    </div>
                    <?php endif; ?>

                    <?php if ($last && !empty($last['gejala_terdeteksi'])): ?>
                    <div class="rec-box">
                        <div class="rec-title">Gejala yang Terdeteksi</div>
                        <ul style="padding-left:1.25rem; line-height:2; margin:0;">
                            <?php foreach ($last['gejala_terdeteksi'] as $g): ?>
                            <li style="font-size:0.875rem; color:var(--color-gray-600);"><?= htmlspecialchars($g) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Riwayat Deteksi -->
                <div class="content-card" style="margin-top:1.25rem;">
                    <h2 class="card-title">🕒 Riwayat Deteksi (<?= count($history) ?> Entri)</h2>
                    <?php if (empty($history)): ?>
                    <p style="color:var(--color-gray-400); text-align:center; padding:2rem; font-style:italic;">
                        Karyawan ini belum pernah melakukan deteksi burnout.
                    </p>
                    <?php else: ?>
                    <div class="history-list">
                        <?php foreach ($history as $i => $h): ?>
                        <div class="history-item" style="opacity: <?= max(0.4, 1 - $i * 0.2) ?>;">
                            <div>
                                <div class="history-date"><?= htmlspecialchars($h['tanggal']) ?></div>
                                <div style="font-size:0.75rem; color:var(--color-gray-400);">
                                    ID: <?= htmlspecialchars($h['id'] ?? '-') ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div class="history-level" style="color: <?= $h['color'] ?>;">
                                    <?= htmlspecialchars($h['label']) ?>
                                </div>
                                <div style="font-size:0.75rem; color:var(--color-gray-400);">
                                    Keyakinan: <?= $h['confidence'] ?>%
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>
</div>

</body>
</html>
