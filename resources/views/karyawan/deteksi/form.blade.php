@extends('layouts.app')

@section('title', 'Deteksi Burnout – BurnoutXpert')

@section('content')
<div class="main-wrapper" style="margin-left: 0; padding: 0;">
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

        <!-- Form Konsultasi -->
        <form id="burnoutForm" action="{{ route('karyawan.deteksi.next') }}" method="POST" onsubmit="return handleSubmit(event)">
            @csrf
            <div class="question-card">
                <!-- Backward Chaining: Simple Phase Label -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span style="display: inline-block; padding: 0.5rem 1.5rem; background: white; border: 1px solid var(--color-gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 999px; font-size: 0.75rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.15em;">
                        {{ session('bc_engine.current_hypothesis', 'Fase 1') }}
                    </span>
                </div>

                <!-- Integrated Progress Header -->
                <div class="progress-container">
                    <div class="progress-header">
                        <span class="progress-title" id="progress-text">Gejala 1 dari {{ count($questions) }}</span>
                        <span class="progress-percentage" id="progress-percent">0%</span>
                    </div>
                    <div class="modern-progress-bar">
                        <div class="modern-progress-fill" id="progress-bar" style="width: 0%;"></div>
                    </div>
                </div>
                
                @foreach ($questions as $index => $q)
                <div class="step {{ $index === 0 ? 'active' : '' }}" data-step="{{ $index + 1 }}" style="{{ $index === 0 ? '' : 'display:none' }}">
                    <h2 class="question-text" style="font-size: 1.5rem; line-height: 1.4; margin-bottom: 2.5rem;">Seberapa sering Anda mengalami: {{ $q->nama }}?</h2>
                    <style>
                        .option-btn-yes:hover { border-color: #16a34a !important; background: #f0fdf4 !important; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(22, 163, 74, 0.15); }
                        .option-btn-yes.selected { border-color: #16a34a !important; background: #f0fdf4 !important; box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.2); }
                        
                        .option-btn-no:hover { border-color: #dc2626 !important; background: #fef2f2 !important; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(220, 38, 38, 0.15); }
                        .option-btn-no.selected { border-color: #dc2626 !important; background: #fef2f2 !important; box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.2); }
                        
                        @media (max-width: 600px) {
                            .options-group--2 { grid-template-columns: 1fr !important; }
                        }
                    </style>
                    <div class="options-group options-group--2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <label class="option-btn option-btn-yes" onclick="selectOption({{ $index + 1 }}, 'Ya', '{{ $q->kode }}')" style="padding: 2.5rem 1.5rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-radius: 16px; border: 2px solid var(--color-gray-200); cursor: pointer; transition: 0.3s; background: white;">
                            <input type="radio" name="{{ $q->kode }}" value="Ya" style="display:none">
                            <div class="icon-circle" style="width: 60px; height: 60px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <span style="font-weight: 800; font-size: 1.4rem; color: #16a34a; margin-bottom: 0.5rem;">Ya</span>
                            <span style="font-size: 0.9rem; color: var(--color-gray-500); font-weight: 500;">Saya Sering Merasakannya</span>
                        </label>

                        <label class="option-btn option-btn-no" onclick="selectOption({{ $index + 1 }}, 'Tidak', '{{ $q->kode }}')" style="padding: 2.5rem 1.5rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-radius: 16px; border: 2px solid var(--color-gray-200); cursor: pointer; transition: 0.3s; background: white;">
                            <input type="radio" name="{{ $q->kode }}" value="Tidak" style="display:none">
                            <div class="icon-circle" style="width: 60px; height: 60px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </div>
                            <span style="font-weight: 800; font-size: 1.4rem; color: #dc2626; margin-bottom: 0.5rem;">Tidak</span>
                            <span style="font-size: 0.9rem; color: var(--color-gray-500); font-weight: 500;">Saya Tidak Pernah Merasakannya</span>
                        </label>
                    </div>
                </div>
                @endforeach

                <!-- Summary Step -->
                <div class="step" data-step="{{ count($questions) + 1 }}" style="display:none">
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
        </form>
    </main>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalQuestions = {{ count($questions) }};
    const totalSteps = totalQuestions + 1;
    let answers = {};
    let isAnimating = false;
    let autoNextTimeout = null;

    const storageKey = `burnoutWizardState_{{ auth()->id() }}`;

    function saveState() {
        localStorage.setItem(storageKey, JSON.stringify({ currentStep, answers }));
    }

    function loadState() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const data = JSON.parse(saved);
            answers = data.answers || {};
            
            for (const [step, val] of Object.entries(answers)) {
                const stepEl = document.querySelector(`.step[data-step="${step}"]`);
                if (stepEl) {
                    const btns = stepEl.querySelectorAll('.option-btn');
                    btns.forEach(btn => {
                        if (btn.innerText.trim().includes(val)) btn.classList.add('selected');
                    });
                    const radio = stepEl.querySelector(`input[value="${val}"]`);
                    if (radio) radio.checked = true;
                }
            }

            if (data.currentStep > 1 && data.currentStep <= totalSteps) {
                currentStep = data.currentStep;
                updateUI(true);
            }
        }
    }

    function selectOption(step, val, gid) {
        if (isAnimating) return;
        const stepEl = document.querySelector(`.step[data-step="${step}"]`);
        const btns = stepEl.querySelectorAll('.option-btn');
        btns.forEach(btn => {
            btn.classList.remove('selected');
            if (btn.innerText.trim().includes(val)) btn.classList.add('selected');
        });

        answers[step] = val;
        const radio = stepEl.querySelector(`input[value="${val}"]`);
        if (radio) radio.checked = true;
        
        saveState();
        checkNav();

        if (step <= totalQuestions) {
            if (autoNextTimeout) clearTimeout(autoNextTimeout);
            autoNextTimeout = setTimeout(() => {
                if (currentStep === step && !isAnimating) changeStep(1);
            }, 500);
        }
    }

    function changeStep(n) {
        if (isAnimating) return;
        const steps = document.querySelectorAll('.step');
        const prevStepIdx = currentStep - 1;
        const nextStepIdx = currentStep + n - 1;

        if (nextStepIdx < 0 || nextStepIdx >= totalSteps) return;

        if (autoNextTimeout) clearTimeout(autoNextTimeout);
        isAnimating = true;

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

            const wizardContainer = document.querySelector('.wizard-container');
            if (wizardContainer) {
                wizardContainer.classList.remove('haptic-pulse');
                void wizardContainer.offsetWidth;
                wizardContainer.classList.add('haptic-pulse');
            }
            
            setTimeout(() => { isAnimating = false; }, 300);
        }, 300);
    }

    function updateUI(immediate = false) {
        const percent = Math.round((currentStep / totalSteps) * 100);
        document.getElementById('progress-bar').style.width = percent + '%';
        
        const percentEl = document.getElementById('progress-percent');
        if (percentEl) percentEl.innerText = percent + '%';
        
        if (currentStep <= totalQuestions) {
            document.getElementById('progress-text').innerText = `Gejala ${currentStep} dari ${totalQuestions}`;
        } else {
            document.getElementById('progress-text').innerText = `Analisis Selesai`;
        }
        
        document.getElementById('prevBtn').disabled = (currentStep === 1);
        
        if (currentStep === totalSteps) {
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'flex';
        } else {
            document.getElementById('nextBtn').style.display = 'flex';
            document.getElementById('submitBtn').style.display = 'none';
        }

        if (immediate) {
            const steps = document.querySelectorAll('.step');
            steps.forEach(s => {
                s.classList.remove('active');
                s.style.display = 'none';
            });
            steps[currentStep - 1].classList.add('active');
            steps[currentStep - 1].style.display = 'block';
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
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = 'flex';
        localStorage.removeItem(storageKey);
        return true;
    }

    window.addEventListener('load', () => {
        loadState();
        updateUI();
    });
</script>
@endpush

