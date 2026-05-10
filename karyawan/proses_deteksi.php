<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php'); exit();
}

require_once '../config/data_store.php';
bx_init_store();

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}
require_once '../includes/security.php';

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("CSRF Token Validation Failed. Please refresh and try again.");
}

$kb_gejala = $_SESSION['mock_kb']['gejala'];
$kb_aturan = $_SESSION['mock_kb']['aturan'];

// ── Simpan jawaban baru ke session ──
foreach ($_POST as $key => $val) {
    if (preg_match('/^G\d{3}$/', $key) && in_array($val, ['Sering', 'Kadang', 'Tidak Pernah'])) {
        $_SESSION['bc_engine']['answers'][$key] = $val;
    }
}

// ── BC Helpers ──

/**
 * CF User berdasarkan jawaban:
 *   Sering     → 1.0
 *   Kadang     → 0.6
 *   Tidak Pernah → 0.0
 */
function get_cf_user(string $answer): float {
    if ($answer === 'Sering')  return 1.0;
    if ($answer === 'Kadang')  return 0.6;
    return 0.0;
}

/**
 * Cari gejala yang belum ditanya dari kumpulan rules.
 */
function get_new_symptoms_needed(array $rules, array $answered): array {
    $needed = [];
    foreach ($rules as $rule) {
        foreach ($rule['gejala'] as $gid) {
            if (!in_array($gid, $answered) && !in_array($gid, $needed)) {
                $needed[] = $gid;
            }
        }
    }
    return $needed;
}

/**
 * Evaluasi hipotesis menggunakan Certainty Factor yang benar:
 *
 * Untuk setiap rule:
 *   CF_gejala_i = CF_user(jawaban_i) × bobot_pakar_gejala_i
 *   CF_combined = rata-rata(CF_gejala_i) untuk semua gejala dalam rule
 *   CF_final    = CF_combined × CF_pakar_rule
 *
 * Dipilih rule dengan CF_final tertinggi sebagai hasil terbaik.
 */
function evaluate_hypothesis(array $rules, array $kb_gejala, array $answers): array {
    // Buat lookup bobot gejala: ['G001' => 0.85, ...]
    $bobot_map = [];
    foreach ($kb_gejala as $g) {
        $bobot_map[$g['id']] = $g['bobot'];
        $name_map[$g['id']]  = $g['nama'];
    }

    $highest_cf = -1.0;
    $best_rule  = null;
    $best_tracing = [];

    foreach ($rules as $rule) {
        $count = count($rule['gejala']);
        if ($count === 0) continue;

        $sum_cf_weighted = 0.0;
        $trace = [];

        foreach ($rule['gejala'] as $gid) {
            $ans      = $answers[$gid] ?? 'Tidak Pernah';
            $cf_user  = get_cf_user($ans);
            $bobot    = $bobot_map[$gid] ?? 1.0;
            $gname    = $name_map[$gid]  ?? $gid;

            // CF terbobot per gejala
            $cf_weighted = $cf_user * $bobot;
            $sum_cf_weighted += $cf_weighted;

            $trace[] = sprintf(
                "- %s (%s): CF_user=%.2f × bobot=%.2f = CF_terbobot=%.4f [%s]",
                $gname, $gid, $cf_user, $bobot, $cf_weighted, $ans
            );
        }

        // Rata-rata CF terbobot semua gejala
        $avg_cf = $sum_cf_weighted / $count;

        // CF final = avg_CF_gejala × CF_pakar_rule
        $cf_final = $avg_cf * $rule['cf_pakar'];

        if ($cf_final > $highest_cf) {
            $highest_cf  = $cf_final;
            $best_rule   = $rule;
            $best_tracing = [
                'rule_id'       => $rule['id'],
                'cf_pakar'      => $rule['cf_pakar'],
                'avg_gejala_cf' => $avg_cf,
                'cf_final'      => $cf_final,
                'details'       => $trace,
                'formula'       => sprintf("CF_final = avg(CF_user × bobot_gejala) × CF_pakar = %.4f × %.2f = %.4f", $avg_cf, $rule['cf_pakar'], $cf_final),
            ];
        }
    }

    return [$highest_cf, $best_rule, $best_tracing];
}

// ── Inisialisasi BC Engine ──
if (!isset($_SESSION['bc_engine'])) {
    $_SESSION['bc_engine'] = ['goal_index' => 0, 'answers' => [], 'bc_trace' => []];
}

// ── Urutan Hipotesis (Backward Chaining: goal-directed, dari paling berat) ──
$bc_goals     = ['BURNOUT TINGGI', 'BURNOUT SEDANG', 'BURNOUT RENDAH'];
$cf_threshold = 0.25; // minimal CF untuk dikonfirmasi

// ── MAIN BACKWARD CHAINING LOOP ──
while ($_SESSION['bc_engine']['goal_index'] < count($bc_goals)) {
    $goal_index   = $_SESSION['bc_engine']['goal_index'];
    $current_goal = $bc_goals[$goal_index];

    // Kumpulkan semua rule untuk hipotesis ini
    $goal_rules = array_values(array_filter($kb_aturan, fn($r) => $r['diagnosa'] === $current_goal));
    if (empty($goal_rules)) {
        $_SESSION['bc_engine']['goal_index']++;
        continue;
    }

    // Cek gejala yang belum ditanya
    $answered = array_keys($_SESSION['bc_engine']['answers']);
    $needed   = get_new_symptoms_needed($goal_rules, $answered);

    if (!empty($needed)) {
        // BC: butuh lebih banyak data → kembali ke wizard
        $_SESSION['bc_engine']['pending_questions']    = $needed;
        $_SESSION['bc_engine']['current_hypothesis']   = "Fase " . ($goal_index + 1); // Label netral
        $_SESSION['bc_engine']['current_goal_index']   = $goal_index;
        header('Location: deteksi.php');
        exit();
    }

    // Semua gejala hipotesis ini sudah dijawab → evaluasi
    [$highest_cf, $best_rule, $tracing] = evaluate_hypothesis(
        $goal_rules, $kb_gejala, $_SESSION['bc_engine']['answers']
    );

    // Catat jejak BC
    $_SESSION['bc_engine']['bc_trace'][] = [
        'goal'      => $current_goal,
        'rule_id'   => $tracing['rule_id'] ?? '-',
        'cf_final'  => $highest_cf,
        'confirmed' => $highest_cf >= $cf_threshold,
        'note'      => ($highest_cf >= $cf_threshold)
            ? "✅ Terkonfirmasi (CF = " . number_format($highest_cf, 4) . " ≥ {$cf_threshold})"
            : "❌ Ditolak (CF = " . number_format($highest_cf, 4) . " < {$cf_threshold}), lanjut ke hipotesis berikutnya",
    ];

    if ($highest_cf >= $cf_threshold) {
        // ── HIPOTESIS TERKONFIRMASI ──
        $level    = str_replace('BURNOUT ', '', $best_rule['diagnosa']);
        $label    = $best_rule['diagnosa'];
        $color    = $best_rule['color']    ?? '#1E3A5F';
        $bg_light = $best_rule['bg_light'] ?? '#F8FAFB';
        $desc     = $best_rule['desc']     ?? '';

        // Confidence: skala 0-100%, pastikan minimal 10% maksimal 99%
        $confidence = min(99, max(10, intval($highest_cf * 100)));

        // Kumpulkan gejala yang terdeteksi (bukan "Tidak Pernah")
        $gejala_terdeteksi = [];
        $name_map = array_column($kb_gejala, 'nama', 'id');
        foreach ($best_rule['gejala'] as $gid) {
            $ans = $_SESSION['bc_engine']['answers'][$gid] ?? 'Tidak Pernah';
            if ($ans !== 'Tidak Pernah') {
                $gejala_terdeteksi[] = ($name_map[$gid] ?? $gid) . " ({$ans})";
            }
        }

        // Tentukan rekomendasi berdasarkan level
        if ($level === 'TINGGI') {
            $rekomendasi = [
                ['icon' => '🧘', 'judul' => 'Konseling Psikolog',   'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis profesional.'],
                ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total sangat diperlukan. Bicarakan dengan HRD untuk pengaturan cuti.'],
                ['icon' => '🤝', 'judul' => 'Dukungan Sosial',      'isi' => 'Bagikan perasaan Anda dengan orang yang dipercaya. Jangan hadapi sendiri.'],
            ];
        } elseif ($level === 'SEDANG') {
            $rekomendasi = [
                ['icon' => '⚖️', 'judul' => 'Manajemen Waktu',    'isi' => 'Prioritaskan tugas penting dan belajar mendelegasikan tugas yang bisa dikerjakan orang lain.'],
                ['icon' => '🏃', 'judul' => 'Rutinitas Olahraga', 'isi' => 'Aktivitas fisik 30 menit/hari terbukti secara klinis mengurangi hormon stres (kortisol).'],
                ['icon' => '💬', 'judul' => 'Diskusi dengan HRD', 'isi' => 'Komunikasikan beban kerja Anda kepada HRD untuk mencari solusi bersama.'],
            ];
        } else {
            $rekomendasi = [
                ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur',     'isi' => 'Pastikan tidur 7-9 jam setiap malam untuk pemulihan fisik dan mental yang optimal.'],
                ['icon' => '🧘', 'judul' => 'Meditasi & Mindfulness',  'isi' => 'Praktik meditasi 10 menit/hari efektif mengurangi stres dan meningkatkan fokus.'],
                ['icon' => '📊', 'judul' => 'Monitor Secara Berkala',  'isi' => 'Lakukan deteksi ulang setiap bulan untuk memantau perkembangan kondisi Anda.'],
            ];
        }

        $total_skor = array_sum(array_map(
            fn($v) => $v === 'Sering' ? 2 : ($v === 'Kadang' ? 1 : 0),
            $_SESSION['bc_engine']['answers']
        ));

        $user     = $_SESSION['user'];
        $user_id  = $user['id'] ?? 'U000';
        $timestamp = date('Y-m-d H:i:s');
        $report_id = generate_report_id($timestamp);

        $hasil_deteksi = [
            'id'               => $report_id,
            'level'            => $level,
            'label'            => $label,
            'color'            => $color,
            'bg_light'         => $bg_light,
            'desc'             => $desc,
            'confidence'       => $confidence,
            'total_skor'       => $total_skor,
            'jawaban'          => $_SESSION['bc_engine']['answers'],
            'gejala_terdeteksi'=> $gejala_terdeteksi,
            'rekomendasi'      => $rekomendasi,
            'tanggal'          => date('d F Y'),
            'timestamp'        => $timestamp,
            'tracing'          => $tracing,
            'bc_trace'         => $_SESSION['bc_engine']['bc_trace'],
        ];

        // Simpan ke session hasil (untuk halaman hasil & laporan)
        $_SESSION['hasil_deteksi'] = $hasil_deteksi;

        // Simpan ke store persisten (untuk riwayat)
        save_detection_result($user_id, $user['nama'], $hasil_deteksi);

        // Notifikasi HRD jika burnout tinggi
        if ($level === 'TINGGI') {
            $_SESSION['bx_store']['hrd_alerts'][] = [
                'type'      => 'critical',
                'user_id'   => $user_id,
                'message'   => "Peringatan: <strong>{$user['nama']}</strong> terdeteksi <strong>Burnout Tinggi</strong> pada " . date('d M Y H:i'),
                'timestamp' => time(),
                'read'      => false,
            ];
            append_log($user['nama'], 'ALERT_BURNOUT_TINGGI', "User#{$user_id}", "Terdeteksi Burnout Tinggi (CF: {$confidence}%)");
        }

        unset($_SESSION['bc_engine']);
        header('Location: hasil.php');
        exit();
    }

    // Hipotesis tidak terkonfirmasi → coba hipotesis berikutnya
    $_SESSION['bc_engine']['goal_index']++;
}

// ── Semua hipotesis gagal → TIDAK BURNOUT ──
$user    = $_SESSION['user'];
$user_id = $user['id'] ?? 'U000';
$timestamp = date('Y-m-d H:i:s');
$report_id = generate_report_id($timestamp);

$hasil_tidak_burnout = [
    'id'               => $report_id,
    'level'            => 'TIDAK ADA',
    'label'            => 'TIDAK BURNOUT',
    'color'            => '#10B981',
    'bg_light'         => '#F0FFF4',
    'desc'             => 'Selamat! Saat ini Anda tidak menunjukkan gejala burnout yang signifikan. Pertahankan keseimbangan hidup Anda.',
    'confidence'       => 5,
    'total_skor'       => 0,
    'jawaban'          => $_SESSION['bc_engine']['answers'] ?? [],
    'gejala_terdeteksi'=> [],
    'rekomendasi'      => [
        ['icon' => '🌟', 'judul' => 'Pertahankan Keseimbangan', 'isi' => 'Terus pertahankan work-life balance yang sudah baik. Anda melakukannya dengan sangat baik!'],
        ['icon' => '📊', 'judul' => 'Deteksi Rutin',            'isi' => 'Lakukan deteksi burnout secara rutin setiap bulan sebagai langkah pencegahan dini.'],
        ['icon' => '🤸', 'judul' => 'Jaga Kesehatan Fisik',     'isi' => 'Olahraga teratur dan tidur cukup adalah fondasi produktivitas jangka panjang.'],
    ],
    'tanggal'          => date('d F Y'),
    'timestamp'        => $timestamp,
    'tracing'          => [],
    'bc_trace'         => $_SESSION['bc_engine']['bc_trace'] ?? [],
];

$_SESSION['hasil_deteksi'] = $hasil_tidak_burnout;
save_detection_result($user_id, $user['nama'], $hasil_tidak_burnout);
unset($_SESSION['bc_engine']);
header('Location: hasil.php');
exit();
