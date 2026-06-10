@extends('layouts.app')

@section('title', 'Riwayat Check-in Saya – BurnoutXpert')

@section('content')
<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;" data-intro="Halaman ini menampilkan catatan check-in pribadi Anda sebagai bahan refleksi kerja dari waktu ke waktu." data-step="1">
    <div>
        <h1 class="page-title" style="margin:0 0 0.5rem;">Riwayat Check-in Saya</h1>
        <p style="margin:0; color:var(--color-gray-500); line-height:1.7; max-width:680px;">
            Gunakan halaman ini untuk melihat perkembangan kondisi kerja pribadi Anda. Catatan ini ditampilkan untuk membantu refleksi, bukan untuk memberi label atau penilaian performa.
        </p>
    </div>
    @if(count($history) > 0)
        <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" style="padding:0.7rem 1.25rem; font-size:0.875rem; text-decoration:none;" data-intro="Klik tombol ini untuk mengisi check-in kerja baru kapan saja." data-step="2">
            + Check-in Baru
        </a>
    @endif
</div>

@if(count($history) === 0)
    <div class="content-card" style="text-align:center; padding:3rem;">
        <div style="width:64px; height:64px; border-radius:999px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-weight:900; font-size:1.4rem;">◷</div>
        <h3 style="color:var(--color-gray-700); margin-bottom:0.5rem;">Belum Ada Catatan Check-in</h3>
        <p style="color:var(--color-gray-500); margin:0 auto 1.5rem; max-width:460px; line-height:1.7;">
            Anda belum mengisi check-in kerja. Mulai dari sesi singkat untuk memahami pola beban kerja, energi, dan dukungan yang Anda rasakan.
        </p>
        <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" style="text-decoration:none;">Mulai Check-in</a>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="content-card" style="padding:1.25rem; background:#eff6ff; border-color:#bfdbfe;">
            <div style="font-size:0.75rem; color:#1d4ed8; font-weight:900; text-transform:uppercase; letter-spacing:0.08em;">Total Check-in</div>
            <div style="font-size:2rem; color:#1e3a8a; font-weight:950; margin-top:0.3rem;">{{ count($history) }}x</div>
        </div>
        <div class="content-card" style="padding:1.25rem; background:#f0fdf4; border-color:#bbf7d0;">
            <div style="font-size:0.75rem; color:#166534; font-weight:900; text-transform:uppercase; letter-spacing:0.08em;">Terakhir Diisi</div>
            <div style="font-size:1rem; color:#14532d; font-weight:900; margin-top:0.6rem;">{{ collect($history)->first()->created_at->translatedFormat('d F Y') }}</div>
        </div>
        <div class="content-card" style="padding:1.25rem; background:#fff7ed; border-color:#fed7aa;">
            <div style="font-size:0.75rem; color:#9a3412; font-weight:900; text-transform:uppercase; letter-spacing:0.08em;">Catatan</div>
            <div style="font-size:0.9rem; color:#7c2d12; font-weight:800; margin-top:0.6rem; line-height:1.6;">Perubahan skor dibaca sebagai sinyal refleksi, bukan nilai performa.</div>
        </div>
    </div>

    <div class="content-card" style="padding:1.5rem; margin-bottom:1.5rem; background:#f8fafc; border-color:#e2e8f0;">
        <h2 class="card-title" style="margin:0 0 0.75rem;">Yang Anda Lihat dan Yang Pengelola Lihat</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                <div style="font-weight:900; color:var(--color-gray-800); margin-bottom:0.5rem;">Yang Anda lihat</div>
                <ul style="margin:0; padding-left:1.1rem; color:var(--color-gray-600); line-height:1.8; font-size:0.88rem;">
                    <li>Riwayat pribadi</li>
                    <li>Ringkasan evaluasi</li>
                    <li>Rekomendasi dukungan</li>
                </ul>
            </div>
            <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                <div style="font-weight:900; color:var(--color-gray-800); margin-bottom:0.5rem;">Yang pengelola gunakan</div>
                <ul style="margin:0; padding-left:1.1rem; color:var(--color-gray-600); line-height:1.8; font-size:0.88rem;">
                    <li>Pola kebutuhan dukungan kerja</li>
                    <li>Tren kondisi secara terkelola</li>
                    <li>Bahan perbaikan lingkungan kerja</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-card" data-intro="Daftar ini menampilkan catatan check-in pribadi Anda. Klik detail untuk melihat ringkasan dan rekomendasi dari setiap sesi." data-step="3">
        <h2 class="card-title" style="margin-bottom:1.5rem;">Catatan Check-in</h2>
        <div class="timeline">
            @foreach($history as $h)
                @php
                    $diagnosisId = (int) ($h->diagnosa->id ?? 0);
                    $label = match ($diagnosisId) {
                        1 => 'Keseimbangan Stabil',
                        2 => 'Butuh Dukungan Ekstra',
                        3 => 'Perlu Pemantauan',
                        4 => 'Perhatian Ringan',
                        default => 'Ringkasan Evaluasi',
                    };
                @endphp
                <div style="position:relative; padding-left:2rem; margin-bottom:1.5rem;">
                    <div style="position:absolute; left:0; top:0.75rem; width:12px; height:12px; border-radius:50%; background:{{ $h->diagnosa->color }}; box-shadow:0 0 0 3px {{ $h->diagnosa->bg_light }};"></div>
                    @if(!$loop->last)
                        <div style="position:absolute; left:5px; top:1.5rem; width:2px; height:calc(100% + 0.75rem); background:var(--color-gray-100);"></div>
                    @endif

                    <div style="border:1px solid var(--color-gray-100); border-radius:16px; padding:1.25rem; border-left:4px solid {{ $h->diagnosa->color }}; background:var(--color-bg-card);">
                        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; margin-bottom:0.75rem;">
                            <div>
                                <div style="font-size:0.75rem; color:var(--color-gray-400); font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">
                                    {{ $h->created_at->translatedFormat('d F Y, H:i') }}
                                </div>
                                <h3 style="margin:0; font-size:1rem; color:{{ $h->diagnosa->color }};">{{ $label }}</h3>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:1.35rem; font-weight:950; color:{{ $h->diagnosa->color }}; line-height:1;">{{ number_format($h->cf_final * 100, 1) }}</div>
                                <div style="font-size:0.7rem; color:var(--color-gray-400);">Skor Keseimbangan</div>
                            </div>
                        </div>

                        @if($h->gejala->isNotEmpty())
                            <div style="margin-bottom:1rem;">
                                <div style="font-size:0.7rem; font-weight:800; color:var(--color-gray-500); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem;">Area yang muncul dalam evaluasi:</div>
                                <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                                    @foreach($h->gejala->take(4) as $g)
                                        <span style="background:var(--color-gray-50); border:1px solid var(--color-gray-100); color:var(--color-gray-600); font-size:0.72rem; padding:0.2rem 0.6rem; border-radius:50px; font-weight:500;">{{ $g->nama }}</span>
                                    @endforeach
                                    @if($h->gejala->count() > 4)
                                        <span style="background:var(--color-gray-100); color:var(--color-gray-500); font-size:0.72rem; padding:0.2rem 0.6rem; border-radius:50px;">+{{ $h->gejala->count() - 4 }} area lain</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div style="display:flex; justify-content:flex-end;">
                            <a href="{{ route('karyawan.hasil') }}?id={{ $h->id }}" style="font-size:0.82rem; color:var(--color-primary); font-weight:800; text-decoration:none;">
                                Lihat Ringkasan & Rekomendasi
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.OnboardingHelper && window.OnboardingHelper.shouldShow('karyawan_history')) {
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

<style>
    @media (max-width: 900px) {
        [style*="grid-template-columns:repeat(3"],
        [style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns:1fr !important;
        }
    }
</style>
