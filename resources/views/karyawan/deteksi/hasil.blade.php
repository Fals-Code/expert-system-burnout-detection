@extends('layouts.app')

@section('title', 'Ringkasan Check-in Kerja - SanctuaryHub')

@section('content')
@php
    $diagnosis = $konsultasi->diagnosa;
    $diagnosisId = (int) ($diagnosis->id ?? 0);
    $ruleCode = data_get($tracing ?? [], 'rule_kode', '-');

    $themes = [
        1 => [
            'wrapper' => 'background:#f0fdf4; color:#166534; border-color:#bbf7d0;',
            'badge' => 'background:#16a34a; color:#ffffff;',
            'soft' => 'background:#dcfce7; color:#166534; border-color:#bbf7d0;',
            'label' => 'Keseimbangan Stabil',
            'title' => 'Kondisi Kerja Anda Tampak Stabil',
            'icon' => '✓',
        ],
        2 => [
            'wrapper' => 'background:#fff7ed; color:#9a3412; border-color:#fed7aa;',
            'badge' => 'background:#f97316; color:#ffffff;',
            'soft' => 'background:#ffedd5; color:#9a3412; border-color:#fed7aa;',
            'label' => 'Butuh Dukungan Ekstra',
            'title' => 'Kondisi Anda Membutuhkan Perhatian Ekstra',
            'icon' => 'i',
        ],
        3 => [
            'wrapper' => 'background:#fffbeb; color:#92400e; border-color:#fde68a;',
            'badge' => 'background:#f59e0b; color:#ffffff;',
            'soft' => 'background:#fef3c7; color:#92400e; border-color:#fde68a;',
            'label' => 'Perlu Pemantauan',
            'title' => 'Beberapa Area Perlu Dipantau',
            'icon' => 'i',
        ],
        4 => [
            'wrapper' => 'background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;',
            'badge' => 'background:#2563eb; color:#ffffff;',
            'soft' => 'background:#dbeafe; color:#1d4ed8; border-color:#bfdbfe;',
            'label' => 'Perhatian Ringan',
            'title' => 'Ada Area Ringan yang Perlu Perhatian',
            'icon' => 'i',
        ],
    ];

    $theme = $themes[$diagnosisId] ?? [
        'wrapper' => 'background:#f8fafc; color:#334155; border-color:#e2e8f0;',
        'badge' => 'background:#475569; color:#ffffff;',
        'soft' => 'background:#f1f5f9; color:#334155; border-color:#e2e8f0;',
        'label' => 'Ringkasan Evaluasi',
        'title' => 'Ringkasan Check-in Kerja',
        'icon' => 'i',
    ];
@endphp

<div class="main-wrapper" style="margin-left:0; padding:0;">
    <main class="result-container" style="max-width:1100px; margin:0 auto; padding:1rem 0 3rem;">
        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div>
                <h1 style="margin:0 0 0.5rem; color:var(--color-primary); font-size:2rem; font-weight:900; letter-spacing:-0.03em;">
                    Ringkasan Check-in Kerja
                </h1>
                <p style="margin:0; color:var(--color-gray-500); line-height:1.7; max-width:760px;">
                    Ringkasan ini membantu Anda memahami area kerja yang sudah stabil dan area yang mungkin membutuhkan dukungan. Hasil ini adalah indikasi awal berbasis jawaban check-in, bukan diagnosis medis atau penilaian performa.
                </p>
            </div>

            <a href="{{ route('karyawan.deteksi.reset') }}" style="display:inline-flex; align-items:center; gap:0.5rem; background:#0f172a; color:white; padding:0.85rem 1.15rem; border-radius:999px; text-decoration:none; font-weight:900; box-shadow:0 10px 20px rgba(15,23,42,0.16);">
                Isi Check-in Ulang
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
                        {{ $theme['title'] }}
                    </h2>

                    <div style="font-size:3rem; font-weight:950; line-height:1; margin-bottom:0.65rem;">
                        Skor CF: {{ number_format($konsultasi->cf_final, 4) }} ({{ $confidence }}%)
                    </div>
                    <p style="margin:0 0 1rem; max-width:760px; line-height:1.7; font-weight:700; opacity:0.88;">
                        Angka ini membantu membaca pola jawaban, bukan nilai diri atau performa kerja Anda.
                    </p>

                    <p style="margin:0; max-width:760px; line-height:1.8; font-weight:600;">
                        {{ $diagnosis->deskripsi ?? 'Deskripsi evaluasi belum tersedia di database.' }}
                    </p>
                </div>
            </div>
        </section>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <section class="content-card" style="padding:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Rekomendasi Dukungan</h3>
                <div style="color:var(--color-gray-700); line-height:1.8; font-size:0.95rem;">
                    {!! nl2br(e($diagnosis->saran ?? 'Rekomendasi belum tersedia di database.')) !!}
                </div>
            </section>

            <section class="content-card" style="padding:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Kode Evaluasi</h3>
                <div style="border:1px solid; {{ $theme['soft'] }} border-radius:16px; padding:1rem;">
                    <div style="font-size:2rem; font-weight:950; margin-bottom:0.25rem;">{{ $ruleCode }}</div>
                    <div style="font-size:0.85rem; font-weight:700; line-height:1.7;">
                        Kode ini dipakai untuk penelusuran internal sistem, bukan label kondisi personal.
                    </div>
                </div>
            </section>
        </div>

        <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem; background:#f8fafc; border-color:#e2e8f0;">
            <h3 class="card-title" style="margin-bottom:0.75rem;">Pilihan Dukungan Ringan</h3>
            <p style="margin:0 0 1rem; color:var(--color-gray-500); line-height:1.7; font-size:0.92rem;">
                Anda tidak harus menindaklanjuti semuanya. Pilih satu langkah kecil yang paling realistis untuk kondisi minggu ini.
            </p>
            <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem;">
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                    <div style="font-weight:900; color:#1e293b; margin-bottom:0.4rem;">Atur prioritas kerja</div>
                    <p style="margin:0; color:#64748b; font-size:0.84rem; line-height:1.6;">Catat satu tugas yang paling menguras energi, lalu urutkan ulang prioritas harian.</p>
                </div>
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                    <div style="font-weight:900; color:#1e293b; margin-bottom:0.4rem;">Diskusikan beban kerja</div>
                    <p style="margin:0; color:#64748b; font-size:0.84rem; line-height:1.6;">Bicarakan hambatan atau deadline yang terasa berat jika kondisi ini berlanjut.</p>
                </div>
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                    <div style="font-weight:900; color:#1e293b; margin-bottom:0.4rem;">Pulihkan energi</div>
                    <p style="margin:0; color:#64748b; font-size:0.84rem; line-height:1.6;">Ambil jeda singkat, tidur cukup, dan batasi tambahan pekerjaan yang belum mendesak.</p>
                </div>
            </div>
        </section>

        <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem;">
            <h3 class="card-title" style="margin-bottom:1rem;">Area yang Perlu Dukungan</h3>

            @if (!isset($tracing['gejala_details']) || count($tracing['gejala_details']) === 0)
                <p style="color:var(--color-gray-400); margin:0;">Tidak ada rincian area yang tercatat.</p>
            @else
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach ($tracing['gejala_details'] as $detail)
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; border:1px solid var(--color-gray-200); border-radius:14px; padding:1rem; background:white;">
                            <div>
                                <div style="font-weight:800; color:var(--color-gray-800);">{{ $detail['gejala'] ?? '-' }}</div>
                                <div style="font-size:0.82rem; color:var(--color-gray-500); margin-top:0.25rem;">
                                    Jawaban Anda membantu sistem membaca pola kerja harian dengan lebih akurat.
                                </div>
                            </div>
                            <div style="text-align:right; white-space:nowrap;">
                                <div style="font-weight:900; color:var(--color-primary);">{{ $detail['user_ans'] ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if(isset($explanation))
            <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem;">
                <h3 class="card-title" style="margin-bottom:1rem;">Catatan Ringkas Sistem</h3>
                <div style="line-height:1.8; color:var(--color-gray-700); margin-bottom:1rem;">
                    {{ $explanation['summary'] ?? '' }}
                </div>
            </section>
        @endif

        <section class="content-card" style="padding:1.5rem; margin-bottom:1.5rem;">
            <details>
                <summary style="cursor:pointer; font-weight:900; color:var(--color-gray-800);">Bagaimana hasil dihitung</summary>
                <div style="margin-top:1rem; overflow-x:auto;">
                    <p style="margin:0 0 1rem; color:var(--color-gray-600); line-height:1.7;">
                        Sistem menguji goal secara berurutan: Risiko Tinggi, Risiko Sedang, Risiko Rendah. Jika tidak ada rule melewati threshold, hasil menjadi Tidak Terindikasi Burnout.
                    </p>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jawaban</th>
                                <th>CF User</th>
                                <th>Bobot Gejala</th>
                                <th>CF Premis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($tracing['gejala_details'] ?? []) as $detail)
                                <tr>
                                    <td>{{ $detail['kode'] ?? '-' }}</td>
                                    <td>{{ $detail['user_ans'] ?? '-' }}</td>
                                    <td>{{ number_format((float) ($detail['cf_user'] ?? 0), 2) }}</td>
                                    <td>{{ number_format((float) ($detail['bobot'] ?? 0), 2) }}</td>
                                    <td>{{ number_format((float) ($detail['cf_sub'] ?? 0), 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p style="margin:1rem 0 0; color:var(--color-gray-700); line-height:1.7;">
                        Rule utama: <strong>{{ $tracing['rule_kode'] ?? '-' }}</strong>.
                        Rata-rata CF premis {{ number_format((float) ($tracing['cf_average_premis'] ?? 0), 4) }}
                        x CF pakar {{ number_format((float) ($tracing['cf_pakar_rule'] ?? 0), 2) }}
                        = {{ number_format((float) ($tracing['cf_rule'] ?? $konsultasi->cf_final), 4) }}.
                        Threshold: {{ number_format((float) ($tracing['min_threshold'] ?? 0.25), 2) }}.
                    </p>
                </div>
            </details>
        </section>

        <div style="display:flex; gap:0.75rem; flex-wrap:wrap; justify-content:center;">
            <a href="{{ route('karyawan.deteksi.reset') }}" class="btn-action" style="background:#0f172a; color:white; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Isi Check-in Ulang
            </a>
            <a href="{{ route('karyawan.laporan.download', ['id' => $konsultasi->id]) }}" class="btn-action" target="_blank" style="background:var(--color-primary); color:white; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Unduh Ringkasan
            </a>
            <a href="{{ route('karyawan.history') }}" class="btn-action" style="background:#f1f5f9; color:#334155; text-decoration:none; padding:0.8rem 1.4rem; border-radius:999px; font-weight:900;">
                Lihat Riwayat Check-in
            </a>
        </div>
    </main>
</div>

<style>
    @media (max-width: 768px) {
        [style*="grid-template-columns:2fr 1fr"],
        [style*="grid-template-columns:repeat(3"] { grid-template-columns:1fr !important; }
        .result-container { padding-left:1rem !important; padding-right:1rem !important; }
    }
</style>
@endsection
