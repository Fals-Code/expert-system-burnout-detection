<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'deteksi';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Daftar Pertanyaan
$questions = [
    "Apakah Anda merasa kelelahan fisik setiap hari meskipun sudah cukup tidur?",
    "Apakah Anda merasa emosi mudah terkuras saat bekerja?",
    "Apakah Anda merasa tidak peduli dengan hasil pekerjaan Anda?",
    "Apakah Anda merasa sulit berkonsentrasi saat bekerja?",
    "Apakah Anda merasa prestasi kerja Anda menurun?",
    "Apakah Anda merasa sinis terhadap rekan kerja atau klien Anda?",
    "Apakah Anda merasa beban kerja Anda terlalu berat untuk ditangani sendiri?",
    "Apakah Anda merasa kurang dihargai atas upaya yang Anda berikan?",
    "Apakah Anda merasa sulit untuk memulai pekerjaan di pagi hari?",
    "Apakah Anda sering merasa putus asa terhadap target pekerjaan Anda?"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deteksi Burnout – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* ── Main Wrapper ── */
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top Bar ── */
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }

        /* ── Detection Wizard ── */
        .wizard-container { max-width: 700px; margin: 3rem auto; padding: 0 1.5rem; width: 100%; }
        
        /* Progress Bar */
        .progress-wrapper { margin-bottom: 2.5rem; text-align: center; }
        .progress-text { font-size: 0.875rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.75rem; display: block; }
        .progress-bar-bg { background: var(--color-gray-200); height: 8px; border-radius: 4px; overflow: hidden; }
        .progress-bar-fill { background: var(--color-accent); height: 100%; width: 10%; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Question Card */
        .question-card {
            background: #fff; border-radius: 20px; padding: 3rem; box-shadow: var(--shadow-lg);
            min-height: 300px; display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
        }

        .step { display: none; animation: fadeIn 0.4s ease; }
        .step.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .question-text { font-size: 1.5rem; font-weight: 700; color: var(--color-primary); line-height: 1.4; text-align: center; margin-bottom: 2.5rem; }

        /* Options */
        .options-group { display: flex; gap: 1rem; justify-content: center; }
        
        .option-btn {
            padding: 1rem 2.5rem; border-radius: 12px; font-weight: 700; font-size: 1rem;
            cursor: pointer; transition: all 0.2s; border: 2px solid var(--color-gray-200);
            background: #fff; color: var(--color-gray-600); min-width: 140px; text-align: center;
        }

        .option-btn:hover { border-color: var(--color-accent); color: var(--color-accent); background: var(--color-accent-50); }
        
        .option-btn.selected { background: var(--color-accent); color: #fff; border-color: var(--color-accent); box-shadow: var(--shadow-accent); }

        /* Navigation Buttons */
        .nav-buttons { display: flex; justify-content: space-between; margin-top: 2rem; }
        
        .btn-nav {
            padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem;
        }

        .btn-prev { background: #fff; color: var(--color-gray-500); border: 1px solid var(--color-gray-200); }
        .btn-prev:hover:not(:disabled) { background: var(--color-gray-50); color: var(--color-primary); }
        .btn-prev:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-next, .btn-result { background: var(--color-primary); color: #fff; border: none; }
        .btn-next:hover:not(:disabled) { background: var(--color-primary-dark); transform: translateY(-2px); }
        .btn-next:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-result { background: var(--color-accent); box-shadow: var(--shadow-accent); }
        .btn-result:hover { background: var(--color-accent-dark); transform: translateY(-2px); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-wrapper { margin-left: 0; }
            .question-card { padding: 2rem; }
            .question-text { font-size: 1.25rem; }
            .options-group { flex-direction: column; }
            .option-btn { width: 100%; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu" style="display:none; background:none; border:none; cursor:pointer; color:var(--color-primary); padding:0.4rem;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="topbar__title">Deteksi Burnout</div>
        </div>
        <div style="font-size: 0.875rem; color: var(--color-gray-500);"><?= date('d F Y') ?></div>
    </header>

    <main class="wizard-container">
        <!-- Progress Bar -->
        <div class="progress-wrapper">
            <span class="progress-text" id="progress-text">Langkah 1 dari 10</span>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progress-bar"></div>
            </div>
        </div>

        <!-- Form Konsultasi -->
        <form id="burnoutForm" action="proses_deteksi.php" method="POST">
            <div class="question-card">
                <?php foreach ($questions as $index => $q): ?>
                <div class="step <?= $index === 0 ? 'active' : '' ?>" data-step="<?= $index + 1 ?>">
                    <h2 class="question-text"><?= $q ?></h2>
                    <div class="options-group">
                        <label class="option-btn" onclick="selectOption(<?= $index + 1 ?>, 'Ya')">
                            <input type="radio" name="q<?= $index + 1 ?>" value="Ya" style="display:none">
                            Ya
                        </label>
                        <label class="option-btn" onclick="selectOption(<?= $index + 1 ?>, 'Tidak')">
                            <input type="radio" name="q<?= $index + 1 ?>" value="Tidak" style="display:none">
                            Tidak
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="nav-buttons">
                <button type="button" class="btn-nav btn-prev" id="prevBtn" onclick="changeStep(-1)" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    Sebelumnya
                </button>
                <button type="button" class="btn-nav btn-next" id="nextBtn" onclick="changeStep(1)" disabled>
                    Selanjutnya
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
                <button type="submit" class="btn-nav btn-result" id="submitBtn" style="display:none">
                    Lihat Hasil
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </form>
    </main>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 10;
    const answers = {};

    function selectOption(step, val) {
        // Highlight selection
        const stepEl = document.querySelector(`.step[data-step="${step}"]`);
        const btns = stepEl.querySelectorAll('.option-btn');
        btns.forEach(btn => {
            btn.classList.remove('selected');
            if (btn.innerText.trim() === val) btn.classList.add('selected');
        });

        // Store answer
        answers[step] = val;
        
        // Enable Next or Submit
        checkNav();
        
        // Auto next with slight delay
        if (step < totalSteps) {
            setTimeout(() => {
                if (currentStep === step) changeStep(1);
            }, 500);
        }
    }

    function changeStep(n) {
        const steps = document.querySelectorAll('.step');
        steps[currentStep - 1].classList.remove('active');
        
        currentStep += n;
        
        if (currentStep < 1) currentStep = 1;
        if (currentStep > totalSteps) currentStep = totalSteps;
        
        steps[currentStep - 1].classList.add('active');
        
        updateUI();
    }

    function updateUI() {
        // Update Progress
        const percent = (currentStep / totalSteps) * 100;
        document.getElementById('progress-bar').style.width = percent + '%';
        document.getElementById('progress-text').innerText = `Langkah ${currentStep} dari ${totalSteps}`;
        
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
        const hasAnswer = !!answers[currentStep];
        document.getElementById('nextBtn').disabled = !hasAnswer;
        document.getElementById('submitBtn').disabled = !hasAnswer;
    }

    updateUI();
</script>

</body>
</html>
