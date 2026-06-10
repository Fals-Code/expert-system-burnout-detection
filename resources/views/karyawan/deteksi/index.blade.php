@extends('layouts.app')

@section('title', 'Mulai Check-in – Ruang Check-in')

@section('content')
<main class="wizard-container">
    <div id="startScreen" class="question-card" style="text-align:center; max-width:680px; margin:0 auto; padding:2.25rem;" data-intro="Mulai check-in kerja singkat dari halaman ini." data-step="1">
        <div class="step active" style="opacity:1; transform:none;">
            <div style="width:64px; height:64px; border-radius:24px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; font-size:1.75rem; font-weight:900;">
                ✓
            </div>

            <p style="display:inline-flex; align-items:center; margin:0 0 0.75rem; padding:0.35rem 0.75rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-size:0.74rem; font-weight:900; letter-spacing:0.08em; text-transform:uppercase;">
                3–5 menit
            </p>

            <h1 class="question-text" style="margin-bottom:0.75rem;">Check-in Kerja</h1>
            <p style="color:var(--color-gray-500); line-height:1.7; margin:0 auto 1.25rem; max-width:520px;">
                Jawab beberapa pertanyaan singkat tentang beban kerja, energi, dan dukungan yang Anda rasakan minggu ini.
            </p>

            <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:0.75rem; margin:0 auto 1.5rem; max-width:540px; text-align:center;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:0.85rem; color:#475569; font-size:0.82rem; font-weight:800;">Rahasia</div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:0.85rem; color:#475569; font-size:0.82rem; font-weight:800;">Tidak dinilai</div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:0.85rem; color:#475569; font-size:0.82rem; font-weight:800;">Bisa disimpan</div>
            </div>

            @if(isset($savedSession))
                <div style="background:#eff6ff; border:1px dashed #3b82f6; border-radius:16px; padding:1rem; margin:0 auto 1.25rem; text-align:left; max-width:520px;">
                    <h4 style="margin:0 0 0.25rem; color:#1e3a8a; font-weight:900; font-size:0.95rem;">Ada sesi tersimpan</h4>
                    <p style="margin:0; color:#64748b; font-size:0.85rem; line-height:1.6;">Terakhir disimpan {{ $savedSession->updated_at->format('d M Y, H:i') }}.</p>
                </div>
                <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
                    <form action="{{ route('karyawan.deteksi.resume') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-nav btn-result" style="padding:0.8rem 1.4rem; border:none; border-radius:999px; cursor:pointer;">Lanjutkan</button>
                    </form>
                    <a href="{{ route('karyawan.deteksi.reset') }}" class="btn-nav btn-prev" style="padding:0.8rem 1.4rem; border-radius:999px; text-decoration:none;">Mulai Baru</a>
                </div>
            @else
                <a href="{{ route('karyawan.deteksi') }}" class="btn-nav btn-result" style="display:inline-flex; align-items:center; justify-content:center; padding:1rem 2.25rem; border-radius:999px; text-decoration:none; border:none;" data-intro="Klik untuk mulai menjawab check-in kerja." data-step="2">
                    Mulai Check-in
                </a>
            @endif
        </div>
    </div>
</main>

<style>
    @media (max-width: 768px) {
        #startScreen { padding:1.4rem !important; }
        #startScreen [style*="grid-template-columns"] { grid-template-columns:1fr !important; }
    }
</style>
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
