@extends('layouts.app')

@section('title', 'Monitoring Karyawan – BurnoutXpert')

@section('content')
    <h1 class="page-title">Monitoring Kesehatan Karyawan</h1>

    <div class="content-card">
        <div class="card-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title" style="margin: 0;">Daftar Status Karyawan</h2>
            <button class="btn-cta" style="background: #10b981;" onclick="exportToExcel('employeesTable', 'Laporan-Karyawan.xlsx')">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Ekspor Excel
                </span>
            </button>
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
                            <a href="{{ route('hrd.employees.history', $e->id) }}" class="btn-icon" title="Lihat Detail Riwayat" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem;">
                            <div style="color: #cbd5e1; margin-bottom: 1rem;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <div style="font-weight: 700; color: #64748b;">Belum ada karyawan terdaftar</div>
                            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.25rem;">Tambahkan karyawan melalui modul Admin terlebih dahulu.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $employees->links() }}
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
                perPage: "data per halaman",
                noRows: "Data tidak ditemukan",
                info: "Menampilkan {start} sampai {end} dari {rows} data",
            }
        });
    });
</script>
@endpush
