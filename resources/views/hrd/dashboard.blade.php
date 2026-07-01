@extends('layouts.app')

@section('title', 'HRD Dashboard - SanctuaryHub')

@section('content')
    <h1 class="page-title">Monitoring Agregat Burnout</h1>

    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:1rem; margin-top:1rem;">
        @foreach ([
            ['label' => 'Total Deteksi', 'value' => $total_konsultasi, 'color' => 'var(--color-primary)'],
            ['label' => 'Burnout Tinggi', 'value' => $stats['tinggi'], 'color' => '#ef4444'],
            ['label' => 'Burnout Sedang', 'value' => $stats['sedang'], 'color' => '#f59e0b'],
            ['label' => 'Burnout Rendah', 'value' => $stats['rendah'], 'color' => '#eab308'],
            ['label' => 'Tidak Terindikasi', 'value' => $stats['tidak'], 'color' => '#10b981'],
        ] as $card)
            <div class="content-card stat-card" style="border-bottom:4px solid {{ $card['color'] }};">
                <div class="stat-value">{{ $card['value'] }}</div>
                <div class="stat-label">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1.5rem;">
        <div class="content-card">
            <h2 class="card-title">Tren Deteksi Bulanan</h2>
            <div id="trendChart"></div>
        </div>
        <div class="content-card">
            <h2 class="card-title">Distribusi Empat Kategori</h2>
            <div id="distributionChart"></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-top:1.5rem;">
        <div class="content-card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2 class="card-title" style="margin:0;">Aktivitas Deteksi Terbaru</h2>
                <a href="{{ route('hrd.reports') }}" style="font-size:0.85rem; color:var(--color-primary); font-weight:800; text-decoration:none;">Lihat laporan</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Waktu</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:800;">Check-in karyawan</div>
                                    <div style="font-size:0.75rem; color:var(--color-gray-500);">{{ $item->user->divisi->nama ?? 'N/A' }}</div>
                                </td>
                                <td>{{ $item->created_at->diffForHumans() }}</td>
                                <td>
                                    <span class="badge" style="background:{{ $item->diagnosa->bg_light }}; color:{{ $item->diagnosa->color }}; font-weight:800;">
                                        {{ $item->diagnosa->nama }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:2rem; color:var(--color-gray-400);">Belum ada aktivitas deteksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <h2 class="card-title">Info HRD</h2>
            <div style="padding:1.25rem; background:#f8fafc; border-radius:12px; margin-bottom:1rem; border-left:4px solid var(--color-primary);">
                <h4 style="margin:0 0 0.5rem; font-size:0.9rem;">Total Karyawan</h4>
                <div style="font-size:1.5rem; font-weight:900;">{{ $total_karyawan }} <span style="font-size:0.8rem; color:var(--color-gray-500); font-weight:500;">Orang</span></div>
            </div>
            <p style="font-size:0.85rem; color:var(--color-gray-600); line-height:1.6;">
                Dashboard HRD hanya menampilkan data agregat dan kategori umum. Jawaban mentah serta hasil individual tidak ditampilkan untuk menjaga privasi karyawan.
            </p>
            <a href="{{ route('hrd.employees') }}" class="btn-cta" style="width:100%; text-align:center; display:block; padding:0.75rem; margin-top:1rem;">Monitoring Agregat</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const trendChart = new ApexCharts(document.querySelector("#trendChart"), {
        series: [{ name: "Total Deteksi", data: {!! json_encode($chart_trends->pluck('total')) !!} }],
        chart: { height: 280, type: 'area', toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#1E3A5F'],
        xaxis: { categories: {!! json_encode($chart_trends->pluck('month')) !!} },
        fill: { gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    trendChart.render();
    if (window.activeCharts) window.activeCharts.push(trendChart);

    const distributionChart = new ApexCharts(document.querySelector("#distributionChart"), {
        series: [{{ $stats['tinggi'] }}, {{ $stats['sedang'] }}, {{ $stats['rendah'] }}, {{ $stats['tidak'] }}],
        labels: ['Burnout Tinggi', 'Burnout Sedang', 'Burnout Rendah', 'Tidak Terindikasi'],
        chart: { type: 'donut', height: 280 },
        colors: ['#ef4444', '#f59e0b', '#eab308', '#10b981'],
        legend: { position: 'bottom' },
        plotOptions: { pie: { donut: { size: '70%' } } },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    distributionChart.render();
    if (window.activeCharts) window.activeCharts.push(distributionChart);
});
</script>
@endpush

<style>
    .stat-card { padding:1.25rem; }
    .stat-value { font-size:1.75rem; font-weight:900; line-height:1; margin-bottom:0.4rem; }
    .stat-label { font-size:0.72rem; color:var(--color-gray-500); font-weight:800; text-transform:uppercase; letter-spacing:0.04em; }
    @media (max-width: 1024px) {
        .stats-grid, [style*="grid-template-columns:1fr 1fr"], [style*="grid-template-columns:2fr 1fr"] { grid-template-columns:1fr !important; }
    }
</style>
