@extends('layouts.app')

@section('title', 'Deteksi Burnout – BurnoutXpert')

@section('content')
<main class="wizard-container">
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
            <a href="{{ route('karyawan.deteksi') }}" class="btn-nav btn-result" style="margin: 0 auto; padding: 1.25rem 3rem; text-decoration: none;">
                Mulai Analisis Sekarang
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>
    </div>
</main>
@endsection

