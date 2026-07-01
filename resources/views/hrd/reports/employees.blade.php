@extends('layouts.app')

@section('title', 'Monitoring Agregat - SanctuaryHub')

@section('content')
    <h1 class="page-title">Monitoring Agregat Karyawan</h1>

    <div class="content-card">
        <div class="card-header" style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
            <div>
                <h2 class="card-title" style="margin:0;">Status Terakhir per Unit</h2>
                <p style="margin:0.35rem 0 0; color:var(--color-gray-500); font-size:0.88rem;">Identitas dan jawaban mentah tidak ditampilkan di area HRD.</p>
            </div>
            <button class="btn-cta" style="background:#10b981;" onclick="exportToExcel('employeesTable', 'Monitoring-Agregat-SanctuaryHub.xlsx')">Ekspor Excel</button>
        </div>

        <div class="table-container overflow-x-auto">
            <table class="data-table" id="employeesTable">
                <thead>
                    <tr>
                        <th>Referensi</th>
                        <th>Divisi</th>
                        <th>Deteksi Terakhir</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        @php $latest = $employee->konsultasi->first(); @endphp
                        <tr>
                            <td>Karyawan #{{ $employee->id }}</td>
                            <td>{{ $employee->divisi->nama ?? '-' }}</td>
                            <td>{{ $latest ? $latest->created_at->translatedFormat('d M Y') : 'Belum pernah' }}</td>
                            <td>
                                @if ($latest)
                                    <span class="badge" style="background:{{ $latest->diagnosa->bg_light }}; color:{{ $latest->diagnosa->color }};">
                                        {{ $latest->diagnosa->nama }}
                                    </span>
                                @else
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">Belum ada data</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:3rem; color:#94a3b8;">Belum ada karyawan terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1.5rem; display:flex; justify-content:center;">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
