@extends('layouts.app')

@section('title', 'Monitoring Karyawan – BurnoutXpert')

@section('content')
    <h1 class="page-title">Monitoring Kesehatan Karyawan</h1>

    <div class="content-card">
        <div class="card-header" style="margin-bottom: 1.5rem;">
            <h2 class="card-title">Daftar Status Karyawan</h2>
        </div>
        
        <div class="table-container overflow-x-auto">
            <table class="data-table" id="employeesTable">
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
                    @forelse($employees as $e)
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
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem;">
                            <div style="font-size: 3rem; margin-bottom: 0.75rem;">👥</div>
                            <div style="font-weight: 700; color: #64748b;">Belum ada karyawan terdaftar</div>
                            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.25rem;">Tambahkan karyawan melalui modul Admin terlebih dahulu.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new simpleDatatables.DataTable("#employeesTable", {
            searchable: true,
            labels: {
                placeholder: "Cari karyawan...",
                perPage: "{select} data per halaman",
                noRows: "Data tidak ditemukan",
                info: "Menampilkan {start} sampai {end} dari {rows} data",
            }
        });
    });
</script>
@endpush
