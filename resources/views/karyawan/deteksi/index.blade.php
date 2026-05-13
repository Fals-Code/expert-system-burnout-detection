@extends('layouts.app')

@section('title', 'Deteksi Tingkat Burnout – BurnoutXpert')

@section('content')
<div class="intro-container">
    <div class="intro-card fade-in">
        <div class="intro-visual">
            <div class="brain-icon">🧠</div>
            <div class="sparkles">✨</div>
        </div>
        
        <div class="intro-content">
            <h1 class="intro-title">Bagaimana Kabar Mental Anda Hari Ini?</h1>
            <p class="intro-subtitle">
                Burnout seringkali datang tanpa disadari. Ambil waktu 5 menit untuk melakukan evaluasi mandiri menggunakan sistem pakar kami yang berbasis standar medis.
            </p>

            <div class="feature-pills">
                <div class="pill-item">
                    <span class="pill-icon">⏱️</span>
                    <span>Estimasi 5 Menit</span>
                </div>
                <div class="pill-item">
                    <span class="pill-icon">🔒</span>
                    <span>Data Privasi Terjamin</span>
                </div>
                <div class="pill-item">
                    <span class="pill-icon">📊</span>
                    <span>Hasil Akurat (MBI)</span>
                </div>
            </div>

            <div class="cta-wrapper">
                <a href="{{ route('karyawan.deteksi') }}" class="btn-start-diagnosis">
                    Mulai Diagnosis Sekarang
                    <span class="arrow-icon">→</span>
                </a>
                <p class="cta-note">*Diagnosis ini bukan pengganti konsultasi medis profesional.</p>
            </div>
        </div>
    </div>

    <div class="how-it-works shadow-sm">
        <h3>Bagaimana Prosesnya?</h3>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <h4>Jawab Pertanyaan</h4>
                <p>Jelaskan perasaan dan kondisi Anda dalam 1 bulan terakhir.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h4>Analisis Pakar</h4>
                <p>Sistem menganalisis data menggunakan algoritma Backward Chaining.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h4>Hasil & Rekomendasi</h4>
                <p>Dapatkan laporan tingkat burnout dan langkah penanganan yang tepat.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .intro-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    .intro-card {
        background: linear-gradient(135deg, var(--color-primary), #2a4a7a);
        border-radius: 30px;
        padding: 4rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 3rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 3rem;
    }
    .intro-card::after {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    .intro-visual {
        flex: 0 0 200px;
        font-size: 8rem;
        position: relative;
        display: flex;
        justify-content: center;
    }
    .sparkles {
        position: absolute;
        top: 0;
        right: 0;
        font-size: 2rem;
        animation: pulse 2s infinite;
    }
    .intro-content {
        flex: 1;
    }
    .intro-title {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }
    .intro-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        line-height: 1.6;
        margin-bottom: 2.5rem;
    }
    .feature-pills {
        display: flex;
        gap: 1rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }
    .pill-item {
        background: rgba(255, 255, 255, 0.1);
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .btn-start-diagnosis {
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        background: var(--color-accent);
        color: white;
        padding: 1.2rem 2.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.2rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
    }
    .btn-start-diagnosis:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
        background: #e6910a;
        color: white;
    }
    .cta-note {
        font-size: 0.8rem;
        margin-top: 1rem;
        opacity: 0.7;
        font-style: italic;
    }
    .how-it-works {
        background: white;
        padding: 3rem;
        border-radius: 24px;
        text-align: center;
    }
    .how-it-works h3 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 3rem;
        color: var(--color-gray-800);
    }
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    .step-card {
        padding: 1.5rem;
        position: relative;
    }
    .step-num {
        width: 40px;
        height: 40px;
        background: var(--color-primary-light);
        color: var(--color-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin: 0 auto 1.5rem;
    }
    .step-card h4 {
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .step-card p {
        font-size: 0.9rem;
        color: var(--color-gray-600);
        line-height: 1.5;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
    }

    @media (max-width: 900px) {
        .intro-card { flex-direction: column; padding: 2.5rem; text-align: center; }
        .intro-title { font-size: 2rem; }
        .steps-grid { grid-template-columns: 1fr; }
        .feature-pills { justify-content: center; }
    }
</style>
@endpush
