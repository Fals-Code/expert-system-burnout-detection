@extends('layouts.app')

@section('title', 'Profil Risiko Burnout Berdasarkan MBI-GS')

@section('content')
<main style="max-width:1000px;margin:0 auto;padding:24px">
    <h1>{{ $explanation['title'] }}</h1>
    <p>{{ $explanation['summary'] }}</p>

    @if (! $explanation['is_complete'])
        <section style="padding:16px;background:#fffbeb;border:1px solid #fde68a">
            <strong>DATA TIDAK MENCUKUPI</strong>
            <p>Satu atau lebih dimensi belum memiliki seluruh respons yang diwajibkan.</p>
        </section>
    @endif

    <section style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:24px 0">
        @foreach ($explanation['dimensions'] as $code => $dimension)
            <article style="padding:16px;border:1px solid #e2e8f0">
                <h2>{{ $dimension['name'] }} ({{ $code }})</h2>
                <div style="font-size:32px;font-weight:800">
                    {{ $dimension['score'] === null ? '—' : number_format($dimension['score'], 2) }}
                </div>
                <p>Rata-rata skala 0–6</p>
                <small>{{ $dimension['direction'] }}</small>
            </article>
        @endforeach
    </section>

    <section style="padding:16px;background:#eff6ff;border:1px solid #bfdbfe">
        <strong>Profil pola: {{ $explanation['profile_label'] }}</strong>
        <p>{{ $explanation['profile_basis'] }}</p>
    </section>

    @if ($explanation['red_flag']['active'])
        <section style="margin-top:16px;padding:16px;background:#fef2f2;border:1px solid #fecaca">
            <h2>Rekomendasi Dukungan Profesional</h2>
            <p>{{ $explanation['red_flag']['recommendation'] }}</p>
        </section>
    @endif

    <section style="margin-top:16px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0">
        <strong>Disclaimer</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
    </section>

    <p style="margin-top:20px">
        <a href="{{ route('karyawan.deteksi.reset') }}">Isi Ulang</a>
        ·
        <a href="{{ route('karyawan.laporan.download', ['id' => $assessment->id]) }}" target="_blank">Unduh Ringkasan</a>
    </p>
</main>
@endsection
