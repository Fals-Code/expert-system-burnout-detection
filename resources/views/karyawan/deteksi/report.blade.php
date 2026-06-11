<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan CBI</title>
    <style>
        body{font-family:Arial,sans-serif;color:#1e293b;margin:40px;line-height:1.6}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:24px 0}.card,.notice{border:1px solid #cbd5e1;border-radius:12px;padding:16px}.score{font-size:30px;font-weight:800}.track{height:9px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:10px}.fill{height:100%;background:#2563eb}@media print{button{display:none}}
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
                <span>Skala 0–100</span>
                <div class="track">
                    <div class="fill" style="width:{{ $dimension['chart_value'] }}%"></div>
                </div>
            </section>
        @endforeach
    </div>

    <section class="notice">
        <strong>Interpretasi</strong>
        <p>Ketiga skor berdiri sendiri. Nilai lebih tinggi menunjukkan frekuensi kelelahan yang lebih tinggi.</p>
    </section>

    <section class="notice">
        <strong>Disclaimer</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
        <p>{{ $explanation['translation_note'] }}</p>
    </section>
</body>
</html>
