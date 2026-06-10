@extends('layouts.app')

@section('title', 'Riwayat Saya – Sanctuary Hub')

@section('content')
@php
    $latest = $history->first();
    $latestLabel = match ((int) ($latest->diagnosa->id ?? 0)) {
        1 => 'Keseimbangan Stabil',
        2 => 'Butuh Dukungan Ekstra',
        3 => 'Perlu Pemantauan',
        4 => 'Perhatian Ringan',
        default => 'Belum ada check-in',
    };
@endphp

<div class="quiet-history-page">
    <header class="quiet-history-header" data-intro="Halaman ini menampilkan catatan check-in pribadi Anda sebagai bahan refleksi kerja dari waktu ke waktu." data-step="1">
        <div>
            <p class="quiet-kicker">Riwayat Pribadi</p>
            <h1>Riwayat Check-in Saya</h1>
            <p>Lihat perkembangan kondisi kerja Anda tanpa label berlebihan. Detail tiap check-in bisa dibuka seperlunya.</p>
        </div>
        <a href="{{ route('karyawan.deteksi.intro') }}" class="quiet-btn quiet-btn-primary" data-intro="Klik tombol ini untuk mengisi check-in kerja baru kapan saja." data-step="2">Check-in Baru</a>
    </header>

    @if(count($history) === 0)
        <section class="quiet-empty-state">
            <strong>Belum ada catatan check-in</strong>
            <p>Mulai dari sesi singkat untuk memahami pola beban kerja, energi, dan dukungan yang Anda rasakan.</p>
            <a href="{{ route('karyawan.deteksi.intro') }}" class="quiet-btn quiet-btn-primary">Mulai Check-in</a>
        </section>
    @else
        <section class="quiet-history-summary">
            <div>
                <span>Total Check-in</span>
                <strong>{{ count($history) }}x</strong>
            </div>
            <div>
                <span>Terakhir Diisi</span>
                <strong>{{ $latest->created_at->translatedFormat('d M Y') }}</strong>
            </div>
            <div>
                <span>Kondisi Terakhir</span>
                <strong>{{ $latestLabel }}</strong>
            </div>
        </section>

        <section class="quiet-history-section" data-intro="Daftar ini menampilkan catatan check-in pribadi Anda. Klik satu baris untuk melihat ringkasan dan area yang muncul." data-step="3">
            <div class="quiet-section-head">
                <div>
                    <h2>Catatan Check-in</h2>
                    <p>Setiap sesi ditampilkan ringkas. Buka detail hanya saat perlu membaca hasil lengkap.</p>
                </div>
            </div>

            <div class="quiet-history-list">
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
                        $color = $h->diagnosa->color ?? '#2563eb';
                    @endphp

                    <details class="quiet-history-item" {{ $loop->first ? 'open' : '' }}>
                        <summary>
                            <div class="quiet-item-left">
                                <span class="quiet-dot" style="background:{{ $color }};"></span>
                                <div>
                                    <strong>{{ $label }}</strong>
                                    <span>{{ $h->created_at->translatedFormat('d F Y, H:i') }}</span>
                                </div>
                            </div>
                            <div class="quiet-item-right">
                                <span>{{ number_format($h->cf_final * 100, 1) }} skor</span>
                                <b>⌄</b>
                            </div>
                        </summary>

                        <div class="quiet-history-detail">
                            <div>
                                <h3>Ringkasan</h3>
                                <p>{{ $h->diagnosa->deskripsi ?? 'Ringkasan belum tersedia.' }}</p>
                            </div>

                            <div>
                                <h3>Area yang Muncul</h3>
                                @if($h->gejala->isEmpty())
                                    <p>Tidak ada rincian area tercatat.</p>
                                @else
                                    <ul class="quiet-tag-list">
                                        @foreach($h->gejala->take(6) as $g)
                                            <li>{{ $g->nama }}</li>
                                        @endforeach
                                        @if($h->gejala->count() > 6)
                                            <li>+{{ $h->gejala->count() - 6 }} area lain</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>

                            <a href="{{ route('karyawan.hasil') }}?id={{ $h->id }}" class="quiet-link">Lihat ringkasan lengkap</a>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.quiet-history-item');
    items.forEach((item) => {
        item.addEventListener('toggle', function() {
            if (!this.open) return;
            items.forEach((other) => {
                if (other !== this) other.removeAttribute('open');
            });
        });
    });

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
    .quiet-history-page { max-width: 980px; margin: 0 auto; }
    .quiet-history-header { display:flex; justify-content:space-between; gap:1.5rem; align-items:flex-end; padding:0.75rem 0 1.5rem; border-bottom:1px solid rgba(148,163,184,.18); }
    .quiet-kicker { margin:0 0 .6rem; color:#2563eb; font-size:.72rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    .quiet-history-header h1 { margin:0; color:#0f172a; font-size:clamp(2rem,4vw,3rem); line-height:1.05; letter-spacing:-.06em; }
    .quiet-history-header p { margin:.75rem 0 0; color:#64748b; line-height:1.75; max-width:660px; }
    .quiet-btn { display:inline-flex; align-items:center; justify-content:center; padding:.8rem 1.15rem; border-radius:999px; text-decoration:none; color:#2563eb; font-weight:900; white-space:nowrap; }
    .quiet-btn-primary { background:#2563eb; color:#fff; }
    .quiet-empty-state { padding:3rem 0; max-width:540px; }
    .quiet-empty-state strong { display:block; color:#0f172a; font-size:1.3rem; margin-bottom:.45rem; }
    .quiet-empty-state p { color:#64748b; line-height:1.7; margin:0 0 1.25rem; }
    .quiet-history-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.25rem; padding:1.35rem 0; border-bottom:1px solid rgba(148,163,184,.18); }
    .quiet-history-summary span { display:block; color:#94a3b8; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.35rem; }
    .quiet-history-summary strong { display:block; color:#0f172a; font-size:1.25rem; line-height:1.25; font-weight:950; }
    .quiet-history-section { padding-top:2rem; }
    .quiet-section-head h2 { margin:0 0 .35rem; color:#0f172a; font-size:1.3rem; font-weight:950; letter-spacing:-.03em; }
    .quiet-section-head p { margin:0; color:#64748b; line-height:1.7; }
    .quiet-history-list { margin-top:1.25rem; display:flex; flex-direction:column; }
    .quiet-history-item { border-bottom:1px solid rgba(148,163,184,.18); }
    .quiet-history-item summary { list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 0; }
    .quiet-history-item summary::-webkit-details-marker { display:none; }
    .quiet-item-left, .quiet-item-right { display:flex; align-items:center; gap:.75rem; }
    .quiet-dot { width:10px; height:10px; border-radius:999px; flex-shrink:0; }
    .quiet-item-left strong { display:block; color:#0f172a; font-size:1rem; font-weight:950; }
    .quiet-item-left span, .quiet-item-right span { display:block; color:#64748b; font-size:.82rem; margin-top:.18rem; }
    .quiet-item-right b { color:#94a3b8; transition:transform .2s ease; }
    .quiet-history-item[open] .quiet-item-right b { transform:rotate(180deg); }
    .quiet-history-detail { padding:0 0 1.25rem 1.45rem; display:grid; grid-template-columns:1.1fr .9fr; gap:1.5rem; }
    .quiet-history-detail h3 { margin:0 0 .45rem; color:#1e293b; font-size:.85rem; font-weight:950; text-transform:uppercase; letter-spacing:.06em; }
    .quiet-history-detail p { margin:0; color:#64748b; line-height:1.7; font-size:.9rem; }
    .quiet-tag-list { margin:0; padding:0; list-style:none; display:flex; flex-wrap:wrap; gap:.45rem; }
    .quiet-tag-list li { color:#475569; background:#f8fafc; border-radius:999px; padding:.3rem .65rem; font-size:.76rem; font-weight:800; }
    .quiet-link { grid-column:1/-1; color:#2563eb; font-weight:900; text-decoration:none; font-size:.86rem; }
    @media (max-width: 760px) {
        .quiet-history-header { flex-direction:column; align-items:flex-start; }
        .quiet-history-summary, .quiet-history-detail { grid-template-columns:1fr; }
        .quiet-history-item summary { align-items:flex-start; flex-direction:column; }
    }
</style>
@endsection