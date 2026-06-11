@extends('layouts.app')

@section('title', 'Hasil Backward Chaining')

@section('content')
<main class="bc-result">
    <header>
        <p class="eyebrow">Recursive goal verification</p>
        <h1>{{ $explanation['title'] }}</h1>
        <p>{{ $explanation['summary'] }}</p>
    </header>

    <section class="conclusion-card">
        <small>Kesimpulan rule-based</small>
        <h2>{{ $explanation['conclusion_label'] }}</h2>
        <code>{{ $explanation['conclusion_code'] }}</code>
        <p>Status sesi: <strong>{{ $explanation['status'] }}</strong></p>
    </section>

    <section class="trace-panel">
        <div class="section-heading">
            <div>
                <small>Explanation facility</small>
                <h2>Jejak Pelacakan Backward Chaining</h2>
            </div>
            <span>{{ count($explanation['trace']) }} langkah</span>
        </div>

        <div class="trace-list">
            @forelse ($explanation['trace'] as $trace)
                <article class="trace-item {{ $trace['result'] === true ? 'success' : ($trace['result'] === false ? 'failed' : '') }}">
                    <div class="trace-sequence">{{ $trace['sequence'] }}</div>
                    <div>
                        <div class="trace-meta">
                            <strong>{{ $trace['event'] }}</strong>
                            @if ($trace['rule_code'])
                                <code>{{ $trace['rule_code'] }}</code>
                            @endif
                            @if ($trace['premise_key'])
                                <code>{{ $trace['premise_key'] }}</code>
                            @endif
                        </div>
                        <p>{{ $trace['message'] }}</p>
                    </div>
                </article>
            @empty
                <p>Jejak inferensi belum tersedia.</p>
            @endforelse
        </div>
    </section>

    <section class="facts-panel">
        <div class="section-heading">
            <div>
                <small>Working memory</small>
                <h2>Fakta yang Digunakan</h2>
            </div>
            <span>{{ count($explanation['answers']) }} fakta</span>
        </div>

        <div class="facts-grid">
            @foreach ($explanation['answers'] as $answer)
                <article>
                    <div>
                        <code>{{ $answer['code'] }}</code>
                        <strong>{{ $answer['boolean_value'] ? 'TRUE' : 'FALSE' }}</strong>
                    </div>
                    <p>{{ $answer['question'] }}</p>
                    <small>Jawaban: {{ $answer['answer_label'] }}</small>
                </article>
            @endforeach
        </div>
    </section>

    <section class="notice">
        <strong>Disclaimer metodologis</strong>
        <p>{{ $explanation['disclaimer'] }}</p>
    </section>

    <div class="actions">
        <a href="{{ route('karyawan.deteksi.reset') }}">Mulai Sesi Baru</a>
        <a class="primary" href="{{ route('karyawan.laporan.download', ['id' => $session->id]) }}" target="_blank">Cetak Explanation Report</a>
    </div>
</main>

<style>
.bc-result{max-width:1080px;margin:0 auto;padding:1rem 0 3rem}.eyebrow,.section-heading small,.conclusion-card small{color:#2563eb;font-size:.75rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.bc-result header h1{margin:.35rem 0 .6rem;color:#0f172a;font-size:2.15rem}.bc-result header p,.trace-item p,.facts-grid p,.notice p{color:#64748b;line-height:1.65}.conclusion-card{padding:1.4rem;border:1px solid #bfdbfe;border-radius:20px;background:#eff6ff;margin:1.4rem 0}.conclusion-card h2{margin:.35rem 0;color:#1e3a8a}.conclusion-card code,.trace-meta code,.facts-grid code{padding:.2rem .45rem;border-radius:6px;background:#e2e8f0;color:#334155}.trace-panel,.facts-panel,.notice{padding:1.25rem;border:1px solid #e2e8f0;border-radius:18px;background:#fff;margin-bottom:1rem}.section-heading{display:flex;align-items:center;justify-content:space-between;gap:1rem}.section-heading h2{margin:.25rem 0 1rem;color:#0f172a}.section-heading>span{padding:.35rem .65rem;border-radius:999px;background:#f1f5f9;color:#475569;font-weight:800}.trace-list{display:flex;flex-direction:column;gap:.75rem}.trace-item{display:grid;grid-template-columns:auto 1fr;gap:.8rem;padding:.9rem;border:1px solid #e2e8f0;border-radius:14px}.trace-item.success{border-color:#bbf7d0;background:#f0fdf4}.trace-item.failed{border-color:#fecaca;background:#fef2f2}.trace-sequence{display:flex;width:2rem;height:2rem;align-items:center;justify-content:center;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:900}.trace-meta{display:flex;gap:.45rem;align-items:center;flex-wrap:wrap}.trace-item p{margin:.45rem 0 0}.facts-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.facts-grid article{padding:1rem;border:1px solid #e2e8f0;border-radius:14px}.facts-grid article>div{display:flex;justify-content:space-between;gap:.6rem}.facts-grid strong{color:#0f172a}.facts-grid p{margin:.65rem 0}.facts-grid small{color:#64748b}.notice{background:#f8fafc}.actions{display:flex;gap:.75rem;flex-wrap:wrap}.actions a{padding:.8rem 1rem;border-radius:999px;background:#0f172a;color:#fff;text-decoration:none;font-weight:900}.actions a.primary{background:#2563eb}@media(max-width:820px){.facts-grid{grid-template-columns:1fr}}
</style>
@endsection
