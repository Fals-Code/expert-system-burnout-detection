@extends('layouts.app')

@section('title', 'Laporan Burnout - SanctuaryHub')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap;">
        <h1 class="page-title" style="margin:0;">Laporan Agregat Divisi</h1>
        <button class="btn-cta" style="background:#0f766e; border:none;" onclick="exportToExcel('rekapDivisiTable', 'Rekap_SanctuaryHub_Divisi_{{ now()->format('Y-m-d') }}.xlsx')">Ekspor Excel</button>
    </div>

    @php
        $rows = collect($laporan_divisi);
        $totalAll = $rows->sum('total');
        $totalTinggi = $rows->sum('tinggi');
        $totalSedang = $rows->sum('sedang');
        $totalRendah = $rows->sum('rendah');
        $totalTidak = $rows->sum('tidak');
    @endphp

    <div style="display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        @foreach ([
            ['label' => 'Total', 'value' => $totalAll, 'color' => 'var(--color-primary)'],
            ['label' => 'Tinggi', 'value' => $totalTinggi, 'color' => '#ef4444'],
            ['label' => 'Sedang', 'value' => $totalSedang, 'color' => '#f59e0b'],
            ['label' => 'Rendah', 'value' => $totalRendah, 'color' => '#eab308'],
            ['label' => 'Tidak Terindikasi', 'value' => $totalTidak, 'color' => '#10b981'],
        ] as $card)
            <div class="content-card" style="border-bottom:4px solid {{ $card['color'] }}; padding:1.25rem;">
                <div style="font-size:2rem; font-weight:900; line-height:1;">{{ $card['value'] }}</div>
                <div style="font-size:0.75rem; color:var(--color-gray-500); font-weight:800; text-transform:uppercase;">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
        <div class="content-card">
            <h2 class="card-title">Komposisi Burnout per Divisi</h2>
            <div id="divisiBarChart"></div>
        </div>
        <div class="content-card">
            <h2 class="card-title">Distribusi Global</h2>
            <div id="globalPieChart"></div>
        </div>
    </div>

    <div class="content-card">
        <h2 class="card-title">Rekapitulasi Deteksi</h2>
        <p style="color:var(--color-gray-500); font-size:0.9rem; margin-bottom:1.25rem;">
            Grup dengan kurang dari 3 data ditandai sebagai agregat kecil agar tidak mudah mengidentifikasi individu.
        </p>
        <div class="table-container">
            <table class="data-table" id="rekapDivisiTable">
                <thead>
                    <tr>
                        <th>Divisi</th>
                        <th>Total</th>
                        <th>Tinggi</th>
                        <th>Sedang</th>
                        <th>Rendah</th>
                        <th>Tidak Terindikasi</th>
                        <th>Catatan Privasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporan_divisi as $row)
                        <tr>
                            <td style="font-weight:800; color:var(--color-primary);">{{ $row['divisi'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td><span class="badge" style="background:#fee2e2; color:#991b1b;">{{ $row['tinggi'] }}</span></td>
                            <td><span class="badge" style="background:#fef3c7; color:#92400e;">{{ $row['sedang'] }}</span></td>
                            <td><span class="badge" style="background:#fef9c3; color:#854d0e;">{{ $row['rendah'] }}</span></td>
                            <td><span class="badge" style="background:#dcfce7; color:#166534;">{{ $row['tidak'] }}</span></td>
                            <td>{{ $row['suppressed'] ? 'Agregat kecil - jangan ditafsirkan individual' : 'Aman sebagai agregat' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:2rem; color:var(--color-gray-400);">Belum ada data deteksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#64748B';
    const divisiData = @json($laporan_divisi);
    const divisiNames = divisiData.map(d => d.divisi);

    const bar = new ApexCharts(document.querySelector('#divisiBarChart'), {
        series: [
            { name: 'Tinggi', data: divisiData.map(d => d.tinggi) },
            { name: 'Sedang', data: divisiData.map(d => d.sedang) },
            { name: 'Rendah', data: divisiData.map(d => d.rendah) },
            { name: 'Tidak Terindikasi', data: divisiData.map(d => d.tidak) },
        ],
        chart: { type: 'bar', height: 280, stacked: true, toolbar: { show: false }, background: 'transparent' },
        colors: ['#ef4444', '#f59e0b', '#eab308', '#10b981'],
        xaxis: { categories: divisiNames, labels: { style: { colors: textColor, fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: textColor, fontSize: '11px' } } },
        legend: { position: 'top', labels: { colors: textColor } },
        dataLabels: { enabled: false },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    bar.render();
    if (window.activeCharts) window.activeCharts.push(bar);

    const donut = new ApexCharts(document.querySelector('#globalPieChart'), {
        series: [{{ $totalTinggi }}, {{ $totalSedang }}, {{ $totalRendah }}, {{ $totalTidak }}],
        labels: ['Tinggi', 'Sedang', 'Rendah', 'Tidak Terindikasi'],
        chart: { type: 'donut', height: 280, background: 'transparent' },
        colors: ['#ef4444', '#f59e0b', '#eab308', '#10b981'],
        legend: { position: 'bottom', labels: { colors: textColor } },
        dataLabels: { enabled: false },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    donut.render();
    if (window.activeCharts) window.activeCharts.push(donut);
});
</script>
@endpush

<style>
    @media (max-width: 1024px) {
        [style*="grid-template-columns:repeat(5"], [style*="grid-template-columns:2fr 1fr"] { grid-template-columns:1fr !important; }
    }
</style>
