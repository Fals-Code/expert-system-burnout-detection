@extends('layouts.app')

@section('title', 'Log Aktivitas - SanctuaryHub')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0;">Audit Log Sistem</h1>
        <div style="font-size: 0.9rem; color: var(--color-gray-500); font-weight: 600;">Total: {{ $logs->count() }} Aksi</div>
    </div>

    <div class="content-card" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
        <div class="table-container">
            <table class="data-table" id="logsTable">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Entitas</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $l)
                    <tr>
                        <td style="font-size: 0.8rem; white-space: nowrap;">{{ $l->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <div style="font-weight: 700; color: var(--color-primary);">{{ $l->user->nama ?? 'System' }}</div>
                            <div style="font-size: 0.7rem; color: var(--color-gray-400);">{{ $l->user->role ?? '-' }}</div>
                        </td>
                        <td>
                            @php
                                $actionColor = match(true) {
                                    str_contains($l->action, 'CREATE') => ['#dcfce7', '#166534'],
                                    str_contains($l->action, 'UPDATE') => ['#fef3c7', '#92400e'],
                                    str_contains($l->action, 'DELETE') => ['#fee2e2', '#991b1b'],
                                    default => ['#f1f5f9', '#475569']
                                };
                            @endphp
                            <span class="badge" style="background: {{ $actionColor[0] }}; color: {{ $actionColor[1] }}; font-size: 0.7rem;">
                                {{ $l->action }}
                            </span>
                        </td>
                        <td><span style="font-weight: 600; color: var(--color-gray-600);">{{ $l->entity }}</span></td>
                        <td style="font-size: 0.85rem; color: var(--color-gray-600);">{{ $l->desc }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {
            placeholder: "Cari log...",
            perPage: "log per halaman",
            noRows: "Log tidak ditemukan",
            info: "Menampilkan {start} sampai {end} dari {rows} log",
        };
        new simpleDatatables.DataTable("#logsTable", { searchable: true, labels });
    });
</script>
@endpush
