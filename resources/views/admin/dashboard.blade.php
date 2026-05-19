@extends('layouts.app')

@section('title', 'Admin Dashboard – BurnoutXpert')

@section('content')
    <h1 class="page-title">Ringkasan Infrastruktur Sistem</h1>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-top: 1rem;">
        <div class="content-card stat-card" style="border-bottom: 4px solid #3b82f6;">
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="stat-value">{{ $total_users }}</div>
            <div class="stat-label">Total Pengguna</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #10b981;">
            <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            </div>
            <div class="stat-value">{{ $total_gejala }}</div>
            <div class="stat-label">Basis Gejala</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #8b5cf6;">
            <div class="stat-icon" style="background: #f5f3ff; color: #8b5cf6;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="3" x2="6" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path></svg>
            </div>
            <div class="stat-value">{{ $total_aturan }}</div>
            <div class="stat-label">Total Aturan</div>
        </div>
        <div class="content-card stat-card" style="border-bottom: 4px solid #64748b;">
            <div class="stat-icon" style="background: #f8fafc; color: #64748b;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="stat-value">{{ $total_logs }}</div>
            <div class="stat-label">Log Aktivitas</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Distribution Chart -->
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="16"></line><line x1="15" y1="22" x2="15" y2="16"></line><line x1="9" y1="16" x2="15" y2="16"></line><path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M12 6h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01"></path></svg>
                Distribusi Pengguna per Divisi
            </h2>
            <div id="divisionChart"></div>
        </div>

        <!-- System Info -->
        <div class="content-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Informasi Lingkungan
            </h2>
            <div style="font-size: 0.9rem; line-height: 2.5;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: var(--color-gray-500);">Versi PHP</span>
                    <span style="font-weight: 700;">{{ PHP_VERSION }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: var(--color-gray-500);">Laravel Framework</span>
                    <span style="font-weight: 700; color: #f43f5e;">v{{ app()->version() }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: var(--color-gray-500);">Basis Data</span>
                    <span style="font-weight: 700; color: #10b981;">MySQL (InnoDB)</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0;">
                    <span style="color: var(--color-gray-500);">Server Time</span>
                    <span style="font-weight: 700;">{{ now()->format('H:i T') }}</span>
                </div>
            </div>
            <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbeb; border-radius: 12px; border: 1px solid #fef3c7;">
                <div style="font-size: 0.75rem; color: #92400e; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">Saran Admin:</div>
                <div style="font-size: 0.8rem; color: #b45309; line-height: 1.4;">
                    Lakukan backup basis data secara rutin setiap akhir minggu untuk menjaga integritas data pengetahuan.
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <div class="content-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    Log Aktivitas Terbaru
                </h2>
                <a href="{{ route('admin.logs') }}" class="btn-nav" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; text-decoration: none;">Lihat Semua</a>
            </div>
            <div class="log-list">
                @forelse($logs as $l)
                <div style="padding: 1rem; border-radius: 12px; background: #f8fafc; margin-bottom: 0.75rem; border-left: 3px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-primary);">{{ $l->user->nama ?? 'System' }}</div>
                        <div style="font-size: 0.8rem; color: var(--color-gray-600);">{{ $l->desc }}</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--color-gray-400); text-align: right;">
                        {{ $l->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 3rem; color: var(--color-gray-400);">Belum ada aktivitas tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const options = {
            series: [{
                name: 'Total Pengguna',
                data: {!! json_encode($divisi_stats->pluck('users_count')) !!}
            }],
            chart: { type: 'bar', height: 350, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 10, columnWidth: '50%' } },
            xaxis: { categories: {!! json_encode($divisi_stats->pluck('nama')) !!} },
            colors: ['#3b82f6'],
            dataLabels: { enabled: false },
            theme: {
                mode: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
            }
        };
        const chart = new ApexCharts(document.querySelector("#divisionChart"), options);
        chart.render();
        if (window.activeCharts) window.activeCharts.push(chart);
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
