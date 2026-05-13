@extends('layouts.app')

@section('title', 'Hasil Analisis – BurnoutXpert')

@section('content')
<div class="container-result" x-data="{ showTracing: false }">
    <div class="result-header-modern">
        <div class="header-content">
            <h1 class="page-title">Analisis Kesehatan Mental Selesai</h1>
            <p>Laporan ini disusun secara otomatis oleh sistem pakar berdasarkan standar MBI.</p>
        </div>
        <div class="header-actions">
            <button @click="window.print()" class="btn-nav">🖨️ Cetak</button>
            <a href="{{ route('karyawan.dashboard') }}" class="btn-cta">Dashboard</a>
        </div>
    </div>

    <div class="result-grid">
        <!-- Main Result -->
        <div class="content-card main-card">
            <div class="diagnosa-summary">
                <div class="badge-level" style="background: {{ $konsultasi->diagnosa->bg_light }}; color: {{ $konsultasi->diagnosa->color }}">
                    {{ $konsultasi->diagnosa->tingkat }}
                </div>
                <h2 class="diagnosa-name">{{ $konsultasi->diagnosa->nama }}</h2>
                
                <div class="confidence-gauge">
                    <div class="gauge-bar">
                        <div class="gauge-fill" style="width: {{ $confidence }}%; background: {{ $konsultasi->diagnosa->color }}"></div>
                    </div>
                    <div class="gauge-text">
                        <span>Akurasi Diagnosis: <strong>{{ $confidence }}%</strong></span>
                    </div>
                </div>

                <div class="desc-box">
                    <h3>Tentang Kondisi Anda</h3>
                    <p>{{ $konsultasi->diagnosa->deskripsi }}</p>
                </div>
            </div>

            <div class="recommendations">
                <h3>🛡️ Rekomendasi Tindakan</h3>
                <div class="recommendation-text">
                    {{ $konsultasi->diagnosa->saran }}
                </div>
            </div>
        </div>

        <!-- Explanation Facility -->
        <div class="content-card side-card">
            <h3 class="card-title">🔍 Transparansi Sistem</h3>
            <p style="font-size: 0.85rem; color: var(--color-gray-500); margin-bottom: 1.5rem;">
                Bagaimana sistem kami mencapai kesimpulan ini?
            </p>

            <div class="tracing-list">
                <div class="trace-item">
                    <div class="trace-icon">🧬</div>
                    <div class="trace-info">
                        <strong>Metode Analisis</strong>
                        <span>Backward Chaining + Certainty Factor</span>
                    </div>
                </div>
                
                @if($tracing && isset($tracing['gejala_details']))
                <div class="trace-item">
                    <div class="trace-icon">📊</div>
                    <div class="trace-info">
                        <strong>Logika Keputusan</strong>
                        <span>Berdasarkan {{ count($tracing['gejala_details']) }} gejala kunci yang terkonfirmasi.</span>
                    </div>
                </div>
                @endif

                <button @click="showTracing = true" class="btn-nav" style="width: 100%; margin-top: 1rem;">
                    Lihat Detail Perhitungan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tracing -->
    <template x-if="showTracing">
        <div class="modal-overlay active" @click.self="showTracing = false">
            <div class="modal-content-lg fade-in">
                <div class="modal-header">
                    <h3>Detail Perhitungan (Explanation Facility)</h3>
                    <button @click="showTracing = false" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    @if($tracing && isset($tracing['gejala_details']))
                    <div class="alert-info-box">
                        Sistem menggunakan hipotesis <strong>{{ $konsultasi->diagnosa->kode }}</strong> dan membuktikannya melalui aturan <strong>{{ $tracing['rule_kode'] }}</strong>.
                    </div>

                    <table class="tracing-table">
                        <thead>
                            <tr>
                                <th>Gejala yang Ditanyakan</th>
                                <th>Jawaban Anda</th>
                                <th>Bobot</th>
                                <th>Hasil CF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tracing['gejala_details'] as $detail)
                            <tr>
                                <td>{{ $detail['gejala'] }}</td>
                                <td><span class="ans-badge">{{ $detail['user_ans'] }}</span></td>
                                <td>{{ $detail['bobot'] }}</td>
                                <td class="cf-val">+{{ number_format($detail['cf_sub'], 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="tracing-footer">
                        <div class="calc-row">
                            <span>Bobot Keyakinan Aturan (Pakar)</span>
                            <strong>{{ $tracing['cf_pakar_rule'] }}</strong>
                        </div>
                        <div class="calc-row total">
                            <span>Final Certainty Factor</span>
                            <strong>{{ number_format($konsultasi->cf_final, 4) }}</strong>
                        </div>
                    </div>
                    @else
                    <p>Detail perhitungan tidak tersedia untuk rekaman historis ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('styles')
<style>
    .container-result { max-width: 1100px; margin: 0 auto; }
    .result-header-modern { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
    .result-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; }
    
    .main-card { padding: 2.5rem; }
    .side-card { padding: 2rem; background: #f8fafc; }

    .badge-level { display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; font-weight: 800; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 1rem; }
    .diagnosa-name { font-size: 2.2rem; color: var(--color-gray-800); margin-bottom: 1.5rem; line-height: 1.2; }
    
    .gauge-bar { height: 12px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 0.5rem; }
    .gauge-fill { height: 100%; transition: width 1s ease-out; }
    .gauge-text { font-size: 0.9rem; color: var(--color-gray-600); margin-bottom: 2rem; }

    .desc-box { background: #f1f5f9; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem; }
    .desc-box h3 { font-size: 1rem; margin-bottom: 0.75rem; color: var(--color-primary); }
    .desc-box p { font-size: 1rem; line-height: 1.6; color: var(--color-gray-700); }

    .recommendations h3 { font-size: 1.1rem; margin-bottom: 1rem; }
    .recommendation-text { line-height: 1.7; color: var(--color-gray-600); }

    .tracing-list { display: flex; flex-direction: column; gap: 1.25rem; }
    .trace-item { display: flex; gap: 1rem; align-items: center; }
    .trace-icon { width: 45px; height: 45px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: var(--shadow-sm); }
    .trace-info strong { display: block; font-size: 0.9rem; }
    .trace-info span { font-size: 0.8rem; color: var(--color-gray-500); }

    /* Tracing Modal Styles */
    .modal-content-lg { max-width: 800px; width: 90%; background: white; border-radius: 24px; padding: 2.5rem; box-shadow: var(--shadow-xl); position: relative; }
    .alert-info-box { background: #eff6ff; color: #1e40af; padding: 1rem; border-radius: 12px; font-size: 0.9rem; margin-bottom: 1.5rem; }
    
    .tracing-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
    .tracing-table th { text-align: left; padding: 1rem; font-size: 0.8rem; color: var(--color-gray-400); text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
    .tracing-table td { padding: 1rem; border-bottom: 1px solid #f8fafc; font-size: 0.9rem; }
    
    .ans-badge { padding: 0.2rem 0.6rem; background: #f1f5f9; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
    .cf-val { font-weight: 800; color: #10b981; }

    .tracing-footer { background: #f8fafc; padding: 1.5rem; border-radius: 16px; }
    .calc-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; }
    .calc-row.total { margin-top: 1rem; padding-top: 1rem; border-top: 2px dashed #e2e8f0; font-size: 1.1rem; color: var(--color-primary); }

    @media (max-width: 900px) {
        .result-grid { grid-template-columns: 1fr; }
        .diagnosa-name { font-size: 1.8rem; }
    }

    @media print {
        .header-actions, .side-card, .btn-nav { display: none !important; }
        .main-card { box-shadow: none !important; border: none !important; }
    }
</style>
@endpush
