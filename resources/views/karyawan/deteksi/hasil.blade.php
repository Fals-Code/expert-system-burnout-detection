@extends('layouts.app')

@section('title', 'Hasil Analisis Kesehatan Mental – BurnoutXpert')

@section('content')
<div class="container-result" x-data="{ showTracing: false }">
    <!-- Header Section -->
    <div class="result-header-premium">
        <div class="header-main">
            <h1 class="page-title">Laporan Analisis Selesai</h1>
            <p>Berdasarkan evaluasi sistem pakar terhadap gejala yang Anda laporkan.</p>
        </div>
        <div class="header-actions">
            <button @click="window.print()" class="btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Cetak Laporan
            </button>
            <a href="{{ route('karyawan.dashboard') }}" class="btn-cta">Ke Dashboard</a>
        </div>
    </div>

    <div class="result-layout">
        <!-- Main Analysis Card -->
        <div class="analysis-card content-card">
            <div class="status-badge" style="background: {{ $konsultasi->diagnosa->bg_light }}; color: {{ $konsultasi->diagnosa->color }}">
                Tingkat: {{ $konsultasi->diagnosa->tingkat }}
            </div>
            
            <div class="diagnosis-info">
                <h2 class="diagnosis-name">{{ $konsultasi->diagnosa->nama }}</h2>
                <div class="meter-wrapper">
                    <div class="meter-gauge">
                        <div class="meter-fill" style="width: {{ $confidence }}%; background: {{ $konsultasi->diagnosa->color }}"></div>
                    </div>
                    <div class="meter-labels">
                        <span>Akurasi Analisis</span>
                        <span class="confidence-val">{{ $confidence }}%</span>
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="description-section">
                <h3>Apa Artinya Ini?</h3>
                <p>{{ $konsultasi->diagnosa->deskripsi }}</p>
            </div>

            <div class="recommendations-section">
                <h3>💡 Rekomendasi Langkah Selanjutnya</h3>
                <div class="advice-card">
                    {{ $konsultasi->diagnosa->saran }}
                </div>
            </div>

            <div class="action-footer">
                <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-text">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    Diagnosis Ulang
                </a>
                <button @click="showTracing = true" class="btn-text">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Detail Logika Pakar
                </button>
            </div>
        </div>

        <!-- Sidebar / Summary Info -->
        <div class="sidebar-info">
            <div class="summary-card content-card">
                <h3>📊 Ringkasan Sesi</h3>
                <ul class="summary-list">
                    <li>
                        <span>Waktu Selesai</span>
                        <strong>{{ $konsultasi->created_at->format('H:i, d M Y') }}</strong>
                    </li>
                    <li>
                        <span>Gejala Terdeteksi</span>
                        <strong>{{ count($konsultasi->gejala) }} Gejala</strong>
                    </li>
                    <li>
                        <span>Metode Analisis</span>
                        <strong>Backward Chaining</strong>
                    </li>
                </ul>
            </div>

            <div class="help-card content-card">
                <div class="help-icon">🆘</div>
                <h4>Butuh Bantuan Lebih?</h4>
                <p>Jika Anda merasa membutuhkan bantuan segera, jangan ragu untuk menghubungi departemen HRD atau konselor profesional.</p>
            </div>
        </div>
    </div>

    <!-- Tracing Modal (Explanation Facility) -->
    <template x-if="showTracing">
        <div class="modal-overlay active" @click.self="showTracing = false">
            <div class="modal-container-lg slide-up">
                <div class="modal-header">
                    <h3>Explanation Facility (Transparansi Logika)</h3>
                    <button @click="showTracing = false" class="btn-close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="logic-intro">
                        Sistem menggunakan data Anda untuk membuktikan hipotesis <strong>{{ $konsultasi->diagnosa->nama }}</strong> melalui aturan pakar.
                    </div>

                    @if($tracing && isset($tracing['gejala_details']))
                    <div class="table-responsive">
                        <table class="tracing-table">
                            <thead>
                                <tr>
                                    <th>Gejala</th>
                                    <th>Jawaban</th>
                                    <th>CF Pakar</th>
                                    <th>Hasil Sub-CF</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tracing['gejala_details'] as $detail)
                                <tr>
                                    <td>{{ $detail['gejala'] }}</td>
                                    <td><span class="ans-pill">{{ $detail['user_ans'] }}</span></td>
                                    <td>{{ $detail['bobot'] }}</td>
                                    <td class="text-success">+{{ number_format($detail['cf_sub'], 3) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="logic-footer">
                        <div class="logic-row">
                            <span>Total Certainty Factor (Combined)</span>
                            <span class="logic-val">{{ number_format($konsultasi->cf_final, 4) }}</span>
                        </div>
                        <div class="logic-row highlight">
                            <span>Akurasi Final</span>
                            <span class="logic-val">{{ $confidence }}%</span>
                        </div>
                    </div>
                    @else
                    <p class="text-center p-4 text-muted">Detail kalkulasi tidak tersedia untuk sesi ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('styles')
<style>
    .container-result { max-width: 1100px; margin: 2rem auto; }
    .result-header-premium { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
    .result-layout { display: grid; grid-template-columns: 1.6fr 1fr; gap: 2rem; }

    .analysis-card { padding: 3.5rem; position: relative; }
    .status-badge { display: inline-block; padding: 0.5rem 1.2rem; border-radius: 50px; font-weight: 800; font-size: 0.75rem; letter-spacing: 1px; margin-bottom: 1.5rem; }
    .diagnosis-name { font-size: 2.5rem; font-weight: 800; color: var(--color-gray-800); margin-bottom: 2rem; line-height: 1.1; }

    .meter-gauge { height: 14px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 0.75rem; }
    .meter-fill { height: 100%; transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .meter-labels { display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--color-gray-500); }
    .confidence-val { font-weight: 800; color: var(--color-gray-800); }

    .divider { margin: 2.5rem 0; border: none; border-top: 1px solid #f1f5f9; }

    .description-section h3, .recommendations-section h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 1.2rem; color: var(--color-primary); }
    .description-section p { font-size: 1.1rem; line-height: 1.7; color: var(--color-gray-700); margin-bottom: 2.5rem; }

    .advice-card { background: #f0fdf4; border-left: 5px solid #10b981; padding: 1.5rem; border-radius: 12px; color: #065f46; line-height: 1.6; font-size: 1.05rem; }

    .action-footer { margin-top: 3.5rem; display: flex; gap: 2rem; border-top: 1px solid #f1f5f9; pt: 2rem; }
    .btn-text { background: none; border: none; padding: 0; color: var(--color-gray-500); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; transition: color 0.2s; text-decoration: none; }
    .btn-text:hover { color: var(--color-primary); }

    .sidebar-info { display: flex; flex-direction: column; gap: 2rem; }
    .summary-list { list-style: none; padding: 0; margin-top: 1.5rem; }
    .summary-list li { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #f8fafc; font-size: 0.9rem; }
    .summary-list li span { color: var(--color-gray-500); }
    .summary-list li strong { color: var(--color-gray-800); }

    .help-card { background: #fffbeb; border: 1px solid #fef3c7; }
    .help-icon { font-size: 2rem; margin-bottom: 1rem; }
    .help-card h4 { font-weight: 700; margin-bottom: 0.8rem; color: #92400e; }
    .help-card p { font-size: 0.85rem; color: #b45309; line-height: 1.5; }

    /* Modal Styles */
    .modal-container-lg { max-width: 850px; width: 95%; background: white; border-radius: 28px; padding: 3rem; position: relative; max-height: 90vh; overflow-y: auto; }
    .logic-intro { background: #eff6ff; padding: 1.25rem; border-radius: 16px; margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5; color: #1e40af; border-left: 5px solid #3b82f6; }
    .tracing-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
    .tracing-table th { text-align: left; padding: 1rem; font-size: 0.8rem; color: var(--color-gray-400); text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
    .tracing-table td { padding: 1rem; border-bottom: 1px solid #f8fafc; font-size: 0.9rem; }
    .ans-pill { background: #f1f5f9; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
    .logic-footer { background: #f8fafc; padding: 1.5rem; border-radius: 16px; }
    .logic-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; }
    .logic-row.highlight { margin-top: 1rem; padding-top: 1rem; border-top: 2px dashed #e2e8f0; font-size: 1.2rem; font-weight: 800; color: var(--color-primary); }

    @media (max-width: 900px) {
        .result-layout { grid-template-columns: 1fr; }
        .analysis-card { padding: 2rem; }
        .diagnosis-name { font-size: 1.8rem; }
        .result-header-premium { flex-direction: column; align-items: flex-start; gap: 1.5rem; }
        .header-actions { width: 100%; display: flex; gap: 1rem; }
        .header-actions button, .header-actions a { flex: 1; justify-content: center; }
    }

    @media print {
        .header-actions, .action-footer, .sidebar-info, .btn-close-modal { display: none !important; }
        .analysis-card { box-shadow: none !important; border: none !important; padding: 0 !important; }
        .container-result { margin: 0 !important; max-width: 100% !important; }
        .result-layout { display: block !important; }
    }
</style>
@endpush
