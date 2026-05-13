@extends('layouts.app')

@section('title', 'HRD Dashboard – BurnoutXpert')

@section('content')
    <h1 class="page-title">Monitoring Kesehatan Mental Karyawan</h1>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-top: 1rem;">
        <div class="content-card stat-card" style="border-bottom: 4px solid var(--color-primary);">
            <div class="stat-icon" style="background: var(--color-primary-light); color: var(--color-primary);">📊</div>
            <div class="stat-value">{{ $total_konsultasi }}</div>
            <div class="stat-label">Total Deteksi</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #ef4444;">
            <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">⚠️</div>
            <div class="stat-value">{{ $stats['tinggi'] }}</div>
            <div class="stat-label">Burnout Tinggi</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #f59e0b;">
            <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">⚖️</div>
            <div class="stat-value">{{ $stats['sedang'] }}</div>
            <div class="stat-label">Burnout Sedang</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #10b981;">
            <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">✅</div>
            <div class="stat-value">{{ $stats['rendah'] }}</div>
            <div class="stat-label">Kondisi Normal</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <div class="content-card">
            <h2 class="card-title">📈 Tren Deteksi Bulanan</h2>
            <div id="trendChart"></div>
        </div>
        <div class="content-card">
            <h2 class="card-title">🎯 Distribusi Kondisi</h2>
            <div id="distributionChart"></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <div class="content-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin: 0;">🕒 Aktivitas Deteksi Terbaru</h2>
                <a href="{{ route('hrd.reports') }}" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700; text-decoration: none;">Lihat Laporan Lengkap →</a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Waktu</th>
                            <th>Hasil Diagnosa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $h)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--color-gray-800);">{{ $h->user->nama }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-500);">{{ $h->user->divisi->nama ?? 'N/A' }}</div>
                            </td>
                            <td style="font-size: 0.85rem;">{{ $h->created_at->diffForHumans() }}</td>
                            <td>
                                <span class="badge" style="background: {{ $h->diagnosa->bg_light }}; color: {{ $h->diagnosa->color }}; font-weight: 700;">
                                    {{ $h->diagnosa->nama }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem; color: var(--color-gray-400);">Belum ada aktivitas deteksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <h2 class="card-title">💡 Info HRD</h2>
            <div style="padding: 1.25rem; background: #f8fafc; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid var(--color-primary);">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem;">Total Karyawan</h4>
                <div style="font-size: 1.5rem; font-weight: 900;">{{ $total_karyawan }} <span style="font-size: 0.8rem; color: var(--color-gray-500); font-weight: 500;">Orang</span></div>
            </div>
            <p style="font-size: 0.85rem; color: var(--color-gray-600); line-height: 1.5;">
                Gunakan dashboard ini untuk memantau tren kesehatan mental secara real-time. Jika angka <strong>Burnout Tinggi</strong> meningkat, segera lakukan intervensi melalui kebijakan HRD.
            </p>
            <div style="margin-top: 1.5rem;">
                <a href="{{ route('hrd.employees') }}" class="btn-cta" style="width: 100%; text-align: center; display: block; padding: 0.75rem;">Monitoring Karyawan</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Line Chart: Trend
        const trendOptions = {
            series: [{
                name: "Total Deteksi",
                data: {!! json_encode($chart_trends->pluck('total')) !!}
            }],
            chart: { height: 280, type: 'area', toolbar: { show: false } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#1E3A5F'],
            xaxis: { categories: {!! json_encode($chart_trends->pluck('month')) !!} },
            fill: { gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } }
        };
        new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

        // Pie Chart: Distribution
        const distOptions = {
            series: [{{ $stats['tinggi'] }}, {{ $stats['sedang'] }}, {{ $stats['rendah'] }}],
            labels: ['Burnout Tinggi', 'Burnout Sedang', 'Normal'],
            chart: { type: 'donut', height: 280 },
            colors: ['#ef4444', '#f59e0b', '#10b981'],
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '70%' } } }
        };
        new ApexCharts(document.querySelector("#distributionChart"), distOptions).render();
    });
</script>
@endpush

<style>
    .stat-card {
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    .stat-label {
        font-size: 0.75rem;
        color: var(--color-gray-500);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
