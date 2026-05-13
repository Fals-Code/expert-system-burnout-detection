@extends('layouts.app')

@section('title', 'Evaluasi Gejala – BurnoutXpert')

@section('content')
<div class="container-wizard" x-data="deteksiWizard()">
    <!-- Header Progress -->
    <div class="wizard-header">
        <div class="header-text">
            <h1 class="page-title">Sesi Diagnosis</h1>
            <p>Jawablah dengan sejujur mungkin sesuai kondisi Anda.</p>
        </div>
        <div class="progress-section">
            <div class="progress-bar-container">
                <div class="progress-bar-fill" :style="'width: ' + progressPercent + '%'"></div>
            </div>
            <div class="progress-labels">
                <span>Progress: <strong x-text="progressPercent + '%'"></strong></span>
                <span><strong x-text="answeredCount"></strong> / {{ $total_gejala }} Gejala</span>
            </div>
        </div>
    </div>

    <!-- Wizard Card -->
    <div class="content-card wizard-card">
        <form action="{{ route('karyawan.deteksi.proses') }}" method="POST" id="diagnosisForm">
            @csrf
            
            <div class="question-container">
                @foreach($questions as $index => $q)
                <div class="question-slide" 
                     x-show="currentStep === {{ $index }}" 
                     x-transition:enter="slide-in-right"
                     x-transition:leave="slide-out-left">
                    
                    <div class="q-meta">
                        <span class="q-badge">{{ strtoupper($q->kategori) }}</span>
                        <span class="q-code">ID: {{ $q->kode }}</span>
                    </div>
                    
                    <h2 class="q-text">Seberapa sering Anda merasa: <br> <span class="highlight">"{{ $q->nama }}"</span>?</h2>
                    
                    <div class="options-container">
                        @foreach($options as $val => $label)
                        <label class="option-item" :class="{'selected': answers['{{ $q->kode }}'] === '{{ $val }}'}">
                            <input type="radio" name="{{ $q->kode }}" value="{{ $val }}" 
                                   x-model="answers['{{ $q->kode }}']" required>
                            <div class="option-content">
                                <div class="radio-circle"></div>
                                <span class="option-text">{{ $label }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Footer Navigation -->
            <div class="wizard-footer">
                <div class="nav-left">
                    <button type="button" class="btn-secondary" @click="prevStep" x-show="currentStep > 0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        Sebelumnya
                    </button>
                </div>
                
                <div class="nav-right">
                    <template x-if="currentStep < {{ count($questions) - 1 }}">
                        <button type="button" class="btn-primary" @click="nextStep" :disabled="!isCurrentStepAnswered()">
                            Lanjut
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </template>
                    
                    <template x-if="currentStep === {{ count($questions) - 1 }}">
                        <button type="submit" class="btn-primary btn-finish" :disabled="!isCurrentStepAnswered() || isSubmitting">
                            <span x-show="!isSubmitting">Selesaikan Langkah Ini</span>
                            <span x-show="isSubmitting">Memproses... ⏳</span>
                        </button>
                    </template>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deteksiWizard() {
    return {
        currentStep: 0,
        totalSteps: {{ count($questions) }},
        progressPercent: Math.round(({{ $progress }} / {{ $total_gejala }}) * 100),
        answeredCount: {{ $progress }},
        answers: {},
        isSubmitting: false,
        
        isCurrentStepAnswered() {
            const currentCodes = {!! json_encode($question_codes) !!};
            const currentCode = currentCodes[this.currentStep];
            return this.answers[currentCode] !== undefined;
        },

        nextStep() {
            if (this.isCurrentStepAnswered()) {
                if (this.currentStep < this.totalSteps - 1) {
                    this.currentStep++;
                }
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },

        submitForm() {
            this.isSubmitting = true;
            document.getElementById('diagnosisForm').submit();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
    .container-wizard {
        max-width: 850px;
        margin: 2rem auto;
    }
    .wizard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }
    .progress-section {
        flex: 0 0 300px;
    }
    .progress-bar-container {
        height: 10px;
        background: #e2e8f0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .progress-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: var(--color-gray-500);
    }
    .wizard-card {
        padding: 3rem;
        min-height: 550px;
        display: flex;
        flex-direction: column;
        border-radius: 24px;
    }
    .q-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .q-badge {
        background: var(--color-primary-light);
        color: var(--color-primary);
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    .q-code {
        color: var(--color-gray-400);
        font-size: 0.8rem;
        font-weight: 600;
    }
    .q-text {
        font-size: 1.8rem;
        line-height: 1.4;
        color: var(--color-gray-800);
        margin-bottom: 3rem;
    }
    .q-text .highlight {
        color: var(--color-primary);
        display: block;
        margin-top: 0.5rem;
    }
    .options-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .option-item {
        border: 2px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .option-item:hover {
        border-color: var(--color-primary-light);
        background: #f8fafc;
    }
    .option-item.selected {
        border-color: var(--color-primary);
        background: #f0f7ff;
        box-shadow: 0 4px 15px rgba(30, 58, 95, 0.08);
    }
    .option-item input {
        position: absolute;
        opacity: 0;
    }
    .option-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .radio-circle {
        width: 22px;
        height: 22px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        position: relative;
        transition: all 0.2s ease;
    }
    .option-item.selected .radio-circle {
        border-color: var(--color-primary);
        background: var(--color-primary);
    }
    .option-item.selected .radio-circle::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
    }
    .option-text {
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--color-gray-700);
    }
    .wizard-footer {
        margin-top: auto;
        padding-top: 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn-primary {
        background: var(--color-primary);
        color: white;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-primary:hover:not(:disabled) {
        background: #2a4a7a;
        transform: translateX(3px);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-secondary {
        background: transparent;
        color: var(--color-gray-500);
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .btn-secondary:hover {
        background: #f8fafc;
        color: var(--color-gray-800);
    }

    /* Animations */
    .slide-in-right { animation: slideInRight 0.4s ease-out; }
    @keyframes slideInRight {
        from { transform: translateX(30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @media (max-width: 768px) {
        .wizard-header { flex-direction: column; align-items: flex-start; gap: 1.5rem; }
        .progress-section { flex: 0 0 auto; width: 100%; }
        .q-text { font-size: 1.4rem; }
        .wizard-card { padding: 1.5rem; min-height: auto; }
    }
</style>
@endpush
