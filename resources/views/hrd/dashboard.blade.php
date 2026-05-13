@extends('layouts.app')

@section('title', 'HRD Dashboard – BurnoutXpert')

@section('content')
    <h1 class="page-title">Monitoring Kesehatan Mental</h1>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-top: 1rem;">
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_konsultasi }}</div>
                <div class="stat-label">Total Deteksi Selesai</div>
            </div>
        </div>
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_karyawan }}</div>
                <div class="stat-label">Total Karyawan Terdaftar</div>
            </div>
        </div>
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">0</div>
                <div class="stat-label">Kasus Perlu Perhatian</div>
            </div>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <div class="content-card">
            <div class="card-header" style="padding: 0; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">📈 Aktivitas Deteksi Terbaru</h2>
                <a href="#" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700;">Lihat Laporan Lengkap</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Divisi</th>
                        <th>Waktu</th>
                        <th>Hasil Diagnosa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $h->user->nama }}</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-400);">{{ $h->user->email }}</div>
                        </td>
                        <td>{{ $h->user->divisi->nama ?? 'N/A' }}</td>
                        <td>{{ $h->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td>
                            <span class="badge" style="background: {{ $h->diagnosa->bg_light }}; color: {{ $h->diagnosa->color }};">
                                {{ $h->diagnosa->nama }} ({{ number_format($h->cf_final * 100, 0) }}%)
                            </span>
                        </td>
                        <td><span class="badge badge--success">SELESAI</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-gray-400);">Belum ada riwayat deteksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
