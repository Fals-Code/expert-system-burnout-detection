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
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .page-content { padding: 2rem; flex: 1; max-width: 1000px; margin: 0 auto; width: 100%; }

        .help-header { text-align: center; margin-bottom: 3rem; }
        .help-header h1 { font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem; }
        .help-header p { color: var(--color-gray-500); font-size: 1.1rem; }

        .faq-grid { display: grid; gap: 1.5rem; }
        .faq-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); }
        .faq-question { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem; }
        .faq-answer { font-size: 0.95rem; color: var(--color-gray-600); line-height: 1.6; }

        .emergency-box { background: #FFF5F5; border: 2px dashed #DC3545; border-radius: 20px; padding: 2rem; margin-top: 3rem; text-align: center; }
        .emergency-box h2 { color: #DC3545; font-weight: 800; margin-bottom: 1rem; }
        .contact-grid { display: flex; justify-content: center; gap: 2rem; margin-top: 1.5rem; }
        .contact-item { font-weight: 700; color: var(--color-primary); font-size: 1.1rem; }

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 768px) {
            .contact-grid { flex-direction: column; gap: 1rem; }
            .help-header h1 { font-size: 1.5rem; }
        }
    </style>
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
