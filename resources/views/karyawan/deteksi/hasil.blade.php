@extends('layouts.app')

@section('title', 'Hasil Diagnosis – BurnoutXpert')

@section('content')
<div class="main-wrapper" style="margin-left: 0; padding: 0;">
    <main class="result-container">
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
                <div class="level-label" style="color: {{ $konsultasi->diagnosa->color }};">{{ $konsultasi->diagnosa->nama }}</div>
                <p class="condition-desc">{{ $konsultasi->diagnosa->deskripsi }}</p>
            </div>
            <div class="circular-progress">
                <svg viewBox="0 0 180 180">
                    <circle class="bg" cx="90" cy="90" r="80"></circle>
                    <circle class="fg" id="progressCircle" cx="90" cy="90" r="80" style="stroke: {{ $konsultasi->diagnosa->color }};"></circle>
                </svg>
                <div class="progress-val">
                    <span class="percent" id="confidenceCounter">0%</span>
                    <span class="txt tooltip-trigger">
                        Akurasi Analisis
                        <span class="tooltip-box">Persentase ini menunjukkan seberapa kuat sistem mengidentifikasi pola burnout dari jawaban Anda.</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Gejala yang Teridentifikasi -->
        <div class="symptoms-section">
            <h2 class="section-title">🔍 Gejala yang Teridentifikasi</h2>
            <div class="pill-group">
                @if ($konsultasi->gejala->isEmpty())
                    <p style="color: var(--color-gray-400); font-size: 0.9rem; font-style: italic;">Tidak ada gejala spesifik yang terdeteksi.</p>
                @else
                    @foreach ($konsultasi->gejala as $g)
                        <div class="pill" style="background: {{ $konsultasi->diagnosa->bg_light }}; color: {{ $konsultasi->diagnosa->color }};">
                            <span class="pill-dot"></span> {{ $g->nama }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <h2 class="section-title">✨ Rekomendasi Penanganan</h2>
        <div class="recommendation-list">
            @php
                $saran = explode("\n", $konsultasi->diagnosa->saran);
                $icons = ['🧘', '✈️', '⚖️', '😴', '🍎'];
            @endphp
            @foreach ($saran as $index => $rec)
                @if (trim($rec))
                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <div class="accordion-left">
                            <span class="priority-badge">Prioritas {{ $index + 1 }}</span>
                            <div class="rec-icon" style="margin-bottom:0;">{{ $icons[$index % count($icons)] }}</div>
                            <h3>{{ Str::before($rec, ':') }}</h3>
                        </div>
                        <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            {{ Str::after($rec, ':') }}
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <div class="action-group">
            <button type="button" onclick="openTracingModal()" class="btn-action" style="background:var(--color-primary); color:white; border:none; cursor:pointer; padding:0.8rem 1.5rem; border-radius:50px; font-weight:700; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Detail Kalkulasi
            </button>
            <a href="{{ route('karyawan.laporan.download', ['id' => $konsultasi->id]) }}" class="btn-action btn-download" target="_blank">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Unduh PDF
            </a>
            <a href="{{ route('karyawan.dashboard') }}" class="btn-action btn-back">
                Dashboard
            </a>
        </div>

        <!-- Langkah Selanjutnya Timeline -->
        <div class="next-steps-timeline">
            <div class="timeline-header">
                <h2>Langkah Selanjutnya</h2>
                <p>Ikuti panduan ini untuk memulai proses pemulihan Anda</p>
            </div>
            <div class="timeline-grid">
                <div class="timeline-item-wrap">
                    <div class="timeline-step">1</div>
                    <h4>Simpan Laporan</h4>
                    <p>Unduh hasil deteksi ini untuk referensi pribadi atau diskusi medis.</p>
                    <a href="{{ route('karyawan.laporan.download', ['id' => $konsultasi->id]) }}" class="timeline-action-btn" target="_blank">Download Laporan</a>
                </div>
                <div class="timeline-item-wrap">
                    <div class="timeline-step">2</div>
                    <h4>Konseling</h4>
                    <p>Jadwalkan sesi pertama dengan psikolog untuk evaluasi lebih mendalam.</p>
                    @if ($konsultasi->diagnosa->tingkat === 'TINGGI' || $konsultasi->diagnosa->tingkat === 'SANGAT TINGGI')
                        <a href="mailto:hrd@burnoutxpert.com?subject=Permintaan Jadwal Konseling - {{ urlencode(auth()->user()->name) }}&body=Halo Tim HRD,%0A%0ASaya {{ urlencode(auth()->user()->name) }} ingin mengajukan jadwal konseling terkait hasil deteksi kesehatan mental saya.%0A%0ATerima kasih." class="timeline-action-btn" style="text-decoration:none; display:inline-block; text-align:center; background:var(--color-error); color:white; border:none;">Ajukan Konseling ke HRD</a>
                    @else
                        <button class="timeline-action-btn" onclick="alert('Fitur pencarian psikolog akan segera hadir!')">Cari Psikolog</button>
                    @endif
                </div>
                <div class="timeline-item-wrap">
                    <div class="timeline-step">3</div>
                    <h4>Follow-up</h4>
                    <p>Lakukan pemeriksaan rutin setiap 30 hari untuk memantau progres Anda.</p>
                    <button class="timeline-action-btn" onclick="alert('Pengingat telah diset untuk 30 hari ke depan.')">Set Pengingat</button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tracing -->
<div class="modal-overlay" id="modalTracing">
    <div class="modal-content" style="background:white; border-radius:16px; width:90%; max-width:600px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="modal-header" style="padding:1.5rem; border-bottom:1px solid var(--color-gray-200); display:flex; justify-content:space-between; align-items:center; background:var(--color-primary); color:white;">
            <h3 style="margin:0; font-size:1.2rem; font-weight:700;">Transparansi Perhitungan Pakar</h3>
            <button type="button" onclick="closeTracingModal()" style="background:transparent; border:none; color:white; font-size:1.5rem; cursor:pointer; padding:0; line-height:1;">&times;</button>
        </div>
        <div class="modal-body" style="padding:1.5rem; overflow-y:auto; background:#f8fafc;">
            @if ($tracing)
                @if (isset($tracing['rule_kode']))
                <div style="background:white; padding:1.25rem; border-radius:12px; border:1px solid var(--color-gray-200); margin-bottom:1rem;">
                    <h4 style="margin:0 0 0.75rem 0; color:var(--color-primary); font-size:1rem;">📐 Rule Dominan yang Terkonfirmasi</h4>
                    <p style="margin:0; font-size:0.95rem;">Kode Rule: <strong style="color:var(--color-accent);">{{ $tracing['rule_kode'] ?? '-' }}</strong></p>
                    <p style="margin:0.25rem 0 0 0; font-size:0.95rem;">Bobot Kepastian Pakar (CF Pakar): <strong>{{ number_format($tracing['cf_pakar_rule'] ?? 0, 2) }}</strong></p>
                </div>
                @endif

                @if (isset($tracing['gejala_details']))
                <div style="background:white; padding:1.25rem; border-radius:12px; border:1px solid var(--color-gray-200); margin-bottom:1rem;">
                    <h4 style="margin:0 0 0.75rem 0; color:var(--color-primary); font-size:1rem;">2. Rincian Gejala & Bobot Jawaban (CF User)</h4>
                    <ul style="margin:0; padding-left:1.2rem; font-size:0.9rem; color:var(--color-gray-600); line-height:1.6;">
                        @foreach ($tracing['gejala_details'] as $detail)
                            <li>{{ $detail['gejala'] }} ({{ $detail['kode'] }}): CF_user={{ number_format($detail['cf_user'], 2) }} × bobot={{ number_format($detail['bobot'], 2) }} = {{ number_format($detail['cf_sub'], 4) }} [{{ $detail['user_ans'] }}]</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div style="background:var(--color-primary); color:white; padding:1.25rem; border-radius:12px;">
                    <h4 style="margin:0 0 0.5rem 0; font-size:1rem; color:rgba(255,255,255,0.9);">3. Hasil Akhir (Final CF)</h4>
                    <p style="margin:0; font-size:0.9rem; line-height:1.5;">Metode: {{ $tracing['method'] ?? 'Backward Chaining + Certainty Factor' }}</p>
                    
                    @if(isset($tracing['avg_gejala_cf']) && isset($tracing['cf_pakar_rule']))
                    <div style="background: rgba(255,255,255,0.1); padding: 0.75rem; border-radius: 8px; margin: 0.75rem 0; font-family: monospace; font-size: 0.85rem; line-height: 1.6;">
                        <div style="color: rgba(255,255,255,0.8);">Rumus: CF_final = avg(CF_user × bobot) × CF_pakar</div>
                        <div>CF_final = {{ number_format($tracing['avg_gejala_cf'], 4) }} × {{ number_format($tracing['cf_pakar_rule'], 2) }} = <strong>{{ number_format($konsultasi->cf_final, 4) }}</strong></div>
                    </div>
                    @else
                    <p style="margin:0.5rem 0 0 0; font-size:1.1rem; font-weight:700;">
                        CF Final: {{ number_format($konsultasi->cf_final, 4) }}
                    </p>
                    @endif
                    
                    <p style="margin:0.5rem 0 0 0; font-size:0.85rem; color:rgba(255,255,255,0.7);">*Nilai final ini kemudian dikonversi menjadi persentase ({{ $confidence }}%).</p>
                </div>
            @else
                <div style="text-align:center; padding:2rem; color:var(--color-gray-500);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem; opacity:0.5;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p style="margin:0;">Detail kalkulasi tidak tersedia untuk sesi ini.</p>
                </div>
            @endif
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem; border-top:1px solid var(--color-gray-200); background:white; display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeTracingModal()" style="background:var(--color-gray-100); color:var(--color-gray-600); border:none; padding:0.6rem 1.2rem; border-radius:8px; font-weight:600; cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openTracingModal() {
        const modal = document.getElementById('modalTracing');
        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeTracingModal() {
        const modal = document.getElementById('modalTracing');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Animation for circular progress
    function animateProgress() {
        const target = {{ $confidence }};
        const circle = document.getElementById('progressCircle');
        const counter = document.getElementById('confidenceCounter');
        if (!circle || !counter) return;

        const duration = 2000;
        const startTime = performance.now();
        
        const circumference = 502; // 2 * pi * 80

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out expo
            const easedProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            
            const currentValue = Math.floor(easedProgress * target);
            counter.innerText = currentValue + '%';
            
            const offset = circumference * (1 - (easedProgress * target / 100));
            circle.style.strokeDashoffset = offset;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        circle.style.strokeDasharray = circumference;
        requestAnimationFrame(update);
    }

    function toggleAccordion(header) {
        const item = header.parentElement;
        const wasActive = item.classList.contains('active');
        
        document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
        
        if (!wasActive) {
            item.classList.add('active');
        }
    }

    window.addEventListener('load', animateProgress);
</script>
@endpush

