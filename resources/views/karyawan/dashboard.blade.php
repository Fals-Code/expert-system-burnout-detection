@extends('layouts.app')

@section('title', 'Dashboard Karyawan – BurnoutXpert')

@section('content')
    <div class="welcome-banner" data-intro="Selamat datang di Dashboard Karyawan! Di sini Anda dapat melihat ringkasan aktivitas dan kondisi kesehatan mental Anda." data-step="1">
        <div class="welcome-content">
            <h1 class="welcome-title">{{ $greet }}, {{ Auth::user()->nama }}! 👋</h1>
            <p class="welcome-subtitle">Bagaimana perasaan Anda hari ini? Lakukan deteksi rutin untuk menjaga keseimbangan kesehatan mental Anda.</p>
            <div style="margin-top: 1.5rem;">
                <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" data-intro="Klik tombol ini kapan saja Anda merasa lelah secara fisik maupun emosional untuk memulai tes." data-step="2">Mulai Deteksi Sekarang</a>
            </div>
        </div>
        <div class="welcome-illustration">
            <svg width="200" height="150" viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="75" r="70" fill="white" fill-opacity="0.1"/>
                <path d="M140 90C140 112.091 122.091 130 100 130C77.9086 130 60 112.091 60 90C60 67.9086 77.9086 50 100 50C122.091 50 140 67.9086 140 90Z" fill="white" fill-opacity="0.2"/>
                <path d="M100 70V100M85 85H115" stroke="white" stroke-width="8" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Stat 1: Total Deteksi -->
        <div class="content-card stat-card" data-intro="Pantau seberapa sering Anda melakukan deteksi di sini." data-step="3">
            <div class="stat-icon" style="background: var(--color-primary-50); color: var(--color-primary);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $total_deteksi }}</div>
                <div class="stat-label">Total Deteksi Anda</div>
            </div>
        </div>

        <!-- Stat 2: Deteksi Terakhir -->
        <div class="content-card stat-card">
            <div class="stat-icon" style="background: #F0FFF4; color: #10B981;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="font-size: 1.1rem; line-height: 1.4;">
                    {{ $last_result ? $last_result->created_at->translatedFormat('d M Y') : 'Belum Ada' }}
                </div>
                <div class="stat-label">Terakhir Dicek</div>
            </div>
        </div>

        <!-- Stat 3: Status Terakhir -->
        <div class="content-card stat-card" data-intro="Status terakhir Anda akan selalu tampil di sini. Jaga agar tetap pada kondisi Normal!" data-step="4">
            <div class="stat-icon" style="background: {{ $last_result->diagnosa->bg_light ?? '#F8FAFB' }}; color: {{ $last_result->diagnosa->color ?? 'var(--color-gray-400)' }};">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color: {{ $last_result->diagnosa->color ?? 'inherit' }};">
                    {{ $last_result ? $last_result->diagnosa->nama : 'N/A' }}
                </div>
                <div class="stat-label">Status Terakhir</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Info Card -->
        <div class="content-card">
            <h2 class="card-title">Mengapa Harus Deteksi Dini?</h2>
            <div style="line-height: 1.6; color: var(--color-gray-600);">
                <p>Burnout adalah kondisi kelelahan fisik, emosional, dan mental yang disebabkan oleh keterlibatan jangka panjang dalam situasi yang menuntut secara emosional.</p>
                <ul style="margin-top: 1rem; padding-left: 1.25rem;">
                    <li style="margin-bottom: 0.5rem;"><strong>Akurasi Tinggi:</strong> Menggunakan algoritma Certainty Factor.</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Privasi Terjamin:</strong> Data Anda hanya digunakan untuk analisis internal.</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Solusi Cepat:</strong> Rekomendasi langsung dari para ahli HR.</li>
                </ul>
            </div>
        </div>

        <!-- Quick Tip Card -->
        <div class="content-card" style="background: var(--color-accent); color: white;">
            <h2 class="card-title" style="color: white;">💡 Tips Hari Ini</h2>
            <p style="font-size: 0.95rem; line-height: 1.6; opacity: 0.9;">
                "Luangkan waktu 5 menit setiap jam untuk sekadar meregangkan tubuh atau menjauh dari layar komputer. Hal kecil ini sangat membantu menjaga fokus Anda."
            </p>
            <div style="margin-top: 1.5rem; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05rem; text-transform: uppercase;">
                #MentalHealthMatters
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('tour_completed_karyawan')) {
        setTimeout(() => {
            introJs().setOptions({
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Mengerti',
                showStepNumbers: true,
                showBullets: true,
                overlayOpacity: 0.6
            }).start().oncomplete(function() {
                localStorage.setItem('tour_completed_karyawan', 'true');
            }).onexit(function() {
                localStorage.setItem('tour_completed_karyawan', 'true');
            });
        }, 500);
    }
});
</script>
@endpush
