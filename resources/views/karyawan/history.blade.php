@extends('layouts.app')

@section('title', 'Riwayat Deteksi – BurnoutXpert')

@section('content')
    <h1 class="page-title">Riwayat Deteksi Anda</h1>

    @if(count($history) === 0)
        <div class="content-card" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h3 style="color: var(--color-gray-700);">Belum Ada Riwayat</h3>
            <p style="color: var(--color-gray-500); margin-bottom: 1.5rem;">Anda belum pernah melakukan deteksi burnout sebelumnya.</p>
            <a href="{{ route('karyawan.deteksi') }}" class="btn-cta">Mulai Deteksi Sekarang</a>
        </div>
    @else
        <div class="timeline">
            @foreach($history as $h)
                <div class="content-card" style="margin-bottom: 1.5rem; position: relative; border-left: 5px solid {{ $h->diagnosa->color }};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-gray-500); font-weight: 700; margin-bottom: 0.5rem;">
                                📅 {{ $h->created_at->translatedFormat('d F Y, H:i') }}
                            </div>
                            <h2 style="margin: 0; color: {{ $h->diagnosa->color }};">{{ $h->diagnosa->nama }}</h2>
                            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--color-gray-700);">
                                Akurasi Analisis: <strong>{{ number_format($h->cf_final * 100, 1) }}%</strong>
                            </div>
                        </div>
                        <div class="badge" style="background: {{ $h->diagnosa->bg_light }}; color: {{ $h->diagnosa->color }}; font-weight: 800;">
                            {{ $h->diagnosa->tingkat }}
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem;">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-gray-600); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Gejala yang Dilaporkan:</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($h->gejala as $g)
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem;">{{ $g->nama }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <a href="{{ route('karyawan.hasil') }}?id={{ $h->id }}" class="btn-nav" style="font-size: 0.85rem; color: var(--color-primary); font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                            Lihat Detail Rekomendasi
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
