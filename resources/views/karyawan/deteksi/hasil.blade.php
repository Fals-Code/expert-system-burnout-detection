@extends('layouts.app')

@section('title', 'Hasil Evaluasi Lingkungan Kerja – BurnoutXpert')

@section('content')
@php
    $diagnosis = $konsultasi->diagnosa;
    $diagnosisId = (int) ($diagnosis->id ?? 0);
    $ruleCode = data_get($tracing ?? [], 'rule_kode', '-');
    $cfPakarRule = data_get($tracing ?? [], 'cf_pakar_rule');
    $cfCombine = data_get($tracing ?? [], 'cf_combine_gejala');

    $themes = [
        1 => [
            'wrapper' => 'background:#f0fdf4; color:#166534; border-color:#bbf7d0;',
            'badge' => 'background:#16a34a; color:#ffffff;',
            'soft' => 'background:#dcfce7; color:#166534; border-color:#bbf7d0;',
            'label' => 'Kondisi Sehat',
            'icon' => '✓',
        ],
        2 => [
            'wrapper' => 'background:#fef2f2; color:#991b1b; border-color:#fecaca;',
            'badge' => 'background:#dc2626; color:#ffffff;',
            'soft' => 'background:#fee2e2; color:#991b1b; border-color:#fecaca;',
            'label' => 'Peringatan Tinggi',
            'icon' => '!',
        ],
        3 => [
            'wrapper' => 'background:#fff7ed; color:#9a3412; border-color:#fed7aa;',
            'badge' => 'background:#f97316; color:#ffffff;',
            'soft' => 'background:#ffedd5; color:#9a3412; border-color:#fed7aa;',
            'label' => 'Peringatan Sedang',
            'icon' => '!',
        ],
        4 => [
            'wrapper' => 'background:#fefce8; color:#854d0e; border-color:#fde68a;',
            'badge' => 'background:#eab308; color:#ffffff;',
            'soft' => 'background:#fef3c7; color:#854d0e; border-color:#fde68a;',
            'label' => 'Peringatan Ringan',
            'icon' => 'i',
        ],
    ];

    $theme = $themes[$diagnosisId] ?? [
        'wrapper' => 'background:#f8fafc; color:#334155; border-color:#e2e8f0;',
        'badge' => 'background:#475569; color:#ffffff;',
        'soft' => 'background:#f1f5f9; color:#334155; border-color:#e2e8f0;',
        'label' => 'Hasil Evaluasi',
        'icon' => 'i',
    ];
@endphp

<div class="main-wrapper" style="margin-left:0; padding:0;">
    <main class="result-container" style="max-width:1100px; margin:0 auto; padding:1rem 0 3rem;">
        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div>
                <h1 style="margin:0 0 0.5rem; color:var(--color-primary); font-size:2rem; font-weight:900; letter-spacing:-0.03em;">
                    Hasil Evaluasi Lingkungan Kerja
                </h1>
                <p style="margin:0; color:var(--color-gray-500); line-height:1.7;">
                    Halaman ini membaca data historis dari record konsultasi yang sudah tersimpan, sehingga refresh halaman tidak menghitung ulang diagnosis dari awal.
                </p>
            </div>

            <a href="{{ route('karyawan.deteksi.reset') }}" style="display:inline-flex; align-items:center; gap:0.5rem; background:#0f172a; color:white; padding:0.85rem 1.15rem; border-radius:999px; text-decoration:none; font-weight:900; box-shadow:0 10px 20px rgba(15,23,42,0.16);">
                Lakukan Deteksi Ulang
            </a>
        </div>

        <section style="border:1px solid; {{ $theme['wrapper'] }} border-radius:28px; padding:2rem; box-shadow:0 20px 40px rgba(15,23,42,0.06); margin-bottom:1.5rem; position:relative; overflow:hidden;">
            <div style="position:absolute; width:280px; height:280px; border-radius:999px; background:rgba(255,255,255,0.45); filter:blur(60px); top:-120px; right:-120px;"></div>

            <div style="position:relative; z-index:1; display:grid; grid-template-columns:auto 1fr; gap:1.25rem; align-items:flex-start;">
                <div style="width:64px; height:64px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:900; border:1px solid; {{ $theme['soft'] }}">
                    {{ $theme['icon'] }}
                </div>

                <div>
                    <div style="display:inline-flex; padding:0.4rem 0.85rem; border-radius:999px; font-size:0.75rem; font-weight:900; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.75rem; {{ $theme['badge'] }}">
                        {{ $theme['label'] }}
                    </div>

                    <h2 style="margin:0 0 0.75rem; font-size:2rem; font-weight:950; line-height:1.2;">
                        {{ $diagnosis->nama ?? 'Diagnosis tidak tersedia' }}
                    </h2>

                    <div style="font-size:3.75rem; font-weight:950; line-height:1; margin-bottom:1rem;">
                        {{ $confidence }}%
                    </div>

                    <p style="margin:0; max-width:760px; line-height:1.8; font-weight:600;">
                        {{ $diagnosis->deskripsi ?? 'Deskripsi diagnosis belum tersedia di database.' }}
                    </p>
                </div>
            </div>
        </section>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <section class="content-card" style="padding:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Saran dari Basis Pengetahuan</h3>
                <div style="color:var(--color-gray-700); line-height:1.8; font-size:0.95rem;">
                    {!! nl2br(e($diagnosis->saran ?? 'Saran belum tersedia di database.')) !!}
                </div>
            </section>

            <section class="content-card" style="padding:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Rule Dominan</h3>
                <div style="border:1px solid; {{ $theme['soft'] }} border-radius:16px; padding:1rem;">
                    <div style="font-size:2rem; font-weight:950; margin-bottom:0.25rem;">{{ $ruleCode }}</div>
                    @if ($cfPakarRule !== null)
                        <div style="font-size:0.85rem; font-weight:700;">CF Pakar: {{ number_format((float) $cfPakarRule, 2) }}</div>
                    @endif
                    @if ($cfCombine !== null)
                        <div style="font-size:0.85rem; font-weight:700;">CF Gejala: {{ number_format((float) $cfCombine, 4) }}</div>
                    @endif
                    <div style="font-size:0.85rem; font-weight:700; margin-top:0.25rem;">CF Final: {{ number_format((float) $konsultasi->cf_final, 4) }}</div>
                </div>
            </section>
        </div>

        <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem;">
            <h3 class="card-title" style="margin-bottom:1rem;">Rincian Jawaban dan Kontribusi Gejala</h3>

            @if (!isset($tracing['gejala_details']) || count($tracing['gejala_details']) === 0)
                <p style="color:var(--color-gray-400); margin:0;">Tidak ada rincian gejala yang tercatat.</p>
            @else
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach ($tracing['gejala_details'] as $detail)
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; border:1px solid var(--color-gray-200); border-radius:14px; padding:1rem; background:white;">
                            <div>
                                <div style="font-weight:800; color:var(--color-gray-800);">{{ $detail['gejala'] ?? '-' }}</div>
                                <div style="font-size:0.82rem; color:var(--color-gray-500); margin-top:0.25rem;">
                                    Kode: {{ $detail['kode'] ?? '-' }} | Arah Evidence: {{ $detail['evidence_direction'] ?? 'PRESENT_SUPPORTS' }}
                                </div>
                            </div>
                            <div style="text-align:right; white-space:nowrap;">
                                <div style="font-weight:900; color:var(--color-primary);">{{ $detail['user_ans'] ?? '-' }}</div>
                                <div style="font-size:0.82rem; color:var(--color-gray-500);">CF: {{ number_format((float) ($detail['cf_sub'] ?? 0), 4) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if(isset($explanation))
            <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Penjelasan Sistem Pakar</h3>
                @php
                    $parsedSummary = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $explanation['summary'] ?? '');
                    $parsedSummary = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedSummary);
                @endphp
                <div style="line-height:1.8; color:var(--color-gray-700); margin-bottom:1rem;">
                    {!! $parsedSummary !!}
                </div>

                @if(!empty($explanation['reasoning_chain']))
                    <ol style="margin:0; padding-left:1.2rem; line-height:1.8; color:var(--color-gray-600);">
                        @foreach($explanation['reasoning_chain'] as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ol>
                @endif
            </section>
        @endif

        <div style="display:flex; gap:0.75rem; flex-wrap:wrap; justify-content:center;">
            <a href="{{ route('karyawan.deteksi.reset') }}" class="btn-action" style="background:#0f172a; color:white; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Lakukan Deteksi Ulang
            </a>
            <a href="{{ route('karyawan.laporan.download', ['id' => $konsultasi->id]) }}" class="btn-action" target="_blank" style="background:var(--color-primary); color:white; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Unduh Laporan
            </a>
            <a href="{{ route('karyawan.dashboard') }}" class="btn-action" style="background:#f1f5f9; color:#334155; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Dashboard
            </a>
        </div>
    </main>
</div>

<style>
    @media (max-width: 768px) {
        [style*="grid-template-columns:2fr 1fr"] { grid-template-columns:1fr !important; }
        .result-container { padding-left:1rem !important; padding-right:1rem !important; }
    }
</style>
@endsection
