@extends('layouts.app')

@section('title', 'Wizard Deteksi Burnout - SanctuaryHub')

@section('content')
<div class="container-wizard" x-data="deteksiWizard()">
    <!-- Header Progress -->
    <div class="wizard-header">
        <h1 class="page-title">Deteksi Kesehatan Mental</h1>
        <div class="progress-container" data-intro="Di sini Anda dapat melihat progres pengisian kuesioner. Pastikan semua gejala terjawab untuk hasil yang maksimal." data-step="1">
            <div class="progress-bar-wrapper">
                <div class="progress-bar-fill" :style="'width: ' + progressPercent + '%'"></div>
            </div>
            <div class="progress-info">
                <span>Progress: <strong x-text="progressPercent + '%'"></strong></span>
                <span>Gejala Terjawab: <strong x-text="answeredCount"></strong> / {{ $total_gejala }}</span>
            </div>
        </div>
    </div>

    <!-- Wizard Form -->
    <div class="content-card wizard-card" data-intro="Baca pertanyaan dengan saksama dan pilih opsi jawaban yang paling sesuai dengan kondisi Anda." data-step="2">
        <form action="{{ route('karyawan.deteksi.proses') }}" method="POST">
            @csrf
            
            <div class="question-list">
                @foreach($questions as $index => $q)
                <div class="question-item" :class="{'active': currentStep === {{ $index }}}" x-show="currentStep === {{ $index }}" x-transition:enter="fade-in">
                    <div class="question-meta">
                        <span class="category-badge">{{ strtoupper($q->kategori) }}</span>
                        <span class="question-number">Gejala #{{ $q->kode }}</span>
                    </div>
                    
                    <h2 class="question-text">Seberapa sering Anda merasa: <br> <strong>"{{ $q->nama }}"</strong>?</h2>
                    
                    <div class="options-grid">
                        @foreach($options as $val => $label)
                        <label class="option-card" :class="{'selected': answers['{{ $q->kode }}'] === '{{ $val }}'}">
                            <input type="radio" name="{{ $q->kode }}" value="{{ $val }}" x-model="answers['{{ $q->kode }}']" required>
                            <span class="option-label">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Navigation Buttons -->
            <div class="wizard-footer">
                <button type="button" class="btn-nav" @click="prevStep" x-show="currentStep > 0" style="display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Kembali
                </button>
                <div style="flex-grow: 1;"></div>
                <button type="button" class="btn-cta" @click="nextStep" x-show="currentStep < {{ count($questions) - 1 }}" style="display: inline-flex; align-items: center; gap: 8px;">
                    Lanjut
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
                <button type="submit" class="btn-cta btn-submit" x-show="currentStep === {{ count($questions) - 1 }}" :disabled="!isCurrentStepAnswered()" data-intro="Setelah menjawab semua pertanyaan, klik tombol ini untuk mengirim dan melihat hasil analisis pakar." data-step="3">
                    Selesaikan Langkah Ini
                </button>
            </div>
        </form>
    </div>

    <!-- Help Tooltip -->
    <div class="help-section">
        <div class="help-icon" style="color: #d97706; display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"></path><path d="M10 22h4"></path><path d="M15 13a5 5 0 0 0 3-4.5 4.5 4.5 0 0 0-9 0 4.5 4.5 0 0 0 3 4.5V15h3v-2z"></path></svg>
        </div>
        <p>Jawablah dengan jujur berdasarkan perasaan Anda dalam <strong>1 bulan terakhir</strong> untuk hasil yang akurat.</p>
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
        
        isCurrentStepAnswered() {
            const currentCodes = {!! json_encode(collect($questions)->pluck('kode')) !!};
            const currentCode = currentCodes[this.currentStep];
            return this.answers[currentCode] !== undefined;
        },

        nextStep() {
            if (this.isCurrentStepAnswered()) {
                if (this.currentStep < this.totalSteps - 1) {
                    this.currentStep++;
                }
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ops!',
                    text: 'Harap pilih salah satu opsi sebelum melanjutkan.',
                    confirmButtonColor: 'var(--color-primary)'
                });
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        }
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.OnboardingHelper && window.OnboardingHelper.shouldShow('karyawan_deteksi_wizard')) {
        setTimeout(() => {
            introJs().setOptions({
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Mengerti',
                showStepNumbers: true,
                showBullets: true,
                overlayOpacity: 0.6
            }).start();
        }, 1200);
    }
});
</script>
@endpush

@push('styles')
<style>
    .container-wizard {
        max-width: 800px;
        margin: 0 auto;
    }
    .wizard-header {
        margin-bottom: 2rem;
    }
    .progress-container {
        background: white;
        padding: 1.25rem;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
    }
    .progress-bar-wrapper {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
        transition: width 0.5s ease;
    }
    .progress-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: var(--color-gray-600);
    }
    .wizard-card {
        min-height: 450px;
        display: flex;
        flex-direction: column;
        padding: 2.5rem;
    }
    .question-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .category-badge {
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.25rem 0.75rem;
        background: var(--color-primary-light);
        color: var(--color-primary);
        border-radius: 20px;
    }
    .question-number {
        font-size: 0.8rem;
        color: var(--color-gray-400);
        font-weight: 600;
    }
    .question-text {
        font-size: 1.5rem;
        color: var(--color-gray-800);
        margin-bottom: 2.5rem;
        line-height: 1.4;
    }
    .options-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .option-card {
        border: 2px solid #e2e8f0;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .option-card:hover {
        border-color: var(--color-primary-light);
        background: #f8fafc;
    }
    .option-card.selected {
        border-color: var(--color-primary);
        background: #eff6ff;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.1);
    }
    .option-card input {
        position: absolute;
        opacity: 0;
    }
    .option-label {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-gray-700);
    }
    .wizard-footer {
        margin-top: auto;
        padding-top: 2.5rem;
        display: flex;
        align-items: center;
    }
    .help-section {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 12px;
    }
    .help-icon {
        font-size: 1.5rem;
    }
    .help-section p {
        font-size: 0.85rem;
        color: #92400e;
        margin: 0;
    }
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 640px) {
        .options-grid { grid-template-columns: 1fr; }
        .wizard-card { padding: 1.5rem; }
    }
</style>
@endpush
