@extends('layouts.app')

@section('title', 'Admin Dashboard – BurnoutXpert')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1 class="page-title" style="margin-bottom:0.25rem;">Ringkasan Infrastruktur Sistem</h1>
            <p style="margin:0; color:var(--color-gray-500); font-size:0.9rem;">Pantau ringkasan sistem, tren, dan basis pengetahuan dari satu halaman.</p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}">
            <input type="hidden" name="refresh_knowledge_base" value="1">
            <button type="submit" class="btn-nav" style="display:inline-flex; align-items:center; gap:0.5rem; border:none; background:#0f172a; color:white; padding:0.8rem 1.1rem; border-radius:12px; font-weight:800; cursor:pointer; box-shadow:0 10px 20px rgba(15,23,42,0.16);">
                Refresh Basis Pengetahuan
            </button>
        </form>
    </div>

    @if (session('success'))
        <div style="margin-top:1rem; background:#ecfdf5; border:1px solid #bbf7d0; color:#166534; padding:1rem 1.25rem; border-radius:14px; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1.25rem; margin-top:1rem;">
        <div class="content-card stat-card" style="border-bottom:4px solid #3b82f6;">
            <div class="stat-value">{{ $total_users }}</div>
            <div class="stat-label">Total Pengguna</div>
        </div>
        <div class="content-card stat-card" style="border-bottom:4px solid #10b981;">
            <div class="stat-value">{{ $total_gejala }}</div>
            <div class="stat-label">Basis Gejala</div>
        </div>
        <div class="content-card stat-card" style="border-bottom:4px solid #8b5cf6;">
            <div class="stat-value">{{ $total_aturan }}</div>
            <div class="stat-label">Total Aturan</div>
        </div>
        <div class="content-card stat-card" style="border-bottom:4px solid #64748b;">
            <div class="stat-value">{{ $total_logs }}</div>
            <div class="stat-label">Log Aktivitas</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-top:1.5rem;">
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="content-card">
                <h2 class="card-title">Rata-rata Stress per Divisi (%)</h2>
                <div id="divisionStressChart"></div>
            </div>

            <div class="content-card">
                <h2 class="card-title">Tren Rata-rata Stress Bulanan</h2>
                <div id="monthlyStressChart"></div>
            </div>

            <div class="content-card">
                <h2 class="card-title">Karyawan Berisiko Tinggi</h2>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <thead>
                            <tr style="border-bottom:2px solid #e2e8f0; text-align:left;">
                                <th style="padding:0.5rem; color:var(--color-gray-500);">Nama</th>
                                <th style="padding:0.5rem; color:var(--color-gray-500);">Divisi</th>
                                <th style="padding:0.5rem; color:var(--color-gray-500);">Status</th>
                                <th style="padding:0.5rem; color:var(--color-gray-500); text-align:right;">Stress Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($earlyAlerts as $alert)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:0.6rem 0.5rem; font-weight:700;">{{ $alert['nama'] }}</td>
                                    <td style="padding:0.6rem 0.5rem; color:var(--color-gray-600);">{{ $alert['divisi'] }}</td>
                                    <td style="padding:0.6rem 0.5rem;">{{ $alert['tingkat'] }}</td>
                                    <td style="padding:0.6rem 0.5rem; text-align:right; font-weight:700; color:#dc2626;">{{ $alert['score'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center; padding:2rem; color:var(--color-gray-400);">Tidak ada karyawan berisiko tinggi saat ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="content-card">
                <h2 class="card-title">Distribusi Risiko Burnout</h2>
                <div id="riskDistributionChart" style="min-height:250px;"></div>
            </div>

            <div class="content-card">
                <h2 class="card-title">Informasi Lingkungan</h2>
                <div style="font-size:0.9rem; line-height:2.5;">
                    <div style="display:flex; justify-content:space-between; border-bottom:1px dashed #e2e8f0;"><span style="color:var(--color-gray-500);">Versi PHP</span><span style="font-weight:700;">{{ PHP_VERSION }}</span></div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px dashed #e2e8f0;"><span style="color:var(--color-gray-500);">Laravel Framework</span><span style="font-weight:700; color:#f43f5e;">v{{ app()->version() }}</span></div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px dashed #e2e8f0;"><span style="color:var(--color-gray-500);">Server Time</span><span style="font-weight:700;">{{ now()->format('H:i T') }}</span></div>
                </div>
            </div>

            <div class="content-card" style="border:1px solid #bfdbfe; background:#eff6ff;">
                <h2 class="card-title" style="color:#1d4ed8;">Kontrol Basis Pengetahuan</h2>
                <p style="font-size:0.85rem; color:#475569; line-height:1.7; margin:0 0 1rem 0;">
                    Gunakan tombol ini setelah mengubah bobot pakar, aturan, atau ambang batas agar engine membaca data terbaru tanpa membuka terminal saat demo.
                </p>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <input type="hidden" name="refresh_knowledge_base" value="1">
                    <button type="submit" style="width:100%; background:#2563eb; color:white; border:none; border-radius:12px; padding:0.85rem 1rem; font-weight:900; cursor:pointer;">
                        Refresh Basis Pengetahuan
                    </button>
                </form>
            </div>

            <div class="content-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <h2 class="card-title" style="margin:0;">Log Aktivitas Terbaru</h2>
                    <a href="{{ route('admin.logs') }}" class="btn-nav" style="font-size:0.75rem; padding:0.25rem 0.75rem; text-decoration:none;">Lihat Semua</a>
                </div>
                @forelse($logs as $l)
                    <div style="padding:0.75rem 1rem; border-radius:12px; background:#f8fafc; margin-bottom:0.5rem; border-left:3px solid #e2e8f0;">
                        <div style="font-size:0.85rem; font-weight:700; color:var(--color-primary);">{{ $l->user->nama ?? 'System' }}</div>
                        <div style="font-size:0.8rem; color:var(--color-gray-600);">{{ $l->desc }}</div>
                        <div style="font-size:0.75rem; color:var(--color-gray-400); margin-top:0.25rem;">{{ $l->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div style="text-align:center; padding:3rem; color:var(--color-gray-400);">Belum ada aktivitas tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new ApexCharts(document.querySelector('#divisionStressChart'), {
            series: [{ name: 'Rerata Stress (%)', data: {!! json_encode($divisionAverages) !!} }],
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '40%', distributed: true } },
            xaxis: { categories: {!! json_encode($divisionLabels) !!} },
            colors: ['#3b82f6', '#ea580c', '#10b981', '#8b5cf6', '#e11d48'],
            dataLabels: { enabled: false },
            legend: { show: false },
            yaxis: { max: 100, labels: { formatter: (v) => v + '%' } }
        }).render();

        new ApexCharts(document.querySelector('#monthlyStressChart'), {
            series: [{ name: 'Rerata Stress Global (%)', data: {!! json_encode($trendAverages) !!} }],
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: {!! json_encode($trendMonths) !!} },
            colors: ['#dc2626'],
            markers: { size: 5 },
            yaxis: { max: 100, labels: { formatter: (v) => v + '%' } }
        }).render();

        new ApexCharts(document.querySelector('#riskDistributionChart'), {
            series: {!! json_encode(array_values($riskDistribution)) !!},
            labels: {!! json_encode(array_keys($riskDistribution)) !!},
            chart: { type: 'donut', height: 260 },
            colors: ['#16a34a', '#ca8a04', '#f97316', '#ea580c', '#dc2626'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true }
        }).render();
    });
</script>
@endpush

<style>
    .stat-card { padding:1.5rem; transition:transform 0.3s ease; }
    .stat-card:hover { transform:translateY(-5px); }
    .stat-value { font-size:1.75rem; font-weight:900; line-height:1; margin-bottom:0.25rem; }
    .stat-label { font-size:0.75rem; color:var(--color-gray-500); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
    @media (max-width: 1024px) { .stats-grid { grid-template-columns:repeat(2, 1fr) !important; } }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns:1fr !important; }
        [style*="grid-template-columns:2fr 1fr"] { grid-template-columns:1fr !important; }
    }
</style>
