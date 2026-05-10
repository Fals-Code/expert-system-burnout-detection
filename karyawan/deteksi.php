<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$page_title = "Deteksi Burnout";
$active_menu = 'deteksi';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

if (!isset($_SESSION['mock_kb'])) {
    $_SESSION['mock_kb'] = include '../config/mock_db.php';
}

require_once '../includes/security.php';

$kb_gejala  = $_SESSION['mock_kb']['gejala'];
$kb_aturan  = $_SESSION['mock_kb']['aturan'];

// ── BACKWARD CHAINING: Inisialisasi Engine ──
// Reset jika user membuka halaman ini secara segar (bukan redirect dari proses_deteksi.php)
$from_engine = isset($_SESSION['bc_engine']['pending_questions']);

if (!$from_engine) {
    // Sesi baru: inisialisasi dan tentukan pertanyaan untuk hipotesis pertama
    $bc_goals = ['BURNOUT TINGGI', 'BURNOUT SEDANG', 'BURNOUT RENDAH'];
    $first_goal_rules = array_filter($kb_aturan, fn($r) => $r['diagnosa'] === $bc_goals[0]);
    $initial_gids = [];
    foreach ($first_goal_rules as $rule) {
        foreach ($rule['gejala'] as $gid) {
            if (!in_array($gid, $initial_gids)) $initial_gids[] = $gid;
        }
    }
    $_SESSION['bc_engine'] = [
        'goal_index'         => 0,
        'answers'            => [],
        'bc_trace'           => [],
        'current_hypothesis' => $bc_goals[0],
        'current_goal_index' => 0,
        'pending_questions'  => $initial_gids,
    ];
}

// ── Hipotesis saat ini ──
$bc_goals_all = ['BURNOUT TINGGI', 'BURNOUT SEDANG', 'BURNOUT RENDAH'];
$goal_colors  = ['BURNOUT TINGGI' => '#DC3545', 'BURNOUT SEDANG' => '#F59E0B', 'BURNOUT RENDAH' => '#10B981'];
$current_hypothesis  = $_SESSION['bc_engine']['current_hypothesis'];
$current_goal_index  = $_SESSION['bc_engine']['current_goal_index'] ?? 0;
$pending_gids        = $_SESSION['bc_engine']['pending_questions'];
$hypo_color          = $goal_colors[$current_hypothesis] ?? 'var(--color-primary)';

// ── Buat daftar pertanyaan hanya untuk pending symptoms ──
$questions = [];
foreach ($pending_gids as $gid) {
    foreach ($kb_gejala as $g) {
        if ($g['id'] === $gid) {
            $questions[$gid] = "Seberapa sering Anda mengalami: " . $g['nama'] . "?";
            break;
        }
    }
}

$total_questions = count($questions);
$total_steps     = $total_questions + 1; // +1 for summary step
$total_goals     = count($bc_goals_all);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Deteksi Burnout – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>

</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="wizard-container">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-content">
                <div class="spinner-container">
                    <div class="spinner-pulse"></div>
                    <span class="spinner-icon">⚙️</span>
                </div>
                <p class="loading-text">Menganalisis Jawaban Anda...</p>
                <p style="font-size: 0.875rem; color: var(--color-gray-400); margin-top: 0.5rem;">Sistem pakar sedang memproses data klinis...</p>
            </div>
        </div>

        <!-- Welcome Step -->
        <div id="startScreen" class="question-card" style="text-align: center;">
            <div class="step active" style="opacity: 1; transform: none;">
                <div class="finish-icon-wrapper" style="margin-bottom: 2rem;">
                    <div class="pulse-ring"></div>
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </div>
                <h1 class="question-text" style="margin-bottom: 1rem;">Mulai Deteksi Burnout</h1>
                <p style="color: var(--color-gray-500); line-height: 1.6; margin-bottom: 3rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                    Sistem pakar kami akan menganalisis kondisi kesehatan mental Anda melalui serangkaian pertanyaan klinis. Mohon jawab dengan jujur untuk hasil yang paling akurat.
                </p>
                <button type="button" class="btn-nav btn-result" style="margin: 0 auto; padding: 1.25rem 3rem;" onclick="startDetection()">
                    Mulai Analisis Sekarang
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Form Konsultasi -->
        <form id="burnoutForm" action="proses_deteksi.php" method="POST" onsubmit="return handleSubmit(event)" style="display:none">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="question-card">
                <!-- Backward Chaining: Fase / Hipotesis Indicator -->
                <div style="background: linear-gradient(135deg, <?= $hypo_color ?>18, <?= $hypo_color ?>08); border: 1px solid <?= $hypo_color ?>40; border-radius: 12px; padding: 0.75rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display:flex; align-items:center; gap: 0.6rem;">
                        <div style="width:8px; height:8px; border-radius:50%; background:<?= $hypo_color ?>; box-shadow: 0 0 6px <?= $hypo_color ?>;">
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--color-gray-500); text-transform: uppercase; letter-spacing: 0.05em;">Menguji Hipotesis</span>
                        <strong style="font-size: 0.85rem; color: <?= $hypo_color ?>"><?= $current_hypothesis ?></strong>
                    </div>
                    <div style="display: flex; gap: 0.35rem;">
                        <?php foreach ($bc_goals_all as $i => $g): ?>
                        <div style="width: 28px; height: 6px; border-radius: 999px; background: <?= $i <= $current_goal_index ? $goal_colors[$g] : 'var(--color-gray-200)' ?>;"></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Integrated Progress Header -->
                <div class="progress-container">
                    <div class="progress-header">
                        <span class="progress-title" id="progress-text">Gejala 1 dari <?= $total_questions ?></span>
                        <span class="progress-percentage" id="progress-percent">0%</span>
                    </div>
                    <div class="modern-progress-bar">
                        <div class="modern-progress-fill" id="progress-bar" style="width: 0%;"></div>
                    </div>
                </div>
                
                <?php 
                $step_idx = 1;
                foreach ($questions as $gid => $q_text): ?>
                <div class="step <?= $step_idx === 1 ? 'active' : '' ?>" data-step="<?= $step_idx ?>">
                    <h2 class="question-text"><?= htmlspecialchars($q_text) ?></h2>
                    <div class="options-group options-group--3">
                        <label class="option-btn" onclick="selectOption(<?= $step_idx ?>, 'Sering', '<?= $gid ?>')">
                            <input type="radio" name="<?= $gid ?>" value="Sering" style="display:none">
                            <div class="option-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                            </div>
                            <span>Sering Merasakan</span>
                        </label>
                        <label class="option-btn" onclick="selectOption(<?= $step_idx ?>, 'Kadang', '<?= $gid ?>')">
                            <input type="radio" name="<?= $gid ?>" value="Kadang" style="display:none">
                            <div class="option-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" y1="12" x2="20" y2="12"></line>
                                </svg>
                            </div>
                            <span>Kadang-kadang</span>
                        </label>
                        <label class="option-btn" onclick="selectOption(<?= $step_idx ?>, 'Tidak Pernah', '<?= $gid ?>')">
                            <input type="radio" name="<?= $gid ?>" value="Tidak Pernah" style="display:none">
                            <div class="option-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </div>
                            <span>Tidak Pernah</span>
                        </label>
                    </div>
                </div>
                <?php 
                $step_idx++;
                endforeach; ?>

                <!-- Summary Step -->
                <div class="step" data-step="<?= $total_steps ?>">
                    <div class="confetti-decoration">
                        <div class="confetti-piece" style="left: 10%; animation: confettiDrop 4s linear infinite; background: #FFD700;"></div>
                        <div class="confetti-piece" style="left: 30%; animation: confettiDrop 5s linear infinite; background: var(--color-accent); animation-delay: 1s;"></div>
                        <div class="confetti-piece" style="left: 50%; animation: confettiDrop 3s linear infinite; background: #4FACFE; animation-delay: 2s;"></div>
                        <div class="confetti-piece" style="left: 70%; animation: confettiDrop 6s linear infinite; background: #F4845F; animation-delay: 0.5s;"></div>
                        <div class="confetti-piece" style="left: 90%; animation: confettiDrop 4.5s linear infinite; background: #10B981; animation-delay: 1.5s;"></div>
                    </div>
                    <div class="finish-screen">
                        <div class="finish-icon-wrapper">
                            <div class="pulse-ring"></div>
                            <div class="pulse-ring" style="animation-delay: 0.5s"></div>
                            <svg class="finish-svg" width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9882C18.7182 19.7217 16.9033 20.9982 14.8354 21.6263C12.7674 22.2544 10.5573 22.2019 8.52419 21.4768C6.49106 20.7517 4.7502 19.3957 3.56066 17.6078C2.37111 15.8199 1.79619 13.6931 1.92131 11.5401C2.04642 9.38716 2.8647 7.33045 4.25206 5.67389C5.63942 4.01733 7.51862 2.85198 9.60803 2.3508C11.6974 1.84961 13.882 2.03961 15.84 2.89" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4L12 14.01L9 11.01" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2 class="question-text">Analisis Selesai</h2>
                        <p class="finish-subtitle">
                            Terima kasih telah menjawab semua pertanyaan. Sistem siap menganalisis tingkat burnout Anda berdasarkan gejala yang dilaporkan.
                        </p>
                        <div class="finish-info-card">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 0.5rem;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <p>Data Anda bersifat rahasia & aman.</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Navigation Buttons -->
                <div class="nav-buttons">
                    <button type="button" class="btn-nav btn-prev" id="prevBtn" onclick="changeStep(-1)" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Sebelumnya
                    </button>
                    <button type="button" class="btn-nav btn-next" id="nextBtn" onclick="changeStep(1)" disabled>
                        Lanjutkan
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                    <button type="submit" class="btn-nav btn-result" id="submitBtn" style="display:none">
                        Lihat Hasil Analisis
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
    let currentStep = 1;
    const totalSteps = <?= $total_steps ?>;
    const totalQuestions = <?= $total_questions ?>;
    const bcPhase = <?= $current_goal_index ?>; // Which hypothesis phase we are in
    let answers = {};
    let isAnimating = false;
    let autoNextTimeout = null;

    // Clear saved state on new BC phase to avoid stale answers carrying over
    const savedPhase = localStorage.getItem('burnoutWizardPhase');
    if (savedPhase != bcPhase) {
        localStorage.removeItem('burnoutWizardState');
        localStorage.setItem('burnoutWizardPhase', bcPhase);
    }

    function saveState() {
        localStorage.setItem('burnoutWizardState', JSON.stringify({ currentStep, answers }));
    }

    function loadState() {
        const saved = localStorage.getItem('burnoutWizardState');
        if (saved) {
            const data = JSON.parse(saved);
            answers = data.answers || {};
            
            // Setup the UI for existing answers
            for (const [step, val] of Object.entries(answers)) {
                const stepEl = document.querySelector(`.step[data-step="${step}"]`);
                if (stepEl) {
                    const btns = stepEl.querySelectorAll('.option-btn');
                    btns.forEach(btn => {
                        if (btn.innerText.trim() === val) btn.classList.add('selected');
                    });
                    // Check the hidden radio input
                    const radio = stepEl.querySelector(`input[value="${val}"]`);
                    if (radio) radio.checked = true;
                }
            }

            if (data.currentStep > 1 && data.currentStep <= totalSteps) {
                currentStep = data.currentStep;
                
                // Hide start screen and show form
                document.getElementById('startScreen').style.display = 'none';
                document.getElementById('burnoutForm').style.display = 'block';
                updateUI();

                // Jump to the current step without animation
                const steps = document.querySelectorAll('#burnoutForm .step');
                steps.forEach(s => {
                    s.classList.remove('active');
                    s.style.display = 'none';
                });
                steps[currentStep - 1].classList.add('active');
                steps[currentStep - 1].style.display = 'block';
            }
        }
    }

    function startDetection() {
        if (isAnimating) return;
        isAnimating = true;
        document.getElementById('startScreen').style.display = 'none';
        document.getElementById('burnoutForm').style.display = 'block';
        updateUI();
        setTimeout(() => { isAnimating = false; }, 500);
    }

    function selectOption(step, val, gid) {
        if (isAnimating) return;
        // Highlight selection
        const stepEl = document.querySelector(`#burnoutForm .step[data-step="${step}"]`);
        const btns = stepEl.querySelectorAll('.option-btn');
        btns.forEach(btn => {
            btn.classList.remove('selected');
            if (btn.innerText.trim().includes(val)) btn.classList.add('selected');
        });

        // Store answer & check radio
        answers[step] = val;
        const radio = stepEl.querySelector(`input[value="${val}"]`);
        if (radio) radio.checked = true;
        
        saveState();

        checkNav();

        // Auto next with slight delay
        if (step < totalSteps) {
            if (autoNextTimeout) clearTimeout(autoNextTimeout);
            autoNextTimeout = setTimeout(() => {
                if (currentStep === step && !isAnimating) changeStep(1);
            }, 500);
        }
    }

    function changeStep(n) {
        if (isAnimating) return;
        const steps = document.querySelectorAll('#burnoutForm .step');
        const prevStepIdx = currentStep - 1;
        const nextStepIdx = currentStep + n - 1;

        if (nextStepIdx < 0 || nextStepIdx >= totalSteps) return;

        if (autoNextTimeout) clearTimeout(autoNextTimeout);
        isAnimating = true;

        // Apply animations
        steps[prevStepIdx].classList.remove('active', 'slide-in-right', 'slide-in-left');
        steps[prevStepIdx].classList.add(n > 0 ? 'slide-out-left' : 'slide-out-right');

        setTimeout(() => {
            steps[prevStepIdx].style.display = 'none';
            steps[prevStepIdx].classList.remove('slide-out-left', 'slide-out-right');
            
            steps[nextStepIdx].style.display = 'block';
            steps[nextStepIdx].classList.add('active', n > 0 ? 'slide-in-right' : 'slide-in-left');
            
            currentStep += n;
            saveState();
            updateUI();
            
            setTimeout(() => { isAnimating = false; }, 300);
        }, 300);
    }

    function updateUI() {
        // Update Progress
        const percent = Math.round((currentStep / totalSteps) * 100);
        document.getElementById('progress-bar').style.width = percent + '%';
        
        // Update percentage text if it exists
        const percentEl = document.getElementById('progress-percent');
        if (percentEl) percentEl.innerText = percent + '%';
        
        if (currentStep <= totalQuestions) {
            document.getElementById('progress-text').innerText = `Gejala ${currentStep} dari ${totalQuestions}`;
        } else {
            document.getElementById('progress-text').innerText = `Analisis Selesai`;
        }
        
        // Update Buttons
        document.getElementById('prevBtn').disabled = (currentStep === 1);
        
        if (currentStep === totalSteps) {
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'flex';
        } else {
            document.getElementById('nextBtn').style.display = 'flex';
            document.getElementById('submitBtn').style.display = 'none';
        }
        
        checkNav();
    }


    function checkNav() {
        if (currentStep > totalQuestions) {
            document.getElementById('nextBtn').disabled = true;
            document.getElementById('submitBtn').disabled = false;
            return;
        }
        const hasAnswer = !!answers[currentStep];
        document.getElementById('nextBtn').disabled = !hasAnswer;
        document.getElementById('submitBtn').disabled = !hasAnswer;
    }

    function handleSubmit(e) {
        e.preventDefault();
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = 'flex';
        
        // Clear saved state on submit
        localStorage.removeItem('burnoutWizardState');

        // Simulate analysis for 2 seconds
        setTimeout(() => {
            document.getElementById('burnoutForm').submit();
        }, 2000);
        
        return false;
    }

    loadState();
    updateUI();
</script>

</body>
</html>
