<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php'); exit();
}

require_once '../config/data_store.php';
bx_init_store();

// Load Knowledge Base from DB
$kb = include '../config/mock_db.php';
$kb_gejala = $kb['gejala'];
$kb_aturan = $kb['aturan'];

require_once '../includes/security.php';

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("CSRF Token Validation Failed. Please refresh and try again.");
}

// ── Simpan jawaban baru ke session (Wizard State) ──
foreach ($_POST as $key => $val) {
    if (preg_match('/^G\d{3}$/', $key) && in_array($val, ['Sering', 'Kadang', 'Tidak Pernah'])) {
        $_SESSION['bc_engine']['answers'][$key] = $val;
    }
}

// ── BC Helpers ──

function get_cf_user(string $answer): float {
    if ($answer === 'Sering')  return 1.0;
    if ($answer === 'Kadang')  return 0.6;
    return 0.0;
}

function get_new_symptoms_needed(array $rules, array $answered, array $kb_gejala): array {
    $valid_gids = array_column($kb_gejala, 'kode');
    $needed = [];
    foreach ($rules as $rule) {
        foreach ($rule['gejala'] as $gid) {
            if (in_array($gid, $valid_gids) && !in_array($gid, $answered) && !in_array($gid, $needed)) {
                $needed[] = $gid;
            }
        }
    }
    return $needed;
}

function evaluate_hypothesis(array $rules, array $kb_gejala, array $answers): array {
    $bobot_map = [];
    $name_map  = [];
    foreach ($kb_gejala as $g) {
        $bobot_map[$g['kode']] = $g['bobot'];
        $name_map[$g['kode']]  = $g['nama'];
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

            $cf_weighted = $cf_user * $bobot;
            $sum_cf_weighted += $cf_weighted;

            $trace[] = sprintf(
                "- %s (%s): CF_user=%.2f × bobot=%.2f = CF_terbobot=%.4f [%s]",
                $gname, $gid, $cf_user, $bobot, $cf_weighted, $ans
            );
        }

        $avg_cf = $sum_cf_weighted / $count;
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

if (!isset($_SESSION['bc_engine'])) {
    $_SESSION['bc_engine'] = ['goal_index' => 0, 'answers' => [], 'bc_trace' => []];
}

$bc_goals     = ['BURNOUT TINGGI', 'BURNOUT SEDANG', 'BURNOUT RENDAH'];
$cf_threshold = 0.25;

// ── MAIN BACKWARD CHAINING LOOP ──
while ($_SESSION['bc_engine']['goal_index'] < count($bc_goals)) {
    $goal_index   = $_SESSION['bc_engine']['goal_index'];
    $current_goal = $bc_goals[$goal_index];

    $goal_rules = array_values(array_filter($kb_aturan, fn($r) => strtoupper($r['diagnosa']) === $current_goal));
    if (empty($goal_rules)) {
        $_SESSION['bc_engine']['goal_index']++;
        continue;
    }

    $answered = array_keys($_SESSION['bc_engine']['answers']);
    $needed   = get_new_symptoms_needed($goal_rules, $answered, $kb_gejala);

    if (!empty($needed)) {
        $_SESSION['bc_engine']['pending_questions']    = $needed;
        $_SESSION['bc_engine']['current_hypothesis']   = "Fase " . ($goal_index + 1);
        $_SESSION['bc_engine']['current_goal_index']   = $goal_index;
        header('Location: deteksi.php');
        exit();
    }

    [$highest_cf, $best_rule, $tracing] = evaluate_hypothesis(
        $goal_rules, $kb_gejala, $_SESSION['bc_engine']['answers']
    );

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
        $level    = str_replace('BURNOUT ', '', strtoupper($best_rule['diagnosa']));
        $label    = 'BURNOUT ' . $level;
        $color    = $best_rule['color']    ?? '#DC3545';
        $bg_light = $best_rule['bg_light'] ?? '#FFF5F5';
        $desc     = $best_rule['desc']     ?? '';
        $confidence = min(99, max(10, intval($highest_cf * 100)));

        $gejala_terdeteksi = [];
        $name_map = array_column($kb_gejala, 'nama', 'kode');
        foreach ($best_rule['gejala'] as $gid) {
            $ans = $_SESSION['bc_engine']['answers'][$gid] ?? 'Tidak Pernah';
            if ($ans !== 'Tidak Pernah') {
                $gejala_terdeteksi[] = ($name_map[$gid] ?? $gid) . " ({$ans})";
            }
        }

        // Rekomendasi static (bisa dipindah ke DB diagnosa)
        $rekomendasi_map = [
            'TINGGI' => [
                ['icon' => '🧘', 'judul' => 'Konseling Psikolog',   'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis profesional.'],
                ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total sangat diperlukan.'],
            ],
            'SEDANG' => [
                ['icon' => '⚖️', 'judul' => 'Manajemen Waktu',    'isi' => 'Prioritaskan tugas penting dan delegasikan tugas.'],
            ],
            'RENDAH' => [
                ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur',     'isi' => 'Pastikan tidur 7-9 jam setiap malam.'],
            ],
        ];
        $rekomendasi = $rekomendasi_map[$level] ?? [];

        $user     = $_SESSION['user'];
        $user_id  = $user['id'];
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
            'jawaban'          => $_SESSION['bc_engine']['answers'],
            'gejala_terdeteksi'=> $gejala_terdeteksi,
            'rekomendasi'      => $rekomendasi,
            'tanggal'          => date('d F Y'),
            'timestamp'        => $timestamp,
            'tracing'          => $tracing,
            'bc_trace'         => $_SESSION['bc_engine']['bc_trace'],
        ];

        $_SESSION['hasil_deteksi'] = $hasil_deteksi;

        // Save to DB
        $db = getDBConnection();
        $stmtDiag = $db->prepare("SELECT id FROM diagnosa WHERE tingkat = ?");
        $stmtDiag->execute([$level === 'RENDAH' ? 'RINGAN' : $level]);
        $diag_id = $stmtDiag->fetchColumn();

        // Get gejala IDs for chosen answers
        $gejala_ids = [];
        $stmtG = $db->prepare("SELECT id FROM gejala WHERE kode = ?");
        foreach ($_SESSION['bc_engine']['answers'] as $gcode => $ans) {
            if ($ans !== 'Tidak Pernah') {
                $stmtG->execute([$gcode]);
                $gid = $stmtG->fetchColumn();
                if ($gid) $gejala_ids[] = $gid;
            }
        }

        save_detection_result_db($user_id, $diag_id, $highest_cf, $gejala_ids);

        unset($_SESSION['bc_engine']);
        header('Location: hasil.php');
        exit();
    }

    $_SESSION['bc_engine']['goal_index']++;
}

// ── TIDAK BURNOUT ──
// ... (logika sama, simpan ke DB dengan ID diagnosa khusus jika ada, atau handle NULL)
unset($_SESSION['bc_engine']);
header('Location: hasil.php');
exit();
