@extends('layouts.app')

@section('title', 'Deteksi Burnout – BurnoutXpert')

@section('content')
<main class="wizard-container">
    <!-- Welcome Step -->
    <div id="startScreen" class="question-card" style="text-align: center;" data-intro="Ini adalah halaman persiapan sebelum memulai deteksi burnout. Di sini Anda bisa memulai sesi baru atau melanjutkan sesi yang tersimpan." data-step="1">
        <div class="step active" style="opacity: 1; transform: none;">
            <div class="finish-icon-wrapper" style="margin-bottom: 2rem;">
                <div class="pulse-ring"></div>
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <h1 class="question-text" style="margin-bottom: 1rem;">Mulai Deteksi Burnout</h1>
            <p style="color: var(--color-gray-500); line-height: 1.6; margin-bottom: 2.5rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                Sistem pakar kami akan menganalisis kondisi kesehatan mental Anda melalui serangkaian pertanyaan klinis. Mohon jawab dengan jujur untuk hasil yang paling akurat.
            </p>

            @if(isset($savedSession))
            <div style="background: rgba(59, 130, 246, 0.05); border: 1.5px dashed #3b82f6; border-radius: 16px; padding: 1.5rem; margin-bottom: 2.5rem; text-align: left; display: flex; flex-direction: column; gap: 1rem; max-width: 500px; margin-left: auto; margin-right: auto; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: var(--color-gray-800); font-weight: 800; font-size: 0.95rem;">Sesi Tersimpan Ditemukan!</h4>
                        <p style="margin: 0.25rem 0 0 0; color: var(--color-gray-500); font-size: 0.85rem;">Anda memiliki progres deteksi burnout yang disimpan pada {{ $savedSession->updated_at->format('d M Y, H:i') }} ({{ count($savedSession->answers) }} gejala terjawab).</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 0.25rem;">
                    <form action="{{ route('karyawan.deteksi.resume') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-nav btn-result" style="padding: 0.65rem 1.25rem; font-size: 0.85rem; background: #3b82f6; color: white; display: flex; align-items: center; cursor: pointer; border: none; border-radius: 50px;">
                            Lanjutkan Sesi
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 0.25rem;"><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </form>
                    <a href="{{ route('karyawan.deteksi.reset') }}" class="btn-nav btn-prev" style="padding: 0.65rem 1.25rem; font-size: 0.85rem; border-color: var(--color-gray-300); display: flex; align-items: center; text-decoration: none; border-radius: 50px;">
                        Mulai dari Awal
                    </a>
                </div>
            </div>
            @else
            <a href="{{ route('karyawan.deteksi') }}" class="btn-nav btn-result" style="margin: 0 auto; padding: 1.25rem 3rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; max-width: max-content;" data-intro="Klik tombol ini untuk memulai proses deteksi burnout. Anda akan menjawab serangkaian pertanyaan klinis." data-step="2">
                Mulai Analisis Sekarang
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            @endif
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.OnboardingHelper && window.OnboardingHelper.shouldShow('karyawan_deteksi_intro')) {
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
