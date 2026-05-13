@extends('layouts.app')

@section('title', 'Admin Dashboard – BurnoutXpert')

@section('content')
    <h1 class="page-title">Ringkasan Sistem</h1>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-top: 1rem;">
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_users }}</div>
                <div class="stat-label">Total Pengguna</div>
            </div>
        </div>
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_gejala }}</div>
                <div class="stat-label">Total Gejala</div>
            </div>
        </div>
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_aturan }}</div>
                <div class="stat-label">Total Aturan</div>
            </div>
        </div>
        <div class="content-card stat-card">
            <div class="stat-info">
                <div class="stat-value">{{ $total_logs }}</div>
                <div class="stat-label">Total Log Aktivitas</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Log Terbaru -->
        <div class="content-card">
            <div class="card-header" style="padding: 0; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">📋 Log Aktivitas Terbaru</h2>
                <a href="#" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 700;">Lihat Semua</a>
            </div>
            <div class="log-mini-list">
                @forelse($logs as $l)
                <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--color-gray-100); font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; color: var(--color-primary);">{{ $l->user->nama ?? 'System' }}</span>
                        <span style="color: var(--color-gray-400); font-size: 0.75rem;">{{ $l->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="color: var(--color-gray-600);">{{ $l->desc }}</div>
                </div>
                @empty
                <p style="text-align: center; color: var(--color-gray-400); padding: 2rem;">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>

        <!-- System Info -->
        <div class="content-card">
            <h2 class="card-title">⚙️ Status Lingkungan</h2>
            <div style="font-size: 0.9rem; line-height: 2;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                    <span style="color: var(--color-gray-500);">Versi PHP:</span>
                    <span style="font-weight: 600;">{{ PHP_VERSION }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                    <span style="color: var(--color-gray-500);">Framework:</span>
                    <span style="font-weight: 600; color: var(--color-accent);">Laravel {{ app()->version() }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-gray-50);">
                    <span style="color: var(--color-gray-500);">Mode Penyimpanan:</span>
                    <span style="font-weight: 600; color: #10B981;">MySQL (Eloquent)</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--color-gray-500);">Zonawaktu:</span>
                    <span style="font-weight: 600;">{{ config('app.timezone') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
