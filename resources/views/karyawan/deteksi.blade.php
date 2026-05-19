@extends('layouts.app')

@section('title', 'Deteksi Burnout – BurnoutXpert')

@section('content')
    @if(session('info'))
        <div class="alert" style="margin-bottom: 1rem; padding: 1rem 1.25rem; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 10px; color: #1e40af; font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                {{ session('info') }}
            </div>
        </div>
    @endif
    <main class="wizard-container">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay" style="display: none;">
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
        <form id="burnoutForm" action="{{ route('karyawan.deteksi.proses') }}" method="POST" onsubmit="return handleSubmit(event)" style="display:none">
            @csrf
            <div class="question-card">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span style="display: inline-block; padding: 0.5rem 1.5rem; background: white; border: 1px solid var(--color-gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 999px; font-size: 0.75rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.15em;">
                        {{ $current_hypothesis }}
                    </span>
                </div>

                <div class="progress-container">
                    <div class="progress-header">
                        <span class="progress-title" id="progress-text">Gejala 1 dari {{ $total_questions }}</span>
                        <span class="progress-percentage" id="progress-percent">0%</span>
                    </div>
                    <div class="modern-progress-bar">
                        <div class="modern-progress-fill" id="progress-bar" style="width: 0%;"></div>
                    </div>
                </div>
                
                @php $step_idx = 1; @endphp
                @foreach ($questions as $gid => $q_text)
                <div class="step {{ $step_idx === 1 ? 'active' : '' }}" data-step="{{ $step_idx }}" style="{{ $step_idx === 1 ? '' : 'display:none' }}">
                    <h2 class="question-text" style="font-size: 1.5rem; line-height: 1.4; margin-bottom: 2.5rem;">{{ $q_text }}</h2>
                    <div class="options-group options-group--3">
                        <label class="option-btn" onclick="selectOption({{ $step_idx }}, 'Sering', '{{ $gid }}')">
                            <input type="radio" name="{{ $gid }}" value="Sering" style="display:none">
                            <span style="font-weight: 700; font-size: 1.1rem;">Sering Merasakan</span>
                        </label>
                        <label class="option-btn" onclick="selectOption({{ $step_idx }}, 'Kadang', '{{ $gid }}')">
                            <input type="radio" name="{{ $gid }}" value="Kadang" style="display:none">
                            <span style="font-weight: 700; font-size: 1.1rem;">Kadang-kadang</span>
                        </label>
                        <label class="option-btn" onclick="selectOption({{ $step_idx }}, 'Tidak Pernah', '{{ $gid }}')">
                            <input type="radio" name="{{ $gid }}" value="Tidak Pernah" style="display:none">
                            <span style="font-weight: 700; font-size: 1.1rem;">Tidak Pernah</span>
                        </label>
                    </div>
                </div>
                @php $step_idx++; @endphp
                @endforeach

                <!-- Summary Step -->
                <div class="step" data-step="{{ $step_idx }}" style="display:none">
                    <div class="finish-screen">
                        <div class="finish-icon-wrapper">
                            <div class="pulse-ring"></div>
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h2 class="question-text">Analisis Selesai</h2>
                        <p class="finish-subtitle">
                            Terima kasih telah menjawab semua pertanyaan. Sistem siap menganalisis tingkat burnout Anda berdasarkan gejala yang dilaporkan.
                        </p>
                    </div>
                </div>

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
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="{{ route('karyawan.deteksi.reset') }}" onclick="return confirm('Batalkan deteksi yang sedang berlangsung? Semua jawaban akan hilang.')" style="font-size: 0.8rem; color: var(--color-gray-400); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; justify-content: center;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Batalkan Deteksi
                    </a>
                </div>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalQuestions = {{ $total_questions }};
    const totalSteps = totalQuestions + 1;
    let answers = {};
    let isAnimating = false;

    function startDetection() {
        document.getElementById('startScreen').style.display = 'none';
        document.getElementById('burnoutForm').style.display = 'block';
        updateUI();
    }

    function selectOption(step, val, gid) {
        const stepEl = document.querySelector(`.step[data-step="${step}"]`);
        const btns = stepEl.querySelectorAll('.option-btn');
        btns.forEach(btn => btn.classList.remove('selected'));
        
        const targetBtn = Array.from(btns).find(btn => btn.innerText.includes(val));
        if (targetBtn) targetBtn.classList.add('selected');

        answers[step] = val;
        const radio = stepEl.querySelector(`input[value="${val}"]`);
        if (radio) radio.checked = true;

        updateUI();
        if (step < totalSteps) {
            setTimeout(() => {
                if (currentStep === step) changeStep(1);
            }, 400);
        }
    }

    function changeStep(n) {
        if (isAnimating) return;
        const steps = document.querySelectorAll('#burnoutForm .step');
        const nextStepIdx = currentStep + n;

        if (nextStepIdx < 1 || nextStepIdx > totalSteps) return;

        steps[currentStep - 1].style.display = 'none';
        steps[nextStepIdx - 1].style.display = 'block';
        
        currentStep = nextStepIdx;
        updateUI();
    }

    function updateUI() {
        const percent = Math.round(((currentStep - 1) / totalQuestions) * 100);
        document.getElementById('progress-bar').style.width = percent + '%';
        document.getElementById('progress-percent').innerText = percent + '%';
        
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
            document.getElementById('nextBtn').disabled = !answers[currentStep];
        }
    }

    function handleSubmit(e) {
        document.getElementById('loadingOverlay').style.display = 'flex';
        return true;
    }
</script>
@endpush
