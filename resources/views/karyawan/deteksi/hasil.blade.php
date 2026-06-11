@extends('layouts.app')

@section('title', 'Profil Risiko Burnout Berdasarkan MBI-GS')

@section('content')
<div class="main-wrapper" style="margin-left:0;padding:0;">
    <main class="mbi-result-shell">
        <header>
            <span>Hasil Skrining Dimensional</span>
            <h1>{{ $explanation['title'] }}</h1>
            <p>{{ $explanation['summary'] }}</p>
        </header>

        @if (! $explanation['is_complete'])
            <section class="result-notice warning">
                <strong>DATA TIDAK MENCUKUPI</strong>
                <p>Satu atau lebih dimensi tidak memiliki seluruh item yang dibutuhkan. Skor parsial tidak ditafsirkan sebagai profil.</p>
            </section>
        @endif

        <section class="dimension-grid">
            @foreach ($explanation['dimensions'] as $code => $dimension)
                <article class="dimension-card">
                    <div class="dimension-heading">
                        <h2>{{ $dimension['name'] }}</h2>
                        <span>{{ $code }}</span>
                    </div>
                    <div class="dimension-score">{{ $dimension['score'] === null ? '—' : number_format($dimension['score'], 2) }}</div>
                    <small>Rata-rata skala 0–6</small>
                    <p>{{ $dimension['direction'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="result-notice profile">
            <span>Profil Pola</span>
            <h2>{{ $explanation['profile_label'] }}</h2>
            <p>{{ $explanation['profile_basis'] }}</p>
        </section>

        @if ($explanation['red_flag']['active'])
            <section class="result-notice danger">
                <h2>Rekomendasi Dukungan Profesional</h2>
                <p>{{ $explanation['red_flag']['recommendation'] }}</p>
            </section>
        @endif

        <section class="result-notice disclaimer">
            <strong>Disclaimer</strong>
            <p>{{ $explanation['disclaimer'] }}</p>
        </section>

        <div class="result-actions">
            <a href="{{ route('karyawan.deteksi.reset') }}">Isi Ulang</a>
            <a class="primary" href="{{ route('karyawan.laporan.download', ['id' => $assessment->id]) }}" target="_blank">Unduh Ringkasan</a>
        </div>
    </main>
</div>

<style>
.mbi-result-shell{max-width:1050px;margin:0 auto;padding:1rem 0 3rem}.mbi-result-shell header{margin-bottom:1.5rem}.mbi-result-shell header>span,.profile>span{color:#2563eb;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:.75rem}.mbi-result-shell h1{margin:.35rem 0 .65rem;font-size:2.15rem;color:#0f172a;font-weight:950;letter-spacing:-.04em}.mbi-result-shell header p{margin:0;max-width:850px;color:#64748b;line-height:1.75}.dimension-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-bottom:1.25rem}.dimension-card{padding:1.35rem;border:1px solid #e2e8f0;border-radius:20px;background:#fff}.dimension-heading{display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.8rem}.dimension-heading h2{margin:0;font-size:1.05rem;color:#1e293b}.dimension-heading span{padding:.3rem .55rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-weight:900;font-size:.75rem}.dimension-score{font-size:2.5rem;font-weight:950;color:#0f172a;line-height:1}.dimension-card small{color:#64748b}.dimension-card p{margin:.9rem 0 0;color:#475569;line-height:1.6;font-size:.86rem}.result-notice{padding:1.25rem;border-radius:18px;margin-bottom:1.25rem}.result-notice h2,.result-notice p{margin:.35rem 0}.warning{border:1px solid #fde68a;background:#fffbeb;color:#92400e}.profile{border:1px solid #dbeafe;background:#eff6ff;color:#1e40af}.danger{border:1px solid #fecaca;background:#fef2f2;color:#991b1b}.disclaimer{border:1px solid #e2e8f0;background:#f8fafc;color:#64748b}.result-actions{display:flex;gap:.75rem;flex-wrap:wrap}.result-actions a{padding:.8rem 1.15rem;border-radius:999px;background:#0f172a;color:#fff;text-decoration:none;font-weight:900}.result-actions a.primary{background:#2563eb}@media(max-width:800px){.dimension-grid{grid-template-columns:1fr}}
</style>
@endsection
