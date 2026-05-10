<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/data_store.php';
bx_init_store();

$user     = $_SESSION['user'];
$nama     = $user['nama'];
$role     = $user['role'];
$active_menu = 'bantuan';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

$sidebar_path = "../includes/sidebar_" . $role . ".php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Bantuan & FAQ – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php if (file_exists($sidebar_path)) include $sidebar_path; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="page-content">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 class="page-title" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Pusat Bantuan BurnoutXpert</h1>
                <p style="color: var(--color-gray-500); font-size: 1.1rem;">Pelajari cara kerja sistem dan temukan jawaban atas pertanyaan Anda.</p>
            </div>

            <!-- Metodologi Section -->
            <div class="content-card" style="margin-bottom: 2rem; border-left: 5px solid var(--color-primary);">
                <h2 class="card-title">🧠 Bagaimana Sistem Mendiagnosis?</h2>
                <div style="line-height: 1.8; color: var(--color-gray-600);">
                    <p>BurnoutXpert menggunakan kombinasi dua metode kecerdasan buatan klasik untuk memberikan hasil yang akurat:</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                        <div style="background: var(--color-gray-50); padding: 1.25rem; border-radius: 12px;">
                            <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1rem;">1. Backward Chaining</h3>
                            <p style="font-size: 0.85rem;">Metode pelacakan ke belakang yang dimulai dari tujuan (diagnosa) lalu mencari fakta (gejala) yang mendukung diagnosa tersebut. Sistem mencocokkan pola gejala Anda dengan basis pengetahuan pakar.</p>
                        </div>
                        <div style="background: var(--color-gray-50); padding: 1.25rem; border-radius: 12px;">
                            <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1rem;">2. Certainty Factor (CF)</h3>
                            <p style="font-size: 0.85rem;">Algoritma untuk menangani ketidakpastian. Karena perasaan manusia bersifat subjektif, CF menghitung tingkat keyakinan hasil diagnosis berdasarkan bobot pakar dan tingkat keyakinan yang Anda berikan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <h2 style="font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem;">Pertanyaan Umum (FAQ)</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <details class="content-card" style="padding: 0;">
                    <summary style="padding: 1.25rem; font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        Apakah hasil deteksi ini bersifat medis?
                        <span style="font-size: 1.2rem;">+</span>
                    </summary>
                    <div style="padding: 0 1.25rem 1.25rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6;">
                        Hasil dari sistem ini bersifat <strong>indikatif</strong> (awal) berdasarkan basis pengetahuan psikologi kerja. Dokumen ini tidak menggantikan diagnosis profesional dari psikolog atau psikiater, namun dapat menjadi data pendukung untuk konsultasi lebih lanjut.
                    </div>
                </details>

                <details class="content-card" style="padding: 0;">
                    <summary style="padding: 1.25rem; font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        Siapa saja yang bisa melihat data saya?
                        <span style="font-size: 1.2rem;">+</span>
                    </summary>
                    <div style="padding: 0 1.25rem 1.25rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6;">
                        Data deteksi Anda dapat diakses oleh tim <strong>HRD</strong> perusahaan sebagai bagian dari monitoring kesejahteraan karyawan. Identitas Anda tetap terjaga sesuai kebijakan privasi perusahaan untuk keperluan intervensi kesehatan mental.
                    </div>
                </details>

                <details class="content-card" style="padding: 0;">
                    <summary style="padding: 1.25rem; font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        Apa yang harus saya lakukan jika hasil diagnosis "Tinggi"?
                        <span style="font-size: 1.2rem;">+</span>
                    </summary>
                    <div style="padding: 0 1.25rem 1.25rem; color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6;">
                        Jangan panik. Hasil tinggi menandakan Anda butuh istirahat segera. Kami menyarankan untuk mengunduh laporan, lalu mendiskusikannya dengan atasan langsung atau tim HRD untuk mendapatkan penyesuaian beban kerja atau waktu cuti.
                    </div>
                </details>
            </div>

            <!-- Footer Help -->
            <div style="margin-top: 3rem; text-align: center; background: var(--color-primary); color: white; padding: 2.5rem; border-radius: 20px;">
                <h3 style="margin-bottom: 0.5rem; font-weight: 800;">Masih butuh bantuan?</h3>
                <p style="opacity: 0.9; margin-bottom: 1.5rem;">Hubungi tim IT Admin atau HRD kami melalui kanal internal perusahaan.</p>
                <a href="mailto:support@burnoutxpert.com" class="btn-cta" style="background: white; color: var(--color-primary); font-weight: 800;">Kirim Email Support</a>
            </div>
        </div>
    </main>
</div>

<style>
details summary::-webkit-details-marker { display: none; }
details[open] summary span { transform: rotate(45deg); }
summary span { transition: transform 0.3s ease; }
</style>

</body>
</html>
