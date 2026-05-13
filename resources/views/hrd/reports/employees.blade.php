@extends('layouts.app')

@section('title', 'Monitoring Karyawan – BurnoutXpert')

@section('content')
    <h1 class="page-title">Monitoring Kesehatan Karyawan</h1>

    <div class="content-card">
        <div class="card-header" style="margin-bottom: 1.5rem;">
            <h2 class="card-title">Daftar Status Karyawan</h2>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Divisi</th>
                        <th>Deteksi Terakhir</th>
                        <th>Hasil Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $e)
                    @php $latest = $e->konsultasi->first(); @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 700;">{{ $e->nama }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $e->email }}</div>
                        </td>
                        <td>{{ $e->divisi->nama ?? '-' }}</td>
                        <td>{{ $latest ? $latest->created_at->translatedFormat('d M Y') : 'Belum pernah' }}</td>
                        <td>
                            @if($latest)
                                <span class="badge" style="background: {{ $latest->diagnosa->bg_light }}; color: {{ $latest->diagnosa->color }};">
                                    {{ $latest->diagnosa->nama }}
                                </span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b;">N/A</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('hrd.employees.history', $e->id) }}" class="btn-icon" title="Lihat Detail Riwayat">👁️</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
