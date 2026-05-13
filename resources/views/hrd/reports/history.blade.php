@extends('layouts.app')

@section('title', 'Riwayat Karyawan – BurnoutXpert')

@section('content')
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
        <a href="{{ route('hrd.employees') }}" class="btn-nav" style="padding: 0.5rem; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </a>
        <h1 class="page-title" style="margin: 0;">Riwayat Deteksi: {{ $user->nama }}</h1>
    </div>

    <div class="content-card" style="margin-bottom: 2rem; background: var(--color-primary); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.85rem; opacity: 0.8;">Unit / Divisi</div>
                <div style="font-size: 1.25rem; font-weight: 700;">{{ $user->divisi->nama ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size: 0.85rem; opacity: 0.8;">Total Deteksi</div>
                <div style="font-size: 1.25rem; font-weight: 700; text-align: right;">{{ $user->konsultasi->count() }} Kali</div>
            </div>
        </div>
    </div>

    @if($user->konsultasi->isEmpty())
        <div class="content-card" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h3 style="color: var(--color-gray-700);">Belum Ada Data</h3>
            <p style="color: var(--color-gray-500);">Karyawan ini belum pernah melakukan deteksi burnout.</p>
        </div>
    @else
        <div class="timeline">
            @foreach($user->konsultasi->sortByDesc('created_at') as $h)
                <div class="content-card" style="margin-bottom: 1.5rem; border-left: 5px solid {{ $h->diagnosa->color }};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-gray-500); font-weight: 700; margin-bottom: 0.5rem;">
                                📅 {{ $h->created_at->translatedFormat('d F Y, H:i') }}
                            </div>
                            <h2 style="margin: 0; color: {{ $h->diagnosa->color }};">{{ $h->diagnosa->nama }}</h2>
                            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--color-gray-700);">
                                Tingkat Keyakinan: <strong>{{ number_format($h->cf_final * 100, 1) }}%</strong>
                            </div>
                        </div>
                        <div class="badge" style="background: {{ $h->diagnosa->bg_light }}; color: {{ $h->diagnosa->color }}; font-weight: 800;">
                            {{ $h->diagnosa->tingkat }}
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem;">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-gray-600); text-transform: uppercase; margin-bottom: 0.5rem;">Gejala Terdeteksi:</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($h->gejala as $g)
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem;">{{ $g->nama }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
