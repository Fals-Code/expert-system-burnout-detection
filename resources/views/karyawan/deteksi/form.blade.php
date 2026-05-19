@extends('layouts.app')

@section('title', 'Deteksi Burnout – BurnoutXpert')

@section('content')
<style>
    .likert-option {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        border: 2px solid var(--color-gray-200);
        background: white;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: left;
        position: relative;
    }
    .likert-option:hover {
        transform: translateY(-2px);
        border-color: var(--color-primary);
        background: rgba(59, 130, 246, 0.02);
        box-shadow: var(--shadow-md);
    }
    .likert-option.selected {
        border-color: var(--color-primary) !important;
        background: rgba(59, 130, 246, 0.05) !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
    }
    .likert-emoji {
        font-size: 2rem;
        line-height: 1;
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }
    .likert-option:hover .likert-emoji {
        transform: scale(1.15) rotate(-5deg);
    }
    .likert-meta {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .likert-label {
        font-size: 1rem;
        font-weight: 800;
        color: var(--color-gray-800);
    }
    .likert-desc {
        font-size: 0.8rem;
        color: var(--color-gray-500);
        font-weight: 500;
        line-height: 1.4;
    }
    
    @media (max-width: 600px) {
        .likert-option {
            padding: 1rem;
            gap: 1rem;
        }
        .likert-emoji {
            font-size: 1.75rem;
        }
        .likert-label {
            font-size: 0.95rem;
        }
        .likert-desc {
            font-size: 0.75rem;
        }
        .nav-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }
        .nav-buttons button, .nav-buttons a {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="main-wrapper" style="margin-left: 0; padding: 0;">
    <main class="wizard-container">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-content">
                <div class="spinner-container">
                    <div class="spinner-pulse"></div>
                    <span class="spinner-icon">
                        <svg class="animate-spin" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 2s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></svg>
                    </span>
                </div>
                <p class="loading-text">Menganalisis Jawaban Anda...</p>
                <p style="font-size: 0.875rem; color: var(--color-gray-400); margin-top: 0.5rem;">Sistem pakar sedang memproses data klinis...</p>
            </div>
        </div>

        <!-- Form Konsultasi -->
        <form id="burnoutForm" action="{{ route('karyawan.deteksi.next') }}" method="POST" onsubmit="return handleSubmit(event)">
            @csrf
            <div class="question-card">

                <!-- Integrated Progress Header -->
                @php
                    $initial_percent = count($questions) > 0 ? min(round((1 / count($questions)) * 100), 100) : 0;
                @endphp
                <div class="progress-container">
                    <div class="progress-header">
                        <span class="progress-title" id="progress-text">Gejala 1 dari {{ count($questions) }}</span>
                        <span class="progress-percentage" id="progress-percent">{{ $initial_percent }}%</span>
                    </div>
                    <div class="modern-progress-bar">
                        <div class="modern-progress-fill" id="progress-bar" style="width: {{ $initial_percent }}%;"></div>
                    </div>
                </div>
                
                @foreach ($questions as $index => $q)
                <div class="step {{ $index === 0 ? 'active' : '' }}" data-step="{{ $index + 1 }}" data-gid="{{ $q->kode }}" style="{{ $index === 0 ? '' : 'display:none' }}">
                    <h2 class="question-text" style="font-size: 1.35rem; line-height: 1.5; margin-bottom: 2rem; color: var(--color-gray-800); text-align: center; font-weight: 800;">
                        {{ $q->nama }}
                    </h2>
                    
                    <div class="likert-group" style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 2rem;">
                        @foreach([
                            'Sangat Sering' => ['😫', 'Sangat Sering / Selalu', 'Hampir setiap hari & sangat mengganggu kondisi saya'],
                            'Sering'        => ['😩', 'Sering Merasakan', 'Beberapa kali dalam seminggu, cukup menguras energi'],
                            'Kadang'        => ['😐', 'Kadang-Kadang', 'Sekali-sekali saja dalam seminggu atau saat beban bertumpuk'],
                            'Jarang'        => ['🙁', 'Jarang Sekali', 'Hanya sekali dalam sebulan atau kondisi tertentu'],
                            'Sangat Jarang' => ['🙂', 'Sangat Jarang', 'Hampir tidak pernah merasakannya sama sekali'],
                            'Tidak'         => ['😊', 'Tidak Pernah', 'Sama sekali tidak pernah saya rasakan belakangan ini']
                        ] as $value => $meta)
                        <label class="likert-option" onclick="selectOption({{ $index + 1 }}, '{{ $value }}', '{{ $q->kode }}')">
                            <input type="radio" name="{{ $q->kode }}" value="{{ $value }}" style="display:none">
                            <span class="likert-emoji">{{ $meta[0] }}</span>
                            <div class="likert-meta">
                                <span class="likert-label">{{ $meta[1] }}</span>
                                <span class="likert-desc">{{ $meta[2] }}</span>
                            </div>
                        </label>
                        @endforeach
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
            <div class="nav-buttons" style="display: flex; gap: 1rem; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn-nav btn-prev" id="prevBtn" onclick="changeStep(-1)" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Sebelumnya
                    </button>
                    <button type="button" class="btn-nav btn-prev" id="saveLaterBtn" onclick="handleSaveLater()" style="border-color: var(--color-primary); color: var(--color-primary); font-weight: 700;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Simpan Progres
                    </button>
                </div>
                
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

<!-- Hidden form for saving state securely to DB -->
<form id="saveLaterForm" action="{{ route('karyawan.deteksi.save') }}" method="POST" style="display:none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalQuestions = {{ count($questions) }};
    const totalSteps = totalQuestions + 1;
    const totalGejala = {{ $total_gejala }};
    const initialProgress = {{ $progress }};
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
            
            // Re-hydrate radio buttons and classes
            for (const [gid, val] of Object.entries(answers)) {
                const radio = document.querySelector(`input[name="${gid}"][value="${val}"]`);
                if (radio) {
                    radio.checked = true;
                    const parent = radio.closest('.likert-option');
                    if (parent) {
                        parent.classList.add('selected');
                    }
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
        
        // Deselect all
        const btns = stepEl.querySelectorAll('.likert-option');
        btns.forEach(btn => btn.classList.remove('selected'));
        
        // Select clicked
        const clickedRadio = stepEl.querySelector(`input[value="${val}"]`);
        if (clickedRadio) {
            clickedRadio.checked = true;
            const parent = clickedRadio.closest('.likert-option');
            if (parent) parent.classList.add('selected');
        }

        answers[gid] = val;
        
        saveState();
        checkNav();

        if (step <= totalQuestions) {
            if (autoNextTimeout) clearTimeout(autoNextTimeout);
            autoNextTimeout = setTimeout(() => {
                if (currentStep === step && !isAnimating) changeStep(1);
            }, 450);
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
        }, 250);
    }

    function updateUI(immediate = false) {
        // Calculate progress percentage of the current wizard page questions
        const percent = Math.min(Math.round((currentStep / totalQuestions) * 100), 100);
        
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
        
        // Find if the current step question has an answer in the answers object
        const stepEl = document.querySelector(`.step[data-step="${currentStep}"]`);
        const gid = stepEl ? stepEl.getAttribute('data-gid') : null;
        const hasAnswer = gid && !!answers[gid];
        
        document.getElementById('nextBtn').disabled = !hasAnswer;
        document.getElementById('submitBtn').disabled = !hasAnswer;
    }

    function handleSaveLater() {
        const form = document.getElementById('saveLaterForm');
        form.innerHTML = '@csrf'; // Reset to prevent double inputs
        
        for (const [gid, val] of Object.entries(answers)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = gid;
            input.value = val;
            form.appendChild(input);
        }

        // Also add other completed answers from session if there are any
        if (typeof showLoader === 'function') {
            showLoader('Menyimpan progres deteksi...');
        }
        
        setTimeout(() => {
            form.submit();
            localStorage.removeItem(storageKey);
        }, 400);
    }

    function handleSubmit(e) {
        if (typeof showLoader === 'function') {
            showLoader('Menganalisis data klinis...');
        } else {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'flex';
        }
        localStorage.removeItem(storageKey);
        return true;
    }

    window.addEventListener('load', () => {
        loadState();
        updateUI();
    });
</script>
@endpush
