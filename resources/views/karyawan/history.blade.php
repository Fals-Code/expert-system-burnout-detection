@extends('layouts.app')

@section('title', 'Riwayat Deteksi – BurnoutXpert')

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
    <h1 class="page-title" style="margin: 0;">Riwayat Deteksi Anda</h1>
    @if(count($history) > 0)
    <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" style="padding: 0.6rem 1.25rem; font-size: 0.875rem;">
        + Deteksi Baru
    </a>
    @endif
</div>

@if(count($history) === 0)
    <div class="content-card" style="text-align: center; padding: 3rem;">
        <div style="color: #cbd5e1; margin-bottom: 1rem;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
        </div>
        <h3 style="color: var(--color-gray-700);">Belum Ada Riwayat</h3>
        <p style="color: var(--color-gray-500); margin-bottom: 1.5rem;">Anda belum pernah melakukan deteksi burnout sebelumnya.</p>
        <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta">Mulai Deteksi Sekarang</a>
    </div>
@else
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                Tren Tingkat Burnout
            </h2>
            <p style="font-size: 0.8rem; color: var(--color-gray-400); margin-bottom: 1rem;">
                Visualisasi perubahan tingkat burnout Anda dari waktu ke waktu berdasarkan riwayat deteksi.
            </p>
            <div id="trendChart"></div>
        </div>
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                Distribusi Hasil
            </h2>
            <p style="font-size: 0.8rem; color: var(--color-gray-400); margin-bottom: 1rem;">
                Proporsi setiap level burnout dari seluruh riwayat Anda.
            </p>
            <div id="distributionChart"></div>

            {{-- Summary Stats --}}
            <div style="margin-top: 1rem; border-top: 1px solid var(--color-gray-100); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.825rem;">
                    <span style="color: var(--color-gray-500);">Total Deteksi</span>
                    <strong>{{ count($history) }}x</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.825rem;">
                    <span style="color: var(--color-gray-500);">Rata-rata CF</span>
                    <strong>{{ number_format(collect($history)->avg('cf_final') * 100, 1) }}%</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.825rem;">
                    <span style="color: var(--color-gray-500);">Deteksi Terakhir</span>
                    <strong>{{ collect($history)->first()->created_at->diffForHumans() }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Timeline Riwayat ── --}}
    <div class="content-card">
        <h2 class="card-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Riwayat Lengkap
        </h2>
        <div class="timeline">
            @foreach($history as $i => $h)
                <div style="position: relative; padding-left: 2rem; margin-bottom: 1.5rem;">
                    {{-- Timeline dot --}}
                    <div style="position: absolute; left: 0; top: 0.75rem; width: 12px; height: 12px; border-radius: 50%; background: {{ $h->diagnosa->color }}; box-shadow: 0 0 0 3px {{ $h->diagnosa->bg_light }};"></div>
                    {{-- Timeline line --}}
                    @if(!$loop->last)
                    <div style="position: absolute; left: 5px; top: 1.5rem; width: 2px; height: calc(100% + 0.75rem); background: var(--color-gray-100);"></div>
                    @endif

                    <div style="border: 1px solid var(--color-gray-100); border-radius: 12px; padding: 1.25rem; border-left: 4px solid {{ $h->diagnosa->color }}; background: var(--color-bg-card); transition: box-shadow 0.2s ease;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-400); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                                    {{ $h->created_at->translatedFormat('d F Y, H:i') }}
                                </div>
                                <h3 style="margin: 0; font-size: 1rem; color: {{ $h->diagnosa->color }};">
                                    {{ $h->diagnosa->nama }}
                                </h3>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.5rem; font-weight: 900; color: {{ $h->diagnosa->color }}; line-height: 1;">
                                        {{ number_format($h->cf_final * 100, 1) }}%
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--color-gray-400);">Akurasi CF</div>
                                </div>
                                <span class="badge" style="background: {{ $h->diagnosa->bg_light }}; color: {{ $h->diagnosa->color }}; font-weight: 800; font-size: 0.75rem;">
                                    {{ $h->diagnosa->tingkat }}
                                </span>
                            </div>
                        </div>

                        @if($h->gejala->isNotEmpty())
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: var(--color-gray-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Gejala Dilaporkan:</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                @foreach($h->gejala->take(4) as $g)
                                    <span style="background: var(--color-gray-50); border: 1px solid var(--color-gray-100); color: var(--color-gray-600); font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 50px; font-weight: 500;">{{ $g->nama }}</span>
                                @endforeach
                                @if($h->gejala->count() > 4)
                                    <span style="background: var(--color-gray-100); color: var(--color-gray-500); font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 50px;">+{{ $h->gejala->count() - 4 }} lainnya</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div style="display: flex; justify-content: flex-end;">
                            <a href="{{ route('karyawan.hasil') }}?id={{ $h->id }}" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                Lihat Detail & Rekomendasi
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if(count($history) > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#64748B';
    const gridColor = isDark ? '#1E293B' : '#F1F5F9';

    // ── Trend Chart (Area) ──
    const trendData = @json($chartTrend);

    const trendOptions = {
        series: [{
            name: 'CF Burnout (%)',
            data: trendData.map(d => parseFloat((d.cf * 100).toFixed(1)))
        }],
        chart: {
            type: 'area',
            height: 220,
            toolbar: { show: false },
            sparkline: { enabled: false },
            background: 'transparent',
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        colors: ['#F4845F'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 }
        },
        markers: {
            size: 5,
            colors: ['#F4845F'],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: { size: 7 }
        },
        xaxis: {
            categories: trendData.map(d => d.date),
            labels: {
                style: { colors: textColor, fontSize: '10px' },
                rotate: -30,
                rotateAlways: trendData.length > 5
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            min: 0,
            max: 100,
            labels: {
                style: { colors: textColor, fontSize: '11px' },
                formatter: v => v + '%'
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: v => v + '% (CF)' }
        },
        annotations: {
            yaxis: [
                { y: 25, borderColor: '#F59E0B', label: { text: 'Threshold (25%)', style: { color: '#F59E0B', fontSize: '10px' } } }
            ]
        }
    };

    new ApexCharts(document.querySelector('#trendChart'), trendOptions).render();

    // ── Distribution Chart (Donut) ──
    const distData = @json($chartDistribution);

    const distOptions = {
        series: distData.counts,
        labels: distData.labels,
        chart: {
            type: 'donut',
            height: 200,
            background: 'transparent'
        },
        colors: distData.colors,
        legend: {
            position: 'bottom',
            fontSize: '11px',
            labels: { colors: textColor }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            color: textColor,
                            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) + 'x'
                        }
                    }
                }
            }
        },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        dataLabels: { enabled: false }
    };

    new ApexCharts(document.querySelector('#distributionChart'), distOptions).render();
});
</script>
@endif
@endpush
