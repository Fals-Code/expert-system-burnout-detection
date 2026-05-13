@extends('layouts.app')

@section('title', 'Laporan Burnout – BurnoutXpert')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0;">Laporan Analisis Divisi</h1>
        <div style="display: flex; gap: 0.75rem;">
            <button class="btn-cta" style="background: #10b981; border: none;" onclick="window.print()">Cetak PDF</button>
        </div>
    </div>

    <div class="content-card">
        <h2 class="card-title">Rekapitulasi Deteksi - {{ now()->translatedFormat('F Y') }}</h2>
        <p style="color: var(--color-gray-500); font-size: 0.9rem; margin-bottom: 2rem;">Laporan ini merangkum tingkat burnout karyawan di setiap divisi untuk periode berjalan.</p>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Divisi</th>
                        <th>Total Asesmen</th>
                        <th>Burnout Tinggi</th>
                        <th>Burnout Sedang</th>
                        <th>Burnout Rendah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporan_divisi as $l)
                    <tr>
                        <td style="font-weight: 700; color: var(--color-primary);">{{ $l['divisi'] }}</td>
                        <td>{{ $l['total'] }} orang</td>
                        <td><span class="badge" style="background: #fee2e2; color: #991b1b;">{{ $l['tinggi'] }}</span></td>
                        <td><span class="badge" style="background: #fef3c7; color: #92400e;">{{ $l['sedang'] }}</span></td>
                        <td><span class="badge" style="background: #dcfce7; color: #166534;">{{ $l['rendah'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card" style="margin-top: 1.5rem;">
        <h2 class="card-title">💡 Analisis Singkat HRD</h2>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; font-size: 0.95rem; color: #334155; line-height: 1.6;">
            <p>Berdasarkan data di atas, identifikasi divisi dengan angka <strong>Burnout Tinggi</strong> yang signifikan. Disarankan untuk menjadwalkan sesi konseling atau *team building* bagi divisi yang terdampak untuk mencegah penurunan produktivitas lebih lanjut.</p>
        </div>
    </div>
@endsection
