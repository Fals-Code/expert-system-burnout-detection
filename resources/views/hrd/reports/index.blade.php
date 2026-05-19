@extends('layouts.app')

@section('title', 'Laporan Burnout – BurnoutXpert')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0;">Laporan Analisis Divisi</h1>
        <div style="display: flex; gap: 0.75rem;">
            <button class="btn-cta" style="background: #0f766e; border: none; display: inline-flex; align-items: center; gap: 6px;" onclick="exportToExcel('rekapDivisiTable', 'Rekap_Burnout_Divisi_{{ now()->format('Y-m-d') }}.xlsx')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Ekspor Excel
            </button>
            <button class="btn-cta" style="background: #10b981; border: none;" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak PDF
            </button>
        </div>
    </div>

    {{-- ── Chart Section ── --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
        {{-- Summary Stats --}}
        @php
            $totalAll    = collect($laporan_divisi)->sum('total');
            $totalTinggi = collect($laporan_divisi)->sum('tinggi');
            $totalSedang = collect($laporan_divisi)->sum('sedang');
            $totalRendah = collect($laporan_divisi)->sum('rendah');
        @endphp
        <div class="content-card stat-card" style="border-bottom: 4px solid var(--color-primary); padding: 1.5rem;">
            <div style="color: var(--color-primary); margin-bottom: 0.5rem;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </div>
            <div style="font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 0.25rem;">{{ $totalAll }}</div>
            <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total Deteksi</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #ef4444; padding: 1.5rem;">
            <div style="color: #ef4444; margin-bottom: 0.5rem;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <div style="font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 0.25rem; color: #ef4444;">{{ $totalTinggi }}</div>
            <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Burnout Tinggi/Sangat Tinggi</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #10b981; padding: 1.5rem;">
            <div style="color: #10b981; margin-bottom: 0.5rem;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div style="font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 0.25rem; color: #10b981;">{{ $totalRendah }}</div>
            <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kondisi Normal/Rendah</div>
        </div>
    </div>

    {{-- ── Visual Charts ── --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Komposisi Burnout per Divisi
            </h2>
            <p style="font-size: 0.8rem; color: var(--color-gray-400); margin-bottom: 1rem;">Distribusi tingkat burnout berdasarkan divisi (stacked bar chart).</p>
            <div id="divisiBarChart"></div>
        </div>
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                Distribusi Global
            </h2>
            <p style="font-size: 0.8rem; color: var(--color-gray-400); margin-bottom: 1rem;">Proporsi keseluruhan dari semua divisi.</p>
            <div id="globalPieChart"></div>
        </div>
    </div>

    {{-- ── Data Table ── --}}
    <div class="content-card" style="margin-bottom: 1.5rem;">
        <h2 class="card-title">Rekapitulasi Deteksi — {{ now()->translatedFormat('F Y') }}</h2>
        <p style="color: var(--color-gray-500); font-size: 0.9rem; margin-bottom: 2rem;">
            Laporan ini merangkum tingkat burnout karyawan di setiap divisi.
        </p>

        <div class="table-container">
            <table class="data-table" id="rekapDivisiTable">
                <thead>
                    <tr>
                        <th>Divisi</th>
                        <th>Total Asesmen</th>
                        <th>Burnout Tinggi</th>
                        <th>Burnout Sedang</th>
                        <th>Burnout Rendah</th>
                        <th>Risk Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporan_divisi as $l)
                    @php
                        $riskScore = $l['total'] > 0 ? round(($l['tinggi'] / $l['total']) * 100) : 0;
                        $riskColor = $riskScore >= 50 ? '#ef4444' : ($riskScore >= 25 ? '#f59e0b' : '#10b981');
                    @endphp
                    <tr>
                        <td style="font-weight: 700; color: var(--color-primary);">{{ $l['divisi'] }}</td>
                        <td>{{ $l['total'] }} orang</td>
                        <td><span class="badge" style="background: #fee2e2; color: #991b1b;">{{ $l['tinggi'] }}</span></td>
                        <td><span class="badge" style="background: #fef3c7; color: #92400e;">{{ $l['sedang'] }}</span></td>
                        <td><span class="badge" style="background: #dcfce7; color: #166534;">{{ $l['rendah'] }}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; height: 6px; background: var(--color-gray-100); border-radius: 3px; overflow: hidden;">
                                    <div style="width: {{ $riskScore }}%; height: 100%; background: {{ $riskColor }}; border-radius: 3px; transition: width 1s ease;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 700; color: {{ $riskColor }}; min-width: 3ch;">{{ $riskScore }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card">
        <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"></path><path d="M10 22h4"></path><path d="M15 13a5 5 0 0 0 3-4.5 4.5 4.5 0 0 0-9 0 4.5 4.5 0 0 0 3 4.5V15h3v-2z"></path></svg>
            Analisis & Rekomendasi HRD
        </h2>
        @php
            $riskiestDivisi = collect($laporan_divisi)->sortByDesc('tinggi')->first();
            $safestDivisi   = collect($laporan_divisi)->sortBy('tinggi')->first();
        @endphp
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div style="background: #fef2f2; padding: 1.25rem; border-radius: 12px; border-left: 4px solid #ef4444;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #991b1b; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Divisi Perlu Perhatian
                </h4>
                <p style="margin: 0; font-size: 0.9rem; color: #7f1d1d; font-weight: 700;">{{ $riskiestDivisi['divisi'] ?? '-' }}</p>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: #9f1239;">{{ $riskiestDivisi['tinggi'] ?? 0 }} karyawan dengan burnout tinggi — segera jadwalkan konseling.</p>
            </div>
            <div style="background: #f0fdf4; padding: 1.25rem; border-radius: 12px; border-left: 4px solid #10b981;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #166534; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Divisi Terbaik
                </h4>
                <p style="margin: 0; font-size: 0.9rem; color: #14532d; font-weight: 700;">{{ $safestDivisi['divisi'] ?? '-' }}</p>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: #166534;">Kondisi paling stabil — jadikan model praktik terbaik manajemen tim.</p>
            </div>
        </div>
        <div style="background: #f8fafc; padding: 1.25rem; border-radius: 12px; margin-top: 1rem; font-size: 0.9rem; color: #334155; line-height: 1.6;">
            <p style="margin: 0;">Berdasarkan data di atas, identifikasi divisi dengan angka <strong>Burnout Tinggi</strong> yang signifikan. 
            Disarankan untuk menjadwalkan sesi konseling atau <em>team building</em> bagi divisi yang terdampak. 
            Gunakan <strong>Risk Score</strong> sebagai indikator prioritas intervensi.</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#64748B';

    const divisiData = @json($laporan_divisi);
    const divisiNames  = divisiData.map(d => d.divisi);
    const tinggiData   = divisiData.map(d => d.tinggi);
    const sedangData   = divisiData.map(d => d.sedang);
    const rendahData   = divisiData.map(d => d.rendah);

    // ── Stacked Bar Chart per Divisi ──
    const chart1 = new ApexCharts(document.querySelector('#divisiBarChart'), {
        series: [
            { name: 'Burnout Tinggi',  data: tinggiData  },
            { name: 'Burnout Sedang',  data: sedangData  },
            { name: 'Kondisi Normal',  data: rendahData  },
        ],
        chart: {
            type: 'bar',
            height: 280,
            stacked: true,
            toolbar: { show: false },
            background: 'transparent'
        },
        colors: ['#ef4444', '#f59e0b', '#10b981'],
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 4,
                columnWidth: '55%'
            }
        },
        xaxis: {
            categories: divisiNames,
            labels: { style: { colors: textColor, fontSize: '11px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: { style: { colors: textColor, fontSize: '11px' } }
        },
        grid: {
            borderColor: isDark ? '#1E293B' : '#F1F5F9',
            strokeDashArray: 4
        },
        legend: {
            position: 'top',
            fontSize: '12px',
            labels: { colors: textColor }
        },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        dataLabels: { enabled: false },
        theme: {
            mode: isDark ? 'dark' : 'light'
        }
    });
    chart1.render();
    if (window.activeCharts) window.activeCharts.push(chart1);

    // ── Global Donut Chart ──
    const chart2 = new ApexCharts(document.querySelector('#globalPieChart'), {
        series: [{{ $totalTinggi }}, {{ $totalSedang }}, {{ $totalRendah }}],
        labels: ['Tinggi/Sangat Tinggi', 'Sedang', 'Normal/Rendah'],
        chart: {
            type: 'donut',
            height: 280,
            background: 'transparent'
        },
        colors: ['#ef4444', '#f59e0b', '#10b981'],
        legend: {
            position: 'bottom',
            fontSize: '11px',
            labels: { colors: textColor }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            color: textColor,
                            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        theme: {
            mode: isDark ? 'dark' : 'light'
        }
    });
    chart2.render();
    if (window.activeCharts) window.activeCharts.push(chart2);
});
</script>
@endpush
