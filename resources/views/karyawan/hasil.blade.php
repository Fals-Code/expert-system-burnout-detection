@extends('layouts.app')

@section('title', 'Hasil Diagnosis – BurnoutXpert')

@section('content')
    <main class="result-container" id="report-content">
        @if(isset($no_burnout))
            <div class="result-header" style="text-align: center; margin-bottom: 3rem;">
                <div class="header-icon">✅</div>
                <div class="header-text">
                    <h1>Kondisi Anda Normal</h1>
                    <p>Sistem tidak mendeteksi gejala burnout yang signifikan pada saat ini. Tetap jaga keseimbangan kerja dan istirahat Anda!</p>
                </div>
            </div>
            <div style="text-align: center;">
                <a href="{{ route('karyawan.dashboard') }}" class="btn-cta">Kembali ke Dashboard</a>
            </div>
        @else
            <div class="result-header">
                <div class="header-icon">🔥</div>
                <div class="header-text">
                    <h1>Hasil Deteksi Burnout Anda</h1>
                    <p>Berdasarkan analisis sistem pakar terhadap gejala yang Anda laporkan.</p>
                </div>
            </div>

            <div class="main-result-card">
                <div class="result-info">
                    <h2>Tingkat Burnout</h2>
                    <div class="level-label" style="color: {{ $color }};">{{ $label }}</div>
                    <p class="condition-desc">{{ $desc }}</p>
                </div>
                <div class="circular-progress">
                    <svg viewBox="0 0 180 180">
                        <circle class="bg" cx="90" cy="90" r="80"></circle>
                        <circle class="fg" id="progressCircle" cx="90" cy="90" r="80" style="stroke: {{ $color }};"></circle>
                    </svg>
                    <div class="progress-val">
                        <span class="percent" id="confidenceCounter">0%</span>
                        <span class="txt">Akurasi Analisis</span>
                    </div>
                </div>
            </div>

            <!-- Gejala yang Teridentifikasi -->
            <div class="symptoms-section">
                <h2 class="section-title">🔍 Gejala yang Teridentifikasi</h2>
                <div class="pill-group">
                    @forelse ($gejala_terdeteksi as $g)
                        <div class="pill" style="background: {{ $bg_light }}; color: {{ $color }};">
                            <span class="pill-dot"></span> {{ $g }}
                        </div>
                    @empty
                        <p style="color: var(--color-gray-400); font-size: 0.9rem; font-style: italic;">Tidak ada gejala spesifik yang terdeteksi.</p>
                    @endforelse
                </div>
            </div>

            <h2 class="section-title">✨ Rekomendasi Penanganan</h2>
            <div class="recommendation-list">
                @foreach ($rekomendasi as $index => $rec)
                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <div class="accordion-left">
                            <span class="priority-badge">Prioritas {{ $index + 1 }}</span>
                            <div class="rec-icon">{{ $rec['icon'] }}</div>
                            <h3>{{ $rec['judul'] }}</h3>
                        </div>
                        <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            {{ $rec['isi'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="action-group">
                <button type="button" id="pdfBtn" onclick="generatePDF()" class="btn-action" style="background:#10b981; color:white; border:none; cursor:pointer; padding:0.8rem 1.5rem; border-radius:50px; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Unduh Laporan PDF
                </button>
                <button type="button" onclick="openTracingModal()" class="btn-action" style="background:var(--color-primary); color:white; border:none; cursor:pointer; padding:0.8rem 1.5rem; border-radius:50px; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Detail Kalkulasi
                </button>
                <a href="{{ route('karyawan.dashboard') }}" class="btn-action btn-back">
                    Dashboard
                </a>
            </div>
        @endif
    </main>

    <!-- Modal Tracing -->
    @if(!isset($no_burnout))
    <div class="modal-overlay" id="modalTracing">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Transparansi Perhitungan Pakar</h3>
                <button type="button" onclick="closeTracingModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                <div style="background:#f8fafc; padding:1.25rem; border-radius:12px; margin-bottom:1rem;">
                    <h4 style="margin-top:0;">🔄 Alur Backward Chaining</h4>
                    @foreach($bc_trace as $trace)
                        <div style="margin-bottom:0.5rem; font-size:0.85rem;">
                            <strong>{{ $trace['goal'] }}:</strong> 
                            {{ $trace['confirmed'] ? '✅ Terkonfirmasi' : '❌ Ditolak' }} 
                            (CF: {{ number_format($trace['cf_final'], 4) }})
                        </div>
                    @endforeach
                </div>
                
                <div style="background:white; border:1px solid #e2e8f0; padding:1.25rem; border-radius:12px;">
                    <h4 style="margin-top:0;">📐 Detail Perhitungan</h4>
                    <p style="font-size:0.9rem;">Rule: <strong>{{ $tracing['rule_id'] }}</strong></p>
                    <ul style="font-size:0.85rem; color:#64748b; padding-left:1.2rem;">
                        @foreach($tracing['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                    <div style="border-top:1px dashed #e2e8f0; margin-top:1rem; pt:1rem; font-weight:700;">
                        Final CF: {{ number_format($tracing['cf_final'], 4) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
    @if(!isset($no_burnout))
    function generatePDF() {
        const btn = document.getElementById('pdfBtn');
        btn.innerHTML = '⏳ Memproses...';
        btn.disabled = true;

        // Hide interactive elements before print
        document.querySelectorAll('.action-group, #modalTracing').forEach(el => el.style.display = 'none');

        const element = document.getElementById('report-content');
        const opt = {
            margin: 0.5,
            filename: 'Laporan_BurnoutXpert_{{ now()->format("Ymd") }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            document.querySelectorAll('.action-group, #modalTracing').forEach(el => el.style.display = '');
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Unduh Laporan PDF';
            btn.disabled = false;
        });
    }
    @endif
    function openTracingModal() {
        document.getElementById('modalTracing').classList.add('active');
    }

    function closeTracingModal() {
        document.getElementById('modalTracing').classList.remove('active');
    }

    function toggleAccordion(header) {
        const item = header.parentElement;
        item.classList.toggle('active');
    }

    @if(!isset($no_burnout))
    function animateProgress() {
        const target = {{ $confidence }};
        const circle = document.getElementById('progressCircle');
        const counter = document.getElementById('confidenceCounter');
        const duration = 1500;
        const circumference = 502;

        let startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const currentVal = Math.floor(progress * target);
            
            counter.innerText = currentVal + '%';
            circle.style.strokeDashoffset = circumference * (1 - (progress * target / 100));
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        }

        circle.style.strokeDasharray = circumference;
        window.requestAnimationFrame(step);
    }
    window.addEventListener('DOMContentLoaded', animateProgress);
    @endif
</script>
@endpush
