@extends('layouts.app')

@section('title', 'Riwayat Dukungan Karyawan – Ruang Check-in')

@section('content')
@php
    $histories = $user->konsultasi->sortByDesc('created_at')->values();
    $chartHistories = $user->konsultasi->sortBy('created_at')->values();
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
            1 => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'dot' => '#16a34a'],
            2 => ['bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa', 'dot' => '#f97316'],
            3 => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'dot' => '#f59e0b'],
            4 => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe', 'dot' => '#2563eb'],
            default => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0', 'dot' => '#64748b'],
        };
    };

    $latestTone = $toneFor($latest?->diagnosa?->id);
    $latestLabel = $latest ? $labelFor($latest->diagnosa?->id) : 'Belum ada check-in';
    $latestScore = $latest ? number_format($latest->cf_final * 100, 1) : '-';
    $previousScore = $previous ? number_format($previous->cf_final * 100, 1) : null;
    $scoreDelta = ($latest && $previous) ? round(($latest->cf_final - $previous->cf_final) * 100, 1) : null;
    $chartDates = $chartHistories->map(fn($item) => $item->created_at->translatedFormat('d M'))->toArray();
    $chartScores = $chartHistories->map(fn($item) => round($item->cf_final * 100, 1))->toArray();
@endphp

<div class="hrd-history-shell">
    <div class="hrd-history-header">
        <div class="hrd-history-title-wrap">
            <a href="{{ route('hrd.employees') }}" class="hrd-back-link" aria-label="Kembali ke daftar karyawan">←</a>
            <div>
                <p class="hrd-kicker">Support History</p>
                <h1 class="page-title hrd-page-title">Riwayat Dukungan Kerja</h1>
                <p class="hrd-subtitle">
                    Ringkasan check-in untuk membantu HRD membaca pola dukungan kerja. Detail tersedia per sesi tanpa membuat halaman berubah jadi arsip dakwaan, karena manusia ternyata kurang suka diperlakukan seperti tiket masalah.
                </p>
            </div>
        </div>
    </div>

    <section class="hrd-summary-grid">
        <article class="hrd-summary-card hrd-summary-card--identity">
            <span class="hrd-card-label">Karyawan</span>
            <strong>{{ $user->nama }}</strong>
            <small>{{ $user->divisi->nama ?? 'Unit belum tersedia' }}</small>
        </article>

        <article class="hrd-summary-card">
            <span class="hrd-card-label">Total Check-in</span>
            <strong>{{ $histories->count() }}</strong>
            <small>catatan tersimpan</small>
        </article>

        <article class="hrd-summary-card" style="background:{{ $latestTone['bg'] }}; border-color:{{ $latestTone['border'] }};">
            <span class="hrd-card-label" style="color:{{ $latestTone['text'] }};">Kondisi Terakhir</span>
            <strong style="color:{{ $latestTone['text'] }}; font-size:1.05rem;">{{ $latestLabel }}</strong>
            <small>{{ $latest?->created_at?->translatedFormat('d M Y, H:i') ?? 'Belum ada data' }}</small>
        </article>

        <article class="hrd-summary-card">
            <span class="hrd-card-label">Skor Sistem</span>
            <strong>{{ $latestScore }}</strong>
            <small>
                @if(!is_null($scoreDelta))
                    {{ $scoreDelta > 0 ? '+' : '' }}{{ $scoreDelta }} dari sesi sebelumnya
                @elseif($previousScore)
                    sebelumnya {{ $previousScore }}
                @else
                    belum ada pembanding
                @endif
            </small>
        </article>
    </section>

    <section class="hrd-guidance-strip">
        <div>
            <strong>Prinsip baca cepat:</strong>
            <span>lihat tren, lihat area dukungan, lalu tindak lanjuti dengan bahasa suportif. Jangan jadikan skor sebagai ranking karyawan.</span>
        </div>
        <div>
            <strong>Fokus HRD:</strong>
            <span>beban kerja, hambatan kerja, ritme kerja, dan kebutuhan dukungan organisasi.</span>
        </div>
    </section>

    @if($histories->isEmpty())
        <section class="content-card hrd-empty-state">
            <div class="hrd-empty-icon">◷</div>
            <h3>Belum Ada Check-in</h3>
            <p>Karyawan ini belum memiliki catatan check-in kerja. Belum ada data yang perlu ditindaklanjuti.</p>
        </section>
    @else
        <div class="hrd-history-layout">
            <section class="content-card hrd-trend-card">
                <div class="hrd-section-head">
                    <div>
                        <h2 class="card-title">Tren Ringkas</h2>
                        <p>Perubahan skor sistem dari waktu ke waktu. Angka ini adalah sinyal evaluasi, bukan nilai performa personal.</p>
                    </div>
                </div>
                <div id="employeeHistoryChart" class="hrd-chart"></div>
            </section>

            <section class="content-card hrd-ethic-card">
                <h2 class="card-title">Catatan Etis</h2>
                <p>
                    Saat perlu follow-up, mulai dari pertanyaan aman seperti “apa beban kerja yang terasa berat?” bukan “kenapa skor kamu tinggi?”. Yang pertama dukungan, yang kedua jump scare administratif.
                </p>
                <div class="hrd-ethic-list">
                    <span>✓ Tidak untuk ranking</span>
                    <span>✓ Tidak untuk hukuman</span>
                    <span>✓ Gunakan konteks kerja</span>
                </div>
            </section>
        </div>

        <section class="content-card hrd-compact-list-card">
            <div class="hrd-section-head hrd-section-head--split">
                <div>
                    <h2 class="card-title">Daftar Check-in</h2>
                    <p>Klik satu baris untuk melihat rincian area dukungan dan saran tindak lanjut.</p>
                </div>
                <span class="hrd-mini-help">Detail terbuka satu per satu agar tetap rapi.</span>
            </div>

            <div class="hrd-history-list">
                @foreach($histories as $h)
                    @php
                        $tone = $toneFor($h->diagnosa?->id);
                        $supportLabel = $labelFor($h->diagnosa?->id);
                        $areas = $h->gejala ?? collect();
                    @endphp

                    <details class="hrd-history-item" {{ $loop->first ? 'open' : '' }}>
                        <summary>
                            <div class="hrd-row-left">
                                <span class="hrd-status-dot" style="background:{{ $tone['dot'] }};"></span>
                                <div>
                                    <div class="hrd-row-date">{{ $h->created_at->translatedFormat('d M Y, H:i') }}</div>
                                    <div class="hrd-row-title" style="color:{{ $tone['text'] }};">{{ $supportLabel }}</div>
                                </div>
                            </div>
                            <div class="hrd-row-right">
                                <span class="hrd-chip" style="background:{{ $tone['bg'] }}; color:{{ $tone['text'] }}; border-color:{{ $tone['border'] }};">{{ number_format($h->cf_final * 100, 1) }} skor</span>
                                <span class="hrd-chip hrd-chip--neutral">{{ $areas->count() }} area</span>
                                <span class="hrd-chevron">⌄</span>
                            </div>
                        </summary>

                        <div class="hrd-detail-panel">
                            <div class="hrd-detail-grid">
                                <div class="hrd-detail-box hrd-detail-box--wide">
                                    <span class="hrd-detail-label">Ringkasan Sistem</span>
                                    <p>{{ $h->diagnosa->deskripsi ?? 'Deskripsi ringkasan belum tersedia.' }}</p>
                                </div>

                                <div class="hrd-detail-box">
                                    <span class="hrd-detail-label">Saran Tindak Lanjut</span>
                                    <p>{{ $h->diagnosa->saran ?? 'Saran belum tersedia.' }}</p>
                                </div>
                            </div>

                            <div class="hrd-detail-box hrd-area-box">
                                <div class="hrd-area-header">
                                    <span class="hrd-detail-label">Area yang Perlu Dukungan</span>
                                    <small>Maksimal 8 area ditampilkan agar mudah dibaca.</small>
                                </div>

                                @if($areas->isEmpty())
                                    <span class="hrd-empty-chip">Tidak ada rincian area tercatat</span>
                                @else
                                    <div class="hrd-area-list">
                                        @foreach($areas->take(8) as $g)
                                            <span class="hrd-area-chip">{{ $g->nama }}</span>
                                        @endforeach
                                        @if($areas->count() > 8)
                                            <span class="hrd-area-chip hrd-area-chip--more">+{{ $areas->count() - 8 }} area lain</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const historyItems = document.querySelectorAll('.hrd-history-item');

    historyItems.forEach((item) => {
        item.addEventListener('toggle', function () {
            if (!this.open) return;

            historyItems.forEach((other) => {
                if (other !== this) {
                    other.removeAttribute('open');
                }
            });
        });
    });

    @if(!$histories->isEmpty())
        const employeeHistoryChart = new ApexCharts(document.querySelector('#employeeHistoryChart'), {
            series: [{
                name: 'Skor Sistem',
                data: {!! json_encode($chartScores) !!}
            }],
            chart: {
                type: 'area',
                height: 220,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Poppins, sans-serif'
            },
            colors: ['#2563eb'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.24, opacityTo: 0.04, stops: [0, 90, 100] }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: {!! json_encode($chartDates) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 4,
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                y: { formatter: function(val) { return val.toFixed(1) + ' skor sistem'; } }
            },
            markers: { size: 4, colors: ['#2563eb'], strokeColors: '#ffffff', strokeWidth: 2, hover: { size: 6 } }
        });

        employeeHistoryChart.render();
    @endif
});
</script>
@endpush

<style>
    .hrd-history-shell {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .hrd-history-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hrd-history-title-wrap {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .hrd-back-link {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    .hrd-kicker {
        display: inline-flex;
        margin: 0 0 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.75rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .hrd-page-title { margin: 0 0 0.35rem; }

    .hrd-subtitle {
        margin: 0;
        color: var(--color-gray-500);
        line-height: 1.7;
        max-width: 760px;
    }

    .hrd-summary-grid {
        display: grid;
        grid-template-columns: 1.2fr repeat(3, minmax(0, 0.8fr));
        gap: 1rem;
    }

    .hrd-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    .hrd-summary-card strong {
        display: block;
        font-size: 1.7rem;
        color: #0f172a;
        line-height: 1.15;
        font-weight: 950;
    }

    .hrd-summary-card small {
        display: block;
        color: #64748b;
        margin-top: 0.4rem;
        line-height: 1.45;
    }

    .hrd-summary-card--identity strong { font-size: 1.2rem; }

    .hrd-card-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .hrd-guidance-strip {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1rem;
    }

    .hrd-guidance-strip div {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.9rem;
        color: #64748b;
        line-height: 1.65;
        font-size: 0.88rem;
    }

    .hrd-guidance-strip strong {
        color: #1e293b;
        margin-right: 0.25rem;
    }

    .hrd-history-layout {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1rem;
        align-items: stretch;
    }

    .hrd-trend-card,
    .hrd-ethic-card,
    .hrd-compact-list-card {
        padding: 1.25rem;
    }

    .hrd-ethic-card {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .hrd-ethic-card p {
        margin: 0;
        color: #7c2d12;
        line-height: 1.8;
        font-size: 0.9rem;
    }

    .hrd-ethic-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .hrd-ethic-list span {
        background: #ffedd5;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .hrd-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }

    .hrd-section-head .card-title { margin-bottom: 0.3rem; }

    .hrd-section-head p {
        margin: 0;
        color: #64748b;
        line-height: 1.6;
        font-size: 0.88rem;
    }

    .hrd-mini-help {
        flex-shrink: 0;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 0.45rem 0.7rem;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .hrd-chart { min-height: 220px; }

    .hrd-history-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .hrd-history-item {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: white;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.035);
    }

    .hrd-history-item summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
    }

    .hrd-history-item summary::-webkit-details-marker { display: none; }

    .hrd-row-left,
    .hrd-row-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .hrd-status-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
        box-shadow: 0 0 0 4px #f8fafc;
    }

    .hrd-row-date {
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 800;
        margin-bottom: 0.2rem;
    }

    .hrd-row-title {
        font-size: 1rem;
        font-weight: 950;
        line-height: 1.25;
    }

    .hrd-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.76rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .hrd-chip--neutral {
        background: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }

    .hrd-chevron {
        color: #94a3b8;
        font-weight: 900;
        transition: transform 0.2s ease;
    }

    .hrd-history-item[open] .hrd-chevron { transform: rotate(180deg); }

    .hrd-detail-panel {
        border-top: 1px solid #e2e8f0;
        padding: 1rem;
        background: #f8fafc;
    }

    .hrd-detail-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 0.9rem;
        margin-bottom: 0.9rem;
    }

    .hrd-detail-box {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.95rem;
    }

    .hrd-detail-label {
        display: block;
        color: #334155;
        font-size: 0.76rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.45rem;
    }

    .hrd-detail-box p {
        margin: 0;
        color: #64748b;
        line-height: 1.7;
        font-size: 0.88rem;
    }

    .hrd-area-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.7rem;
    }

    .hrd-area-header small {
        color: #94a3b8;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .hrd-area-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .hrd-area-chip,
    .hrd-empty-chip {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        border-radius: 999px;
        padding: 0.35rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .hrd-area-chip--more { background: #e2e8f0; }

    .hrd-empty-state {
        text-align: center;
        padding: 3rem;
    }

    .hrd-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.4rem;
        font-weight: 900;
    }

    @media (max-width: 960px) {
        .hrd-summary-grid,
        .hrd-guidance-strip,
        .hrd-history-layout,
        .hrd-detail-grid {
            grid-template-columns: 1fr;
        }

        .hrd-history-item summary,
        .hrd-row-right,
        .hrd-area-header,
        .hrd-section-head--split {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
