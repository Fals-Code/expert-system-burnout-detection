@extends('layouts.app')

@section('title', 'Check-in Kondisi Kerja – BurnoutXpert')

@section('content')
<main class="wizard-container">
    <div id="startScreen" class="question-card" style="text-align:center; max-width:860px; margin:0 auto;" data-intro="Ini adalah halaman persiapan sebelum memulai check-in kondisi kerja. Anda bisa memulai sesi baru atau melanjutkan sesi yang tersimpan." data-step="1">
        <div class="step active" style="opacity:1; transform:none;">
            <div class="finish-icon-wrapper" style="margin-bottom:1.5rem;">
                <div class="pulse-ring"></div>
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>

            <p style="display:inline-flex; align-items:center; gap:0.5rem; margin:0 0 0.75rem; padding:0.4rem 0.85rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-size:0.75rem; font-weight:900; letter-spacing:0.12em; text-transform:uppercase;">
                Check-in Mingguan
            </p>

            <h1 class="question-text" style="margin-bottom:1rem;">Check-in Kondisi Kerja</h1>
            <p style="color:var(--color-gray-500); line-height:1.75; margin-bottom:1.5rem; max-width:640px; margin-left:auto; margin-right:auto;">
                Isi singkat 3 sampai 5 menit untuk membantu melihat pola beban kerja, energi, dan dukungan yang Anda rasakan minggu ini. Tidak ada jawaban benar atau salah; pilih jawaban yang paling dekat dengan pengalaman kerja harian Anda.
            </p>

            <div style="background:#f8fafc; border:1px solid #dbeafe; border-radius:22px; padding:1.25rem; margin:0 auto 1.5rem; max-width:700px; text-align:left;">
                <div style="display:flex; align-items:flex-start; gap:0.85rem;">
                    <div style="width:42px; height:42px; border-radius:999px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-weight:900; flex-shrink:0;">✓</div>
                    <div>
                        <h3 style="margin:0 0 0.45rem; color:var(--color-gray-800); font-size:1rem; font-weight:900;">Privasi Jawaban</h3>
                        <p style="margin:0; color:var(--color-gray-500); font-size:0.88rem; line-height:1.7;">
                            Jawaban Anda bersifat rahasia dan digunakan untuk membantu membaca kebutuhan dukungan kerja. Tampilan untuk pihak pengelola diarahkan sebagai rekap sesuai hak akses, bukan sebagai ruang untuk menghakimi jawaban personal.
                        </p>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; margin:0 auto 1.75rem; max-width:760px; text-align:left;">
                <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:1rem; box-shadow:0 8px 18px rgba(15,23,42,0.04);">
                    <div style="font-weight:900; color:var(--color-gray-800); font-size:0.9rem;">Beban Kerja</div>
                    <p style="margin:0.25rem 0 0; color:var(--color-gray-500); font-size:0.78rem; line-height:1.5;">Membaca pola tugas dan tekanan kerja yang dirasakan.</p>
                </div>
                <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:1rem; box-shadow:0 8px 18px rgba(15,23,42,0.04);">
                    <div style="font-weight:900; color:var(--color-gray-800); font-size:0.9rem;">Energi Kerja</div>
                    <p style="margin:0.25rem 0 0; color:var(--color-gray-500); font-size:0.78rem; line-height:1.5;">Melihat kondisi energi dan fokus dalam aktivitas harian.</p>
                </div>
                <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:1rem; box-shadow:0 8px 18px rgba(15,23,42,0.04);">
                    <div style="font-weight:900; color:var(--color-gray-800); font-size:0.9rem;">Dukungan Kerja</div>
                    <p style="margin:0.25rem 0 0; color:var(--color-gray-500); font-size:0.78rem; line-height:1.5;">Mengenali area dukungan yang mungkin dibutuhkan.</p>
                </div>
            </div>

            @if(isset($savedSession))
            <div style="background:rgba(59,130,246,0.05); border:1.5px dashed #3b82f6; border-radius:16px; padding:1.5rem; margin-bottom:2rem; text-align:left; display:flex; flex-direction:column; gap:1rem; max-width:560px; margin-left:auto; margin-right:auto; box-shadow:var(--shadow-sm);">
                <div>
                    <h4 style="margin:0; color:var(--color-gray-800); font-weight:800; font-size:0.95rem;">Sesi Check-in Tersimpan</h4>
                    <p style="margin:0.25rem 0 0 0; color:var(--color-gray-500); font-size:0.85rem;">Anda memiliki progres check-in kerja yang disimpan pada {{ $savedSession->updated_at->format('d M Y, H:i') }}.</p>
                </div>
                <div style="display:flex; gap:1rem; margin-top:0.25rem; flex-wrap:wrap;">
                    <form action="{{ route('karyawan.deteksi.resume') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-nav btn-result" style="padding:0.65rem 1.25rem; font-size:0.85rem; background:#3b82f6; color:white; display:flex; align-items:center; cursor:pointer; border:none; border-radius:50px;">
                            Lanjutkan Check-in
                        </button>
                    </form>
                    <a href="{{ route('karyawan.deteksi.reset') }}" class="btn-nav btn-prev" style="padding:0.65rem 1.25rem; font-size:0.85rem; border-color:var(--color-gray-300); display:flex; align-items:center; text-decoration:none; border-radius:50px;">
                        Mulai dari Awal
                    </a>
                </div>
            </div>
            @else
            <form action="{{ route('karyawan.deteksi') }}" method="GET" style="margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:1rem;">
                <label style="display:flex; align-items:flex-start; gap:0.6rem; max-width:620px; text-align:left; color:var(--color-gray-600); font-size:0.86rem; line-height:1.6; cursor:pointer;">
                    <input type="checkbox" required style="margin-top:0.25rem; accent-color:#2563eb;">
                    <span>Saya memahami bahwa check-in ini bertujuan membantu membaca kondisi kerja dan kebutuhan dukungan, bukan untuk memberi label personal.</span>
                </label>
                <button type="submit" class="btn-nav btn-result" style="margin:0 auto; padding:1.25rem 3rem; display:inline-flex; align-items:center; justify-content:center; max-width:max-content; border:none; cursor:pointer;" data-intro="Klik tombol ini untuk memulai check-in kondisi kerja. Anda akan menjawab indikator harian secara singkat." data-step="2">
                    Mulai Check-in
                </button>
            </form>
            @endif
        </div>
    </div>
</main>

<style>
    @media (max-width: 768px) {
        #startScreen [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
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
