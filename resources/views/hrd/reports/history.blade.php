@extends('layouts.app')

@section('title', 'Riwayat Karyawan – Sanctuary Hub')

@section('content')
@php
    $histories = $user->konsultasi->sortByDesc('created_at')->values();
    $latest = $histories->first();
    $previous = $histories->count() > 1 ? $histories[1] : null;

    $labelFor = function ($diagnosisId) {
        return match ((int) $diagnosisId) {
            1 => 'Keseimbangan Stabil',
            2 => 'Butuh Dukungan Ekstra',
            3 => 'Perlu Pemantauan',
            4 => 'Perhatian Ringan',
            default => 'Ringkasan Evaluasi',
        };
    };

    $toneFor = function ($diagnosisId) {
        return match ((int) $diagnosisId) {
            1 => ['text' => '#166534', 'dot' => '#16a34a'],
            2 => ['text' => '#9a3412', 'dot' => '#f97316'],
            3 => ['text' => '#92400e', 'dot' => '#f59e0b'],
            4 => ['text' => '#1d4ed8', 'dot' => '#2563eb'],
            default => ['text' => '#475569', 'dot' => '#64748b'],
        };
    };

    $latestTone = $toneFor($latest?->diagnosa?->id);
    $latestLabel = $latest ? $labelFor($latest->diagnosa?->id) : 'Belum ada check-in';
    $latestScore = $latest ? number_format($latest->cf_final * 100, 1) : '-';
    $scoreDelta = ($latest && $previous) ? round(($latest->cf_final - $previous->cf_final) * 100, 1) : null;
@endphp

<div class="hrd-min-page">
    <header class="hrd-min-header">
        <a href="{{ route('hrd.employees') }}" class="hrd-back">←</a>
        <div>
            <p class="hrd-kicker">Employee History</p>
            <h1>Riwayat Check-in Karyawan</h1>
            <p>{{ $user->nama }} · {{ $user->divisi->nama ?? 'Unit belum tersedia' }}</p>
        </div>
    </header>

    <section class="hrd-min-summary">
        <div><span>Total Check-in</span><strong>{{ $histories->count() }}</strong></div>
        <div><span>Kondisi Terakhir</span><strong style="color:{{ $latestTone['text'] }};">{{ $latestLabel }}</strong></div>
        <div><span>Skor Sistem</span><strong>{{ $latestScore }}</strong></div>
        <div><span>Perubahan</span><strong>{{ !is_null($scoreDelta) ? (($scoreDelta > 0 ? '+' : '') . $scoreDelta) : '-' }}</strong></div>
    </section>

    @if($histories->isEmpty())
        <section class="hrd-empty">
            <strong>Belum ada check-in</strong>
            <p>Karyawan ini belum memiliki catatan check-in kerja.</p>
        </section>
    @else
        <section class="hrd-check-section">
            <div class="hrd-section-head">
                <h2>Daftar Check-in</h2>
                <p>Satu baris mewakili satu sesi. Buka baris untuk melihat ringkasan dan detail area.</p>
            </div>

            <div class="hrd-check-list">
                @foreach($histories as $h)
                    @php
                        $tone = $toneFor($h->diagnosa?->id);
                        $supportLabel = $labelFor($h->diagnosa?->id);
                        $areas = $h->gejala ?? collect();
                        $score = number_format($h->cf_final * 100, 1);
                    @endphp

                    <details class="hrd-check-item" {{ $loop->first ? 'open' : '' }}>
                        <summary>
                            <div class="hrd-row-main">
                                <span class="hrd-dot" style="background:{{ $tone['dot'] }};"></span>
                                <div>
                                    <strong>{{ $supportLabel }}</strong>
                                    <span>{{ $h->created_at->translatedFormat('d F Y, H:i') }}</span>
                                </div>
                            </div>
                            <div class="hrd-row-meta">
                                <span>{{ $score }} skor</span>
                                <span>{{ $areas->count() }} area</span>
                                <b>⌄</b>
                            </div>
                        </summary>

                        <div class="hrd-check-detail">
                            <section class="hrd-detail-summary">
                                <h3>Ringkasan Check-in</h3>
                                <p>Sesi ini berada pada <strong style="color:{{ $tone['text'] }};">{{ $supportLabel }}</strong> dengan skor sistem <strong>{{ $score }}</strong>. {{ $h->diagnosa->deskripsi ?? 'Deskripsi ringkasan belum tersedia.' }}</p>
                            </section>

                            <div class="hrd-detail-grid">
                                <section>
                                    <h3>Saran Tindak Lanjut</h3>
                                    <p>{{ $h->diagnosa->saran ?? 'Saran belum tersedia.' }}</p>
                                </section>
                                <section>
                                    <h3>Area yang Muncul</h3>
                                    @if($areas->isEmpty())
                                        <p>Tidak ada rincian area tercatat.</p>
                                    @else
                                        <ul class="hrd-area-list">
                                            @foreach($areas->take(8) as $g)
                                                <li>{{ $g->nama }}</li>
                                            @endforeach
                                            @if($areas->count() > 8)
                                                <li>+{{ $areas->count() - 8 }} area lain</li>
                                            @endif
                                        </ul>
                                    @endif
                                </section>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif
</div>

<style>
    .hrd-min-page { max-width: 1040px; margin: 0 auto; }
    .hrd-min-header { display:flex; align-items:flex-start; gap:1rem; padding:.75rem 0 1.5rem; border-bottom:1px solid rgba(148,163,184,.18); }
    .hrd-back { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; color:#2563eb; text-decoration:none; font-weight:950; border-radius:999px; }
    .hrd-kicker { margin:0 0 .6rem; color:#2563eb; font-size:.72rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    .hrd-min-header h1 { margin:0; color:#0f172a; font-size:clamp(2rem,4vw,3rem); line-height:1.05; letter-spacing:-.06em; }
    .hrd-min-header p:last-child { margin:.7rem 0 0; color:#64748b; line-height:1.6; }
    .hrd-min-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1.25rem; padding:1.35rem 0; border-bottom:1px solid rgba(148,163,184,.18); }
    .hrd-min-summary span { display:block; color:#94a3b8; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.35rem; }
    .hrd-min-summary strong { display:block; color:#0f172a; font-size:1.25rem; line-height:1.25; font-weight:950; }
    .hrd-empty { padding:3rem 0; color:#64748b; }
    .hrd-empty strong { display:block; color:#0f172a; font-size:1.3rem; margin-bottom:.4rem; }
    .hrd-empty p { margin:0; }
    .hrd-check-section { padding-top:2rem; }
    .hrd-section-head h2 { margin:0 0 .35rem; color:#0f172a; font-size:1.3rem; font-weight:950; letter-spacing:-.03em; }
    .hrd-section-head p { margin:0; color:#64748b; line-height:1.7; }
    .hrd-check-list { margin-top:1.25rem; display:flex; flex-direction:column; }
    .hrd-check-item { border-bottom:1px solid rgba(148,163,184,.18); }
    .hrd-check-item summary { list-style:none; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 0; }
    .hrd-check-item summary::-webkit-details-marker { display:none; }
    .hrd-row-main, .hrd-row-meta { display:flex; align-items:center; gap:.75rem; }
    .hrd-dot { width:10px; height:10px; border-radius:999px; flex-shrink:0; }
    .hrd-row-main strong { display:block; color:#0f172a; font-size:1rem; font-weight:950; }
    .hrd-row-main span, .hrd-row-meta span { color:#64748b; font-size:.82rem; }
    .hrd-row-meta b { color:#94a3b8; transition:transform .2s ease; }
    .hrd-check-item[open] .hrd-row-meta b { transform:rotate(180deg); }
    .hrd-check-detail { padding:0 0 1.35rem 1.45rem; }
    .hrd-detail-summary { margin-bottom:1rem; }
    .hrd-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
    .hrd-check-detail h3 { margin:0 0 .45rem; color:#1e293b; font-size:.82rem; font-weight:950; text-transform:uppercase; letter-spacing:.06em; }
    .hrd-check-detail p { margin:0; color:#64748b; line-height:1.75; font-size:.9rem; }
    .hrd-area-list { margin:0; padding:0; list-style:none; display:flex; flex-wrap:wrap; gap:.45rem; }
    .hrd-area-list li { color:#475569; background:#f8fafc; border-radius:999px; padding:.3rem .65rem; font-size:.76rem; font-weight:800; }
    @media (max-width: 820px) { .hrd-min-summary, .hrd-detail-grid { grid-template-columns:1fr 1fr; } .hrd-check-item summary { align-items:flex-start; flex-direction:column; } }
    @media (max-width: 560px) { .hrd-min-summary, .hrd-detail-grid { grid-template-columns:1fr; } }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.hrd-check-item');
    items.forEach((item) => {
        item.addEventListener('toggle', function () {
            if (!this.open) return;
            items.forEach((other) => {
                if (other !== this) other.removeAttribute('open');
            });
        });
    });
});
</script>
@endpush
