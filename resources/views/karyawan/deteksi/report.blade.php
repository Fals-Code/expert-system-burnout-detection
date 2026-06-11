<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explanation Report Backward Chaining</title>
    <style>
        body{font-family:Arial,sans-serif;color:#1e293b;margin:40px;line-height:1.55}h1,h2{color:#0f172a}.summary,.trace,.facts,.disclaimer{border:1px solid #cbd5e1;border-radius:12px;padding:16px;margin:18px 0}.trace-item{display:grid;grid-template-columns:34px 1fr;gap:10px;padding:10px 0;border-bottom:1px solid #e2e8f0}.trace-item:last-child{border-bottom:0}.seq{display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:800}.meta{font-size:12px;color:#64748b}.fact{padding:10px 0;border-bottom:1px solid #e2e8f0}.fact:last-child{border-bottom:0}code{background:#f1f5f9;padding:2px 5px;border-radius:4px}@media print{button{display:none}}
    </style>
</head>
<body>
    <button onclick="window.print()">Cetak / Simpan PDF</button>

    <h1>{{ $explanation['title'] }}</h1>
    <p>Tanggal: {{ $session->created_at->translatedFormat('d F Y, H:i') }}</p>

    <section class="summary">
        <strong>Kesimpulan</strong>
        <h2>{{ $explanation['conclusion_label'] }}</h2>
        <code>{{ $explanation['conclusion_code'] }}</code>
        <p>{{ $explanation['summary'] }}</p>
    </section>

    <section class="trace">
        <h2>Jejak Inferensi</h2>
        @foreach ($explanation['trace'] as $trace)
            <article class="trace-item">
                <div class="seq">{{ $trace['sequence'] }}</div>
                <div>
                    <div class="meta">
                        {{ $trace['event'] }}
                        @if ($trace['rule_code'])
                            · {{ $trace['rule_code'] }}
                        @endif
                        @if ($trace['premise_key'])
                            · {{ $trace['premise_key'] }}
                        @endif
                    </div>
                    <p>{{ $trace['message'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="facts">
        <h2>Fakta yang Digunakan</h2>
        @foreach ($explanation['answers'] as $answer)
            <article class="fact">
                <strong>{{ $answer['code'] }} · {{ $answer['boolean_value'] ? 'TRUE' : 'FALSE' }}</strong>
                <p>{{ $answer['question'] }}</p>
                <small>Jawaban: {{ $answer['answer_label'] }}</small>
            </article>
        @endforeach
    </section>

    <section class="disclaimer">
        <strong>Disclaimer metodologis</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
    </section>
</body>
</html>
