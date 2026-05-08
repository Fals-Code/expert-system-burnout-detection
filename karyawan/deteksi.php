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
    <title>Deteksi Burnout – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* ── Main Wrapper ── */
        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top Bar ── */


        /* ── Detection Wizard ── */
        .wizard-container { max-width: 700px; margin: 3rem auto; padding: 0 1.5rem; width: 100%; }
        
        /* Progress Bar */
        .progress-wrapper { margin-bottom: 2.5rem; text-align: center; }
        .progress-text { font-size: 0.875rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.75rem; display: block; }
        .progress-bar-bg { background: var(--color-gray-200); height: 8px; border-radius: 4px; overflow: hidden; }
        .progress-bar-fill { background: var(--color-accent); height: 100%; width: 10%; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Question Card */
        .question-card {
            background: #fff; border-radius: 24px; padding: 0; box-shadow: var(--shadow-lg);
            min-height: 350px; display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
            border: 1px solid var(--color-gray-100);
        }

        .step {
            display: none;
            width: 100%;
            padding: 3rem;
            box-sizing: border-box;
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
        }
        .step.active { display: block; }

        /* Animations */
        .slide-in-right { animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-out-left { animation: slideOutLeft 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-in-left { animation: slideInLeft 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .slide-out-right { animation: slideOutRight 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }

        @keyframes slideInRight { from { transform: translate(100%, -50%); opacity: 0; } to { transform: translate(0, -50%); opacity: 1; } }
        @keyframes slideOutLeft { from { transform: translate(0, -50%); opacity: 1; } to { transform: translate(-100%, -50%); opacity: 0; } }
        @keyframes slideInLeft { from { transform: translate(-100%, -50%); opacity: 0; } to { transform: translate(0, -50%); opacity: 1; } }
        @keyframes slideOutRight { from { transform: translate(0, -50%); opacity: 1; } to { transform: translate(100%, -50%); opacity: 0; } }

        .question-text { font-size: 1.5rem; font-weight: 700; color: var(--color-primary); line-height: 1.4; text-align: center; margin-bottom: 2.5rem; }

        /* Options */
        .options-group { display: flex; gap: 1.25rem; justify-content: center; }
        
        .option-btn {
            padding: 1.25rem 2.5rem; border-radius: 16px; font-weight: 700; font-size: 1.1rem;
            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 2px solid var(--color-gray-200);
            background: #fff; color: var(--color-gray-600); min-width: 160px; text-align: center;
            position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; gap: 0.75rem;
        }

        .option-btn:hover { border-color: var(--color-accent); color: var(--color-accent); background: var(--color-accent-50); }
        .option-btn.selected { background: var(--color-accent); color: #fff; border-color: var(--color-accent); box-shadow: var(--shadow-accent); transform: scale(1.02); }

        /* Step Indicators */
        .step-indicators { display: flex; justify-content: center; gap: 0.6rem; margin-bottom: 1.5rem; }
        .indicator-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--color-gray-200); transition: 0.3s; }
        .indicator-dot.active { background: var(--color-accent); transform: scale(1.3); }
        .indicator-dot.completed { background: var(--color-primary); }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed; inset: 0; background: rgba(255,255,255,0.98);
            display: none; flex-direction: column; align-items: center; justify-content: center;
            z-index: 1000; gap: 1.5rem; backdrop-filter: blur(5px);
        }
        .loading-content { text-align: center; }
        .spinner-icon { font-size: 3.5rem; margin-bottom: 1rem; display: block; animation: pulseGear 1.5s ease-in-out infinite; }
        .loading-text { font-size: 1.2rem; font-weight: 800; color: var(--color-primary); letter-spacing: 0.02em; }
        @keyframes pulseGear { 
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.15); }
        }

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
        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 768px) {
            .wizard-container { margin: 1rem auto; padding: 0 1rem; }
            .question-card { min-height: 380px; }
            .step { padding: 1.5rem 1rem; }
            .question-text { font-size: 1.2rem; margin-bottom: 2rem; }
            .options-group { flex-direction: column; width: 100%; gap: 0.75rem; }
            .option-btn { width: 100%; min-height: 60px; padding: 1rem; font-size: 1rem; }
            .indicator-dot { width: 7px; height: 7px; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

<div class="main-wrapper">
    <?php include '../includes/topbar.php'; ?>

    <main class="wizard-container">
        <!-- Step Indicators -->
        <div class="step-indicators" id="step-indicators" style="display:none">
            <?php for($i=1; $i<=11; $i++): ?>
                <div class="indicator-dot <?= $i===1 ? 'active' : '' ?>"></div>
            <?php endfor; ?>
        </div>

        <!-- Progress Bar -->
        <div class="progress-wrapper" style="display:none">
            <span class="progress-text" id="progress-text">Langkah 1 dari 10</span>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progress-bar"></div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-content">
                <span class="spinner-icon">⚙️</span>
                <p class="loading-text">Sedang menganalisis jawaban Anda...</p>
            </div>
        </div>

        <!-- Welcome Step -->
        <div id="startScreen" class="question-card" style="display: flex; text-align: center; justify-content: center; align-items: center; padding: 3rem;">
            <div style="max-width: 500px;">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem;">🏥</div>
                <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1rem;">Mulai Analisis Burnout</h2>
                <p style="color: var(--color-gray-500); line-height: 1.6; margin-bottom: 2rem;">
                    Sistem pakar kami akan membuktikan hipotesis kondisi kesehatan mental Anda melalui 10 pertanyaan klinis. Mohon jawab dengan jujur untuk hasil yang akurat.
                </p>
                <button type="button" class="btn-nav btn-result" style="margin: 0 auto; padding: 1rem 2.5rem;" onclick="startDetection()">
                    Mulai Sekarang
                </button>
            </div>
        </div>

        <!-- Form Konsultasi -->
        <form id="burnoutForm" action="proses_deteksi.php" method="POST" onsubmit="return handleSubmit(event)" style="display:none">
            <div class="question-card">
                <?php foreach ($questions as $index => $q): ?>
                <div class="step <?= $index === 0 ? 'active' : '' ?>" data-step="<?= $index + 1 ?>">
                    <h2 class="question-text"><?= $q ?></h2>
                    <div class="options-group">
                        <label class="option-btn" onclick="selectOption(<?= $index + 1 ?>, 'Ya')">
                            <input type="radio" name="q<?= $index + 1 ?>" value="Ya" style="display:none">
                            <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Ya
                        </label>
                        <label class="option-btn" onclick="selectOption(<?= $index + 1 ?>, 'Tidak')">
                            <input type="radio" name="q<?= $index + 1 ?>" value="Tidak" style="display:none">
                            <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Tidak
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Summary Step -->
                <div class="step" data-step="11">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1.5rem;">📊</div>
                        <h2 class="question-text">Analisis Selesai</h2>
                        <p style="color: var(--color-gray-500); margin-bottom: 2rem;">
                            Terima kasih telah menjawab semua pertanyaan. Sistem siap menganalisis tingkat burnout Anda berdasarkan gejala yang dilaporkan.
                        </p>
                        <div style="background: var(--color-gray-50); padding: 1.5rem; border-radius: 16px; border: 1px dashed var(--color-gray-300); display: inline-block; width: 100%;">
                            <p style="font-size: 0.9rem; font-weight: 700; color: var(--color-primary);">Semua data Anda bersifat rahasia dan hanya digunakan untuk keperluan deteksi ini.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-buttons" style="display:none">
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
    const totalSteps = 11;
    const answers = {};
    let isAnimating = false;

    function startDetection() {
        if (isAnimating) return;
        document.getElementById('startScreen').style.display = 'none';
        document.getElementById('burnoutForm').style.display = 'block';
        document.getElementById('step-indicators').style.display = 'flex';
        document.querySelector('.progress-wrapper').style.display = 'block';
        document.querySelector('.nav-buttons').style.display = 'flex';
        updateUI();
    }

    function selectOption(step, val) {
        // Highlight selection
        const stepEl = document.querySelector(`.step[data-step="${step}"]`);
        const btns = stepEl.querySelectorAll('.option-btn');
        btns.forEach(btn => {
            btn.classList.remove('selected');
            if (btn.innerText.trim().includes(val)) btn.classList.add('selected');
        });

        // Store answer
        answers[step] = val;
        
        // Enable Next or Submit
        checkNav();
        
        // Update Indicator
        updateIndicators();

        // Auto next with slight delay
        if (step < totalSteps) {
            setTimeout(() => {
                if (currentStep === step) changeStep(1);
            }, 500);
        }
    }

    function changeStep(n) {
        const steps = document.querySelectorAll('.step');
        const prevStepIdx = currentStep - 1;
        const nextStepIdx = currentStep + n - 1;

        if (nextStepIdx < 0 || nextStepIdx >= totalSteps) return;

        // Apply animations
        steps[prevStepIdx].classList.remove('active', 'slide-in-right', 'slide-in-left');
        steps[prevStepIdx].classList.add(n > 0 ? 'slide-out-left' : 'slide-out-right');

        setTimeout(() => {
            steps[prevStepIdx].style.display = 'none';
            steps[prevStepIdx].classList.remove('slide-out-left', 'slide-out-right');
            
            steps[nextStepIdx].style.display = 'block';
            steps[nextStepIdx].classList.add('active', n > 0 ? 'slide-in-right' : 'slide-in-left');
            
            currentStep += n;
            updateUI();
        }, 300);
    }

    function updateUI() {
        // Update Progress
        const percent = (currentStep / totalSteps) * 100;
        document.getElementById('progress-bar').style.width = percent + '%';
        
        if (currentStep <= 10) {
            document.getElementById('progress-text').innerText = `Gejala ${currentStep} dari 10`;
        } else {
            document.getElementById('progress-text').innerText = `Konfirmasi Akhir`;
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
        
        updateIndicators();
        checkNav();
    }

    function updateIndicators() {
        const dots = document.querySelectorAll('.indicator-dot');
        dots.forEach((dot, idx) => {
            dot.classList.remove('active', 'completed');
            if (idx + 1 === currentStep) {
                dot.classList.add('active');
            } else if (answers[idx + 1]) {
                dot.classList.add('completed');
            }
        });
    }

    function checkNav() {
        if (currentStep > 10) {
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
        
        // Simulate analysis for 2 seconds
        setTimeout(() => {
            document.getElementById('burnoutForm').submit();
        }, 2000);
        
        return false;
    }

    updateUI();
</script>

</body>
</html>
