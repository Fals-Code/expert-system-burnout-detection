<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php'); exit();
}
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
    if (preg_match('/^G\d{3}$/', $key) && in_array($val, ['Sering','Kadang','Tidak Pernah'])) {
        $_SESSION['bc_engine']['answers'][$key] = $val;
    }
}

// ── BC Helpers ──
function get_cf_user($answer) {
    if ($answer === 'Sering') return 1.0;
    if ($answer === 'Kadang') return 0.5;
    return 0.0;
}

function get_new_symptoms_needed($rules, $answered) {
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

function evaluate_hypothesis($rules, $kb_gejala, $answers) {
    $highest_cf = -1.0; $best_rule = null; $best_tracing = [];
    foreach ($rules as $rule) {
        $count = count($rule['gejala']);
        if ($count === 0) continue;
        $sum_cf = 0; $trace = [];
        foreach ($rule['gejala'] as $gid) {
            $ans = $answers[$gid] ?? 'Tidak Pernah';
            $ev_cf = get_cf_user($ans);
            $sum_cf += $ev_cf;
            $gname = '';
            foreach ($kb_gejala as $g) { if ($g['id'] === $gid) { $gname = $g['nama']; break; } }
            $trace[] = "- {$gname} ({$gid}): CF = " . number_format($ev_cf, 2) . " [{$ans}]";
        }
        $avg = $sum_cf / $count;
        $final = $avg * $rule['cf_pakar'];
        if ($final > $highest_cf) {
            $highest_cf = $final; $best_rule = $rule;
            $best_tracing = ['rule_id' => $rule['id'], 'cf_pakar' => $rule['cf_pakar'],
                             'avg_gejala_cf' => $avg, 'details' => $trace];
        }
    }
    return [$highest_cf, $best_rule, $best_tracing];
}

// ── Inisialisasi BC Engine ──
if (!isset($_SESSION['bc_engine'])) {
    $_SESSION['bc_engine'] = ['goal_index' => 0, 'answers' => [], 'bc_trace' => []];
}

// ── Urutan Hipotesis (dari yang paling berat = Backward Chaining goal-directed) ──
$bc_goals = ['BURNOUT TINGGI', 'BURNOUT SEDANG', 'BURNOUT RENDAH'];
$cf_threshold = 0.2;

// ── MAIN BACKWARD CHAINING LOOP ──
while ($_SESSION['bc_engine']['goal_index'] < count($bc_goals)) {
    $goal_index = $_SESSION['bc_engine']['goal_index'];
    $current_goal = $bc_goals[$goal_index];

    // Cari rule yang mendukung hipotesis ini
    $goal_rules = array_values(array_filter($kb_aturan, fn($r) => $r['diagnosa'] === $current_goal));
    if (empty($goal_rules)) { $_SESSION['bc_engine']['goal_index']++; continue; }

    // Cek apakah ada gejala yang belum ditanya untuk hipotesis ini
    $answered = array_keys($_SESSION['bc_engine']['answers']);
    $needed = get_new_symptoms_needed($goal_rules, $answered);

    if (!empty($needed)) {
        // BC: Perlu data lebih — kembalikan ke wizard untuk menanyakan gejala yang relevan
        $_SESSION['bc_engine']['pending_questions'] = $needed;
        $_SESSION['bc_engine']['current_hypothesis'] = $current_goal;
        $_SESSION['bc_engine']['current_goal_index'] = $goal_index;
        header('Location: deteksi.php');
        exit();
    }

    // Semua gejala untuk hipotesis ini sudah dijawab — evaluasi sekarang
    [$highest_cf, $best_rule, $tracing] = evaluate_hypothesis($goal_rules, $kb_gejala, $_SESSION['bc_engine']['answers']);

    // Catat jejak BC
    $_SESSION['bc_engine']['bc_trace'][] = [
        'goal'      => $current_goal,
        'rule_id'   => $tracing['rule_id'] ?? '-',
        'cf_final'  => $highest_cf,
        'confirmed' => $highest_cf >= $cf_threshold,
        'note'      => ($highest_cf >= $cf_threshold)
            ? "✅ Terkonfirmasi (CF = " . number_format($highest_cf, 3) . " ≥ {$cf_threshold})"
            : "❌ Ditolak (CF = " . number_format($highest_cf, 3) . " < {$cf_threshold}), lanjut ke hipotesis berikutnya"
    ];

    if ($highest_cf >= $cf_threshold) {
        // ── HIPOTESIS TERKONFIRMASI → Simpan hasil ──
        $level    = str_replace('BURNOUT ', '', $best_rule['diagnosa']);
        $label    = $best_rule['diagnosa'];
        $color    = $best_rule['color'] ?? '#1E3A5F';
        $bg_light = $best_rule['bg_light'] ?? '#F8FAFB';
        $desc     = $best_rule['desc'] ?? '';
        $confidence = min(99, max(10, intval($highest_cf * 100)));

        $gejala_terdeteksi = [];
        foreach ($best_rule['gejala'] as $gid) {
            $ans = $_SESSION['bc_engine']['answers'][$gid] ?? 'Tidak Pernah';
            if ($ans !== 'Tidak Pernah') {
                foreach ($kb_gejala as $g) {
                    if ($g['id'] === $gid) { $gejala_terdeteksi[] = $g['nama'] . " ({$ans})"; break; }
                }
            }
        }

        if ($level === 'TINGGI') {
            $rekomendasi = [
                ['icon' => '🧘', 'judul' => 'Konseling Psikolog', 'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis.'],
                ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total sangat diperlukan untuk pemulihan.'],
            ];
            if (!isset($_SESSION['hrd_alerts'])) $_SESSION['hrd_alerts'] = [];
            $nama_karyawan = $_SESSION['user']['nama'] ?? 'Karyawan';
            $_SESSION['hrd_alerts'][] = [
                'type' => 'critical',
                'message' => "Peringatan: <strong>$nama_karyawan</strong> terdeteksi <strong>Burnout Tinggi</strong> pada " . date('d M Y H:i'),
                'timestamp' => time(), 'read' => false
            ];
        } elseif ($level === 'SEDANG') {
            $rekomendasi = [
                ['icon' => '⚖️', 'judul' => 'Manajemen Waktu', 'isi' => 'Prioritaskan tugas penting dan belajar mendelegasikan.'],
                ['icon' => '🏃', 'judul' => 'Rutinitas Olahraga', 'isi' => 'Aktivitas fisik terbukti mengurangi hormon stres.'],
            ];
        } else {
            $rekomendasi = [
                ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur', 'isi' => 'Pastikan tidur 7-9 jam setiap malam.'],
                ['icon' => '🧘', 'judul' => 'Meditasi & Mindfulness', 'isi' => 'Praktik meditasi efektif mengurangi stres.'],
            ];
        }

        $total_skor = array_sum(array_map(fn($v) => $v === 'Sering' ? 2 : ($v === 'Kadang' ? 1 : 0), $_SESSION['bc_engine']['answers']));

        $_SESSION['hasil_deteksi'] = [
            'level' => $level, 'label' => $label, 'color' => $color, 'bg_light' => $bg_light,
            'desc' => $desc, 'confidence' => $confidence, 'total_skor' => $total_skor,
            'jawaban' => $_SESSION['bc_engine']['answers'],
            'gejala_terdeteksi' => $gejala_terdeteksi, 'rekomendasi' => $rekomendasi,
            'tanggal' => date('d F Y'), 'timestamp' => date('Y-m-d H:i:s'),
            'tracing' => $tracing,
            'bc_trace' => $_SESSION['bc_engine']['bc_trace'],
        ];
        unset($_SESSION['bc_engine']);
        header('Location: hasil.php'); exit();
    }

    // Hipotesis tidak terkonfirmasi → coba hipotesis berikutnya
    $_SESSION['bc_engine']['goal_index']++;
}

// ── Semua hipotesis gagal → TIDAK BURNOUT ──
$_SESSION['hasil_deteksi'] = [
    'level' => 'TIDAK ADA', 'label' => 'TIDAK BURNOUT', 'color' => '#10B981', 'bg_light' => '#F0FFF4',
    'desc' => 'Selamat! Saat ini Anda tidak menunjukkan gejala burnout yang signifikan.',
    'confidence' => 5, 'total_skor' => 0, 'jawaban' => [],
    'gejala_terdeteksi' => [],
    'rekomendasi' => [
        ['icon' => '🌟', 'judul' => 'Pertahankan Keseimbangan', 'isi' => 'Terus pertahankan work-life balance yang sudah baik.'],
        ['icon' => '📊', 'judul' => 'Deteksi Rutin', 'isi' => 'Lakukan deteksi burnout secara rutin setiap bulan.'],
    ],
    'tanggal' => date('d F Y'), 'timestamp' => date('Y-m-d H:i:s'),
    'tracing' => [],
    'bc_trace' => $_SESSION['bc_engine']['bc_trace'] ?? [],
];
unset($_SESSION['bc_engine']);
header('Location: hasil.php'); exit();
