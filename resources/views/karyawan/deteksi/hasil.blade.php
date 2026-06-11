@extends('layouts.app')

@section('title', 'Profil CBI')

@section('content')
<main class="cbi-result">
    <header>
        <p class="eyebrow">Hasil skrining kontinu</p>
        <h1>{{ $explanation['title'] }}</h1>
        <p>{{ $explanation['summary'] }}</p>
    </header>

    @unless ($explanation['is_complete'])
        <section class="notice warning">
            <strong>INSUFFICIENT_DATA</strong>
            <p>Seluruh item pada setiap dimensi wajib terisi.</p>
        </section>
    @endunless

    <section class="dimension-grid">
        @foreach ($explanation['dimensions'] as $code => $dimension)
            <article class="dimension-card">
                <div class="dimension-title">
                    <h2>{{ $dimension['name'] }}</h2>
                    <span>{{ $code }}</span>
                </div>
                <div class="score">
                    {{ $dimension['score'] === null ? '—' : number_format($dimension['score'], 2) }}
                </div>
                <small>Rata-rata skala {{ $dimension['scale'] }}</small>
                <div class="bar" aria-label="{{ $dimension['name'] }} {{ $dimension['chart_value'] }} dari 100">
                    <div style="width:{{ $dimension['chart_value'] }}%"></div>
                </div>
                <p>{{ $dimension['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="notice info">
        <strong>Interpretasi</strong>
        <p>Skor PB, WB, dan CB berdiri sendiri. Nilai lebih tinggi menunjukkan frekuensi kelelahan yang lebih tinggi.</p>
    </section>

    <section class="notice">
        <strong>Disclaimer</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
        <p>{{ $explanation['translation_note'] }}</p>
    </section>

    <div class="actions">
        <a href="{{ route('karyawan.deteksi.reset') }}">Isi Ulang</a>
        <a href="{{ route('karyawan.laporan.download', ['id' => $assessment->id]) }}" target="_blank">Unduh Ringkasan</a>
    </div>
</main>

<style>
.cbi-result{max-width:1080px;margin:auto;padding:1rem 0 3rem}.eyebrow{color:#2563eb;font-weight:900;text-transform:uppercase;letter-spacing:.08em;font-size:.75rem}.cbi-result h1{color:#0f172a}.dimension-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:1.5rem 0}.dimension-card,.notice{padding:1.25rem;border:1px solid #e2e8f0;border-radius:18px;background:#fff}.dimension-title{display:flex;justify-content:space-between;gap:.5rem}.dimension-title h2{font-size:1rem}.dimension-title span{color:#2563eb;font-weight:900}.score{font-size:2.6rem;font-weight:950}.bar{height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin:1rem 0}.bar div{height:100%;background:#2563eb}.warning{background:#fffbeb;border-color:#fde68a}.info{background:#eff6ff;border-color:#bfdbfe}.actions{display:flex;gap:.75rem;margin-top:1rem}.actions a{padding:.75rem 1rem;border-radius:999px;background:#0f172a;color:white;text-decoration:none;font-weight:800}@media(max-width:850px){.dimension-grid{grid-template-columns:1fr}}
</style>
@endsection
