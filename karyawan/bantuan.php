<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'bantuan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Bantuan & FAQ – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
</head>
<body>
<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div class="help-header">
            <h1>Butuh Bantuan?</h1>
            <p>Kami di sini untuk mendukung kesehatan mental dan produktivitas Anda.</p>
        </div>

        <div class="faq-grid">
            <div class="faq-card">
                <div class="faq-question">
                    <span>🤔</span>
                    Apa itu Burnout?
                </div>
                <div class="faq-answer">
                    Burnout adalah kondisi kelelahan secara fisik, mental, dan emosional yang disebabkan oleh stres berkepanjangan di tempat kerja. Burnout berbeda dengan kelelahan biasa karena seringkali disertai rasa sinis dan penurunan prestasi kerja.
                </div>
            </div>

            <div class="faq-card">
                <div class="faq-question">
                    <span>🧠</span>
                    Bagaimana cara kerja sistem deteksi ini?
                </div>
                <div class="faq-answer">
                    Sistem kami menggunakan metode <strong>Backward Chaining</strong> dengan algoritma <strong>Certainty Factor</strong>. Kami mencocokkan gejala yang Anda rasakan dengan basis pengetahuan para pakar psikologi untuk memberikan tingkat keyakinan (persentase) hasil diagnosis.
                </div>
            </div>

            <div class="faq-card">
                <div class="faq-question">
                    <span>🔒</span>
                    Apakah data saya aman?
                </div>
                <div class="faq-answer">
                    Keamanan data adalah prioritas kami. Hasil deteksi Anda bersifat rahasia dan hanya digunakan secara anonim untuk laporan statistik perusahaan guna memperbaiki kebijakan kesejahteraan karyawan.
                </div>
            </div>
        </div>

        <div class="emergency-box">
            <h2>🆘 Butuh Konsultasi Segera?</h2>
            <p>Jika Anda merasa sangat tertekan dan membutuhkan teman bicara profesional saat ini juga:</p>
            <div class="contact-grid">
                <div class="contact-item">📞 HR Helpline: (021) 1234-5678</div>
                <div class="contact-item">💬 WhatsApp Psikolog: 0812-3456-7890</div>
            </div>
            <p style="margin-top: 1.5rem; font-size: 0.85rem; color: var(--color-gray-500);">Layanan ini tersedia 24/7 khusus untuk seluruh karyawan perusahaan.</p>
        </div>
    </main>
</div>
</body>
</html>
