<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}

// ============================================================
//  BurnoutXpert – Logika Sistem Pakar (Backward Chaining)
//  Metode: Backward Chaining + Certainty Factor sederhana
//
//  Basis Pengetahuan:
//   - BURNOUT TINGGI  : Jawaban "Ya" >= 7 dari 10 gejala
//   - BURNOUT SEDANG  : Jawaban "Ya" 4–6
//   - BURNOUT RENDAH  : Jawaban "Ya" <= 3
//   - TIDAK BURNOUT   : Jawaban "Ya" = 0
//
//  CF (Certainty Factor) = (jumlah_ya / 10) * 100
// ============================================================

// Daftar gejala (label singkat untuk ditampilkan)
$gejala_labels = [
    1  => 'Kelelahan Fisik',
    2  => 'Kelelahan Emosional',
    3  => 'Depersonalisasi (Tidak Peduli)',
    4  => 'Sulit Berkonsentrasi',
    5  => 'Penurunan Prestasi Kerja',
    6  => 'Sikap Sinis',
    7  => 'Beban Kerja Berlebih',
    8  => 'Merasa Tidak Dihargai',
    9  => 'Sulit Memulai Kerja',
    10 => 'Putus Asa terhadap Target',
];

// ── Kumpulkan jawaban dari POST ──────────────────────────────
$jawaban = [];
$jumlah_ya = 0;
$gejala_terdeteksi = [];

for ($i = 1; $i <= 10; $i++) {
    $val = isset($_POST["q$i"]) ? $_POST["q$i"] : 'Tidak';
    $jawaban[$i] = $val;
    if ($val === 'Ya') {
        $jumlah_ya++;
        $gejala_terdeteksi[] = $gejala_labels[$i];
    }
}

// ── Backward Chaining: Buktikan hipotesis dari tujuan ──────
// Goal: Tentukan tingkat burnout tertinggi yang terbukti
// Backward: Cek hipotesis TINGGI dulu → SEDANG → RENDAH → TIDAK

$level    = '';
$label    = '';
$color    = '';
$bg_light = '';
$desc     = '';
$rekomendasi = [];

if ($jumlah_ya >= 7) {
    // ── Hipotesis: BURNOUT TINGGI ──
    $level    = 'TINGGI';
    $label    = 'BURNOUT TINGGI';
    $color    = '#DC3545';
    $bg_light = '#FFF5F5';
    $desc     = 'Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, penurunan motivasi signifikan, dan kemungkinan depersonalisasi. Kondisi ini memerlukan perhatian segera dan penanganan profesional.';
    $rekomendasi = [
        ['icon' => '🧘', 'judul' => 'Konseling Psikolog', 'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis. Burnout tingkat tinggi memerlukan intervensi profesional untuk mencegah dampak jangka panjang terhadap kesehatan fisik dan mental Anda.'],
        ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total selama minimal 5 hari kerja sangat diperlukan untuk pemulihan. Gunakan waktu ini untuk aktivitas yang Anda sukai tanpa gangguan pekerjaan sama sekali.'],
        ['icon' => '🤝', 'judul' => 'Diskusi dengan HRD', 'isi' => 'Segera komunikasikan kondisi Anda dengan HRD atau atasan langsung. Minta penyesuaian beban kerja, fleksibilitas jam kerja, atau redistribusi tugas untuk mengurangi tekanan mental yang dialami.'],
    ];
} elseif ($jumlah_ya >= 4) {
    // ── Hipotesis: BURNOUT SEDANG ──
    $level    = 'SEDANG';
    $label    = 'BURNOUT SEDANG';
    $color    = '#F59E0B';
    $bg_light = '#FFFBEB';
    $desc     = 'Anda menunjukkan tanda-tanda burnout tingkat sedang. Beberapa gejala mulai mengganggu produktivitas dan kualitas kerja Anda. Diperlukan tindakan preventif segera sebelum kondisi memburuk.';
    $rekomendasi = [
        ['icon' => '⚖️', 'judul' => 'Manajemen Waktu & Prioritas', 'isi' => 'Pelajari teknik manajemen waktu seperti metode Pomodoro atau Eisenhower Matrix. Prioritaskan tugas-tugas penting dan belajar untuk mendelegasikan pekerjaan yang bisa dikerjakan orang lain.'],
        ['icon' => '🏃', 'judul' => 'Rutinitas Olahraga', 'isi' => 'Mulailah rutinitas olahraga ringan minimal 30 menit per hari, 3-4 kali seminggu. Aktivitas fisik terbukti secara ilmiah dapat mengurangi hormon stres dan meningkatkan mood.'],
        ['icon' => '💬', 'judul' => 'Konsultasi Awal', 'isi' => 'Pertimbangkan sesi konsultasi awal dengan konselor atau psikolog untuk mendapatkan strategi koping yang tepat sebelum kondisi Anda memburuk lebih lanjut.'],
    ];
} elseif ($jumlah_ya >= 1) {
    // ── Hipotesis: BURNOUT RENDAH ──
    $level    = 'RENDAH';
    $label    = 'BURNOUT RENDAH';
    $color    = '#10B981';
    $bg_light = '#F0FFF4';
    $desc     = 'Anda menunjukkan gejala burnout tingkat rendah. Kondisi Anda masih dalam batas normal namun perlu diwaspadai. Beberapa tanda awal kelelahan mulai muncul, dan tindakan pencegahan dini sangat dianjurkan.';
    $rekomendasi = [
        ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur', 'isi' => 'Pastikan Anda tidur 7-9 jam setiap malam. Buat rutinitas tidur yang konsisten, hindari layar gadget 1 jam sebelum tidur, dan ciptakan lingkungan tidur yang nyaman dan gelap.'],
        ['icon' => '🧘', 'judul' => 'Meditasi & Mindfulness', 'isi' => 'Coba praktik meditasi 10-15 menit per hari menggunakan aplikasi seperti Headspace atau Calm. Mindfulness terbukti efektif mengurangi stres dan meningkatkan fokus kerja.'],
        ['icon' => '📅', 'judul' => 'Work-Life Balance', 'isi' => 'Tetapkan batas waktu kerja yang jelas. Hindari menjawab email pekerjaan di luar jam kerja dan dedikasikan waktu untuk hobi serta keluarga. Keseimbangan ini kunci produktivitas jangka panjang.'],
    ];
} else {
    // ── Hipotesis: TIDAK BURNOUT ──
    $level    = 'TIDAK ADA';
    $label    = 'TIDAK BURNOUT';
    $color    = '#10B981';
    $bg_light = '#F0FFF4';
    $desc     = 'Selamat! Saat ini Anda tidak menunjukkan gejala burnout yang signifikan. Kondisi kesehatan mental Anda dalam keadaan baik. Pertahankan gaya hidup sehat dan keseimbangan kerja yang sudah Anda jalani.';
    $rekomendasi = [
        ['icon' => '🌟', 'judul' => 'Pertahankan Keseimbangan', 'isi' => 'Terus pertahankan work-life balance yang sudah baik. Jadwalkan kegiatan sosial dan hobi secara rutin untuk menjaga kesehatan mental jangka panjang.'],
        ['icon' => '📊', 'judul' => 'Deteksi Rutin', 'isi' => 'Lakukan deteksi burnout secara rutin setiap bulan sebagai bentuk pemantauan diri. Deteksi dini jauh lebih mudah ditangani daripada menunggu kondisi memburuk.'],
        ['icon' => '💪', 'judul' => 'Dukung Rekan Kerja', 'isi' => 'Kondisi mental Anda yang baik memberi Anda kapasitas untuk mendukung rekan kerja yang mungkin sedang kesulitan. Lingkungan kerja yang supportif bermanfaat untuk semua.'],
    ];
}

// ── Certainty Factor (CF) ─────────────────────────────────────
// CF minimum 10% agar lingkaran progress tidak kosong
$confidence = max(10, intval(($jumlah_ya / 10) * 100));

// Jika TIDAK BURNOUT, CF representasikan kesehatan (bukan burnout)
if ($jumlah_ya === 0) $confidence = 5;

// ── Simpan ke Session ─────────────────────────────────────────
$_SESSION['hasil_deteksi'] = [
    'level'              => $level,
    'label'              => $label,
    'color'              => $color,
    'bg_light'           => $bg_light,
    'desc'               => $desc,
    'confidence'         => $confidence,
    'jumlah_ya'          => $jumlah_ya,
    'jawaban'            => $jawaban,
    'gejala_terdeteksi'  => $gejala_terdeteksi,
    'rekomendasi'        => $rekomendasi,
    'tanggal'            => date('d F Y'),
    'timestamp'          => date('Y-m-d H:i:s'),
];

// ── Redirect ke halaman hasil ─────────────────────────────────
header('Location: hasil.php');
exit();
