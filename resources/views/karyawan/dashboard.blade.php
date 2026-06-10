@extends('layouts.app')

@section('title', 'Dashboard Karyawan – Sanctuary Hub')

@section('content')
@php
    $lastDiagnosisId = (int) ($last_result->diagnosa->id ?? 0);
    $lastLabel = match ($lastDiagnosisId) {
        1 => 'Keseimbangan Stabil',
        2 => 'Butuh Dukungan Ekstra',
        3 => 'Perlu Pemantauan',
        4 => 'Perhatian Ringan',
        default => 'Belum ada check-in',
    };

    $trendLabel = match ($trend_direction) {
        'up' => 'Beban meningkat',
        'down' => 'Cenderung membaik',
        default => 'Relatif stabil',
    };

    $trendColor = match ($trend_direction) {
        'up' => '#f97316',
        'down' => '#16a34a',
        default => '#2563eb',
    };

    $latestScore = $last_result ? number_format($last_result->cf_final * 100, 1) : '-';
@endphp

<div class="quiet-page employee-dashboard">
    <header class="quiet-hero" data-intro="Dashboard ini adalah ringkasan pribadi untuk check-in kerja dan rekomendasi dukungan." data-step="1">
        <p class="quiet-kicker">Dashboard Pribadi</p>
        <div class="quiet-hero-row">
            <div>
                <h1>{{ $greet }}, {{ Auth::user()->nama }}</h1>
                <p>Ringkasan kondisi kerja Anda, riwayat check-in, dan langkah kecil yang bisa dilakukan minggu ini.</p>
            </div>
            <div class="quiet-actions">
                <a href="{{ route('karyawan.deteksi.intro') }}" class="quiet-btn quiet-btn-primary" data-intro="Mulai check-in kerja singkat atau lanjutkan sesi tersimpan." data-step="2">Mulai Check-in</a>
                <a href="{{ route('karyawan.history') }}" class="quiet-btn">Riwayat Saya</a>
            </div>
        </div>
    </header>

    <section class="quiet-metrics" aria-label="Ringkasan check-in">
        <div>
            <span>Total Check-in</span>
            <strong>{{ $total_deteksi }}</strong>
        </div>
        <div>
            <span>Perubahan Terakhir</span>
            <strong style="color:{{ $trendColor }};">{{ $trendLabel }}</strong>
        </div>
        <div>
            <span>Kondisi Terakhir</span>
            <strong>{{ $lastLabel }}</strong>
        </div>
        <div>
            <span>Skor Sistem</span>
            <strong>{{ $latestScore }}</strong>
        </div>
    </section>

    @if($warning_flag)
        <section class="quiet-note quiet-note-warning" data-intro="Jika ada perubahan cukup terasa, dashboard memberi pengingat yang tenang dan suportif." data-step="3">
            <strong>Kondisi minggu ini perlu perhatian.</strong>
            <span>Ada kenaikan skor yang cukup terasa dibanding check-in sebelumnya. Gunakan ini sebagai pengingat untuk mengatur prioritas atau mendiskusikan beban kerja jika berlanjut.</span>
        </section>
    @endif

    <div class="quiet-layout">
        <main class="quiet-main">
            <section class="quiet-section" data-intro="Grafik ini membantu melihat perubahan kondisi pribadi dari waktu ke waktu." data-step="4">
                <div class="quiet-section-head">
                    <div>
                        <h2>Riwayat Pribadi</h2>
                        <p>Perubahan skor dari waktu ke waktu. Angka ini dipakai sebagai sinyal refleksi, bukan nilai performa.</p>
                    </div>
                </div>

                @if(count($chart_dates) > 0)
                    <div id="longitudinalTrendChart" class="quiet-chart"></div>
                @else
                    <div class="quiet-empty">
                        <strong>Belum ada grafik pribadi</strong>
                        <span>Isi check-in pertama untuk mulai melihat perkembangan kondisi kerja Anda.</span>
                    </div>
                @endif
            </section>

            <section class="quiet-section">
                <div class="quiet-section-head">
                    <div>
                        <h2>Transparansi Data</h2>
                        <p>Bagian ini menjelaskan data yang Anda lihat dan bagaimana sistem memakainya.</p>
                    </div>
                </div>

                <div class="quiet-two-col">
                    <div>
                        <h3>Yang Anda lihat</h3>
                        <ul>
                            <li>Riwayat pribadi</li>
                            <li>Insight kondisi kerja</li>
                            <li>Rekomendasi dukungan</li>
                        </ul>
                    </div>
                    <div>
                        <h3>Yang ditekankan sistem</h3>
                        <ul>
                            <li>Pola beban kerja</li>
                            <li>Kebutuhan dukungan</li>
                            <li>Perbaikan lingkungan kerja</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <aside class="quiet-aside">
            <section class="quiet-section" data-intro="Saran kecil yang bisa dilakukan tanpa membuat proses terasa menghakimi." data-step="5">
                <h2>Rekomendasi Minggu Ini</h2>
                <ol class="quiet-steps">
                    <li>
                        <strong>Atur prioritas</strong>
                        <span>Catat satu tugas yang paling menguras energi, lalu pilih langkah kecil pertama.</span>
                    </li>
                    <li>
                        <strong>Jaga ritme kerja</strong>
                        <span>Gunakan jeda pendek agar energi tidak turun drastis di tengah hari.</span>
                    </li>
                    <li>
                        <strong>Minta dukungan bila perlu</strong>
                        <span>Diskusikan prioritas atau hambatan jika beban terasa berat berulang.</span>
                    </li>
                </ol>
            </section>

            <section class="quiet-section">
                <h2>Konteks Kerja</h2>
                <dl class="quiet-data-list">
                    <div><dt>Jam kerja bulan ini</dt><dd>{{ $hrisMetrics['total_hours'] }}h</dd></div>
                    <div><dt>Lembur bulan ini</dt><dd>{{ $hrisMetrics['overtime_hours'] }}h</dd></div>
                    <div><dt>Keterlambatan</dt><dd>{{ $hrisMetrics['late_arrivals'] }}x</dd></div>
                    <div><dt>Sisa cuti</dt><dd>{{ $hrisMetrics['remaining_leaves'] }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($chart_dates) > 0)
        const trendOptions = {
            series: [{
                name: 'Skor Keseimbangan',
                data: {!! json_encode($chart_scores) !!}
            }],
            chart: {
                type: 'area',
                height: 280,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Poppins, sans-serif'
            },
            colors: ['#2563eb'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.18, opacityTo: 0.02, stops: [0, 90, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: {!! json_encode($chart_dates) !!},
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
            grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                y: { formatter: (val) => val.toFixed(1) + ' skor' }
            },
            markers: { size: 4, strokeWidth: 2, strokeColors: '#ffffff', hover: { size: 6 } }
        };

        const chart = new ApexCharts(document.querySelector('#longitudinalTrendChart'), trendOptions);
        chart.render();
    @endif

    if (window.OnboardingHelper && window.OnboardingHelper.shouldShow('karyawan_dashboard')) {
        setTimeout(() => {
            introJs().setOptions({
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Mengerti',
                showStepNumbers: true,
                showBullets: true,
                overlayOpacity: 0.6
            }).start();
        }, 1200);
    }
});
</script>
@endpush

<style>
    .quiet-page { max-width: 1180px; margin: 0 auto; }
    .quiet-hero { padding: 0.75rem 0 1.5rem; border-bottom: 1px solid rgba(148,163,184,.18); }
    .quiet-kicker { margin: 0 0 .65rem; color: #2563eb; font-size: .72rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
    .quiet-hero-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 1.5rem; }
    .quiet-hero h1 { margin: 0; color: #0f172a; font-size: clamp(2rem, 4vw, 3.2rem); line-height: 1.05; letter-spacing: -.06em; }
    .quiet-hero p { max-width: 680px; margin: .8rem 0 0; color: #64748b; line-height: 1.75; }
    .quiet-actions { display: flex; gap: .7rem; flex-wrap: wrap; flex-shrink: 0; }
    .quiet-btn { display: inline-flex; align-items: center; justify-content: center; padding: .8rem 1.15rem; border-radius: 999px; color: #2563eb; background: transparent; text-decoration: none; font-weight: 900; border: none; }
    .quiet-btn-primary { background: #2563eb; color: #ffffff; }
    .quiet-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.25rem; padding: 1.35rem 0; border-bottom: 1px solid rgba(148,163,184,.18); }
    .quiet-metrics span { display:block; color:#94a3b8; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.35rem; }
    .quiet-metrics strong { display:block; color:#0f172a; font-size:1.35rem; line-height:1.25; font-weight:950; }
    .quiet-note { margin: 1.25rem 0 0; padding: 1rem 0; display: flex; gap: .7rem; color:#7c2d12; border-bottom: 1px solid rgba(249,115,22,.22); line-height: 1.65; }
    .quiet-note strong { flex-shrink: 0; color:#9a3412; }
    .quiet-layout { display:grid; grid-template-columns: minmax(0, 1.45fr) minmax(280px, .8fr); gap: 2.5rem; margin-top: 2rem; align-items:start; }
    .quiet-main, .quiet-aside { display:flex; flex-direction:column; gap:2rem; }
    .quiet-section { padding: 0 0 1.5rem; border-bottom: 1px solid rgba(148,163,184,.16); }
    .quiet-section h2 { margin:0 0 .4rem; color:#0f172a; font-size:1.25rem; font-weight:950; letter-spacing:-.03em; }
    .quiet-section h3 { margin:0 0 .5rem; color:#1e293b; font-size:.95rem; font-weight:900; }
    .quiet-section p { margin:0; color:#64748b; line-height:1.7; font-size:.9rem; }
    .quiet-chart { min-height: 280px; margin-top: 1rem; }
    .quiet-empty { padding: 2.25rem 0; color:#64748b; display:flex; flex-direction:column; gap:.35rem; }
    .quiet-two-col { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-top:1rem; }
    .quiet-two-col ul, .quiet-steps { margin:0; padding-left:1.15rem; color:#64748b; line-height:1.8; }
    .quiet-steps { display:flex; flex-direction:column; gap:.75rem; }
    .quiet-steps li span { display:block; color:#64748b; line-height:1.6; font-size:.88rem; }
    .quiet-data-list { margin:1rem 0 0; display:flex; flex-direction:column; gap:.8rem; }
    .quiet-data-list div { display:flex; justify-content:space-between; gap:1rem; padding-bottom:.8rem; border-bottom:1px solid rgba(148,163,184,.14); }
    .quiet-data-list dt { color:#64748b; font-size:.85rem; }
    .quiet-data-list dd { margin:0; color:#0f172a; font-weight:950; }
    @media (max-width: 960px) {
        .quiet-hero-row, .quiet-layout { flex-direction: column; display:flex; align-items:stretch; }
        .quiet-metrics, .quiet-two-col { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 640px) {
        .quiet-metrics, .quiet-two-col { grid-template-columns:1fr; }
    }
</style>
@endsection