<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan MBI-GS</title>
    <style>
        body{font-family:Arial,sans-serif;color:#1e293b;margin:40px;line-height:1.6}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:24px 0}.card,.notice{border:1px solid #cbd5e1;padding:16px}.score{font-size:30px;font-weight:800}.alert{border-color:#fecaca;background:#fef2f2}@media print{button{display:none}}
    </style>
</head>
<body>
    <button onclick="window.print()">Cetak / Simpan PDF</button>
    <h1>{{ $explanation['title'] }}</h1>
    <p>Tanggal: {{ $assessment->created_at->translatedFormat('d F Y, H:i') }}</p>
    <p>{{ $explanation['summary'] }}</p>

    <div class="grid">
        @foreach ($explanation['dimensions'] as $code => $dimension)
            <section class="card">
                <strong>{{ $dimension['name'] }} ({{ $code }})</strong>
                <div class="score">{{ $dimension['score'] === null ? '—' : number_format($dimension['score'], 2) }}</div>
                <span>Rata-rata skala 0–6</span>
            </section>
        @endforeach
    </div>

    <section class="notice">
        <strong>Profil pola: {{ $explanation['profile_label'] }}</strong>
        <p>{{ $explanation['profile_basis'] }}</p>
    </section>

    @if ($explanation['red_flag']['active'])
        <section class="notice alert">
            <strong>Rekomendasi dukungan profesional</strong>
            <p>{{ $explanation['red_flag']['recommendation'] }}</p>
        </section>
    @endif

    <section class="notice">
        <strong>Disclaimer</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
    </section>
</body>
</html>
