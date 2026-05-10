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
    let answers = {};
    let isAnimating = false;
    let autoNextTimeout = null;

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
                document.getElementById('step-indicators').style.display = 'flex';
                document.querySelector('.progress-wrapper').style.display = 'block';
                document.querySelector('.nav-buttons').style.display = 'flex';

                // Jump to the current step without animation
                const steps = document.querySelectorAll('.step');
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

        // Store answer & check radio
        answers[step] = val;
        const radio = stepEl.querySelector(`input[value="${val}"]`);
        if (radio) radio.checked = true;
        
        saveState();

        // Enable Next or Submit
        checkNav();
        
        // Update Indicator
        updateIndicators();

        // Auto next with slight delay
        if (step < totalSteps) {
            if (autoNextTimeout) clearTimeout(autoNextTimeout);
            autoNextTimeout = setTimeout(() => {
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
            saveState();
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
