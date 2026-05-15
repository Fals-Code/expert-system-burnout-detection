@extends('layouts.app')

@section('title', 'Hasil Diagnosis – BurnoutXpert')

@section('content')
<style>
    @media (max-width: 768px) {
        #confidenceCounter { font-size: 3.5rem !important; }
        .main-result-card { padding: 2rem 1rem !important; border-radius: 16px !important; }
        .level-label { font-size: 1.2rem !important; padding: 0.5rem 1.5rem !important; }
        .pill-group > div { flex-direction: column; align-items: flex-start !important; gap: 0.5rem; }
        .rec-card { flex-direction: column !important; }
        .action-group { flex-direction: column; }
        .action-group .btn-action { width: 100%; justify-content: center; }
    }
</style>
<div class="main-wrapper" style="margin-left: 0; padding: 0;">
    <main class="result-container">
        <div class="result-header">
            <div class="header-icon">🔥</div>
            <div class="header-text">
                <h1>Hasil Deteksi Burnout Anda</h1>
                <p>Berdasarkan analisis sistem pakar terhadap gejala yang Anda laporkan.</p>
            </div>
        </div>

        <div class="main-result-card" style="background: linear-gradient(135deg, {{ $konsultasi->diagnosa->bg_light }} 0%, #ffffff 100%); border: 1px solid {{ $konsultasi->diagnosa->color }}40; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05); padding: 3rem 2rem; display: flex; flex-direction: column; align-items: center; text-align: center; border-radius: 24px; margin-bottom: 2.5rem;">
            <!-- Decorative blur -->
            <div style="position: absolute; width: 300px; height: 300px; background: {{ $konsultasi->diagnosa->color }}20; filter: blur(80px); border-radius: 50%; top: -100px; left: -100px; pointer-events: none;"></div>
            <div style="position: absolute; width: 200px; height: 200px; background: {{ $konsultasi->diagnosa->color }}15; filter: blur(60px); border-radius: 50%; bottom: -50px; right: -50px; pointer-events: none;"></div>
            
            <h2 style="font-size: 1.2rem; color: var(--color-gray-500); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1rem; font-weight: 700; z-index: 1;">Tingkat Burnout</h2>
            
            <div class="level-label" style="background: {{ $konsultasi->diagnosa->color }}; color: white; padding: 0.75rem 2.5rem; border-radius: 50px; font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; box-shadow: 0 10px 20px {{ $konsultasi->diagnosa->color }}40; margin-bottom: 1.5rem; z-index: 1;">
                {{ strtoupper($konsultasi->diagnosa->nama) }}
            </div>
            
            <div style="font-size: 5rem; font-weight: 900; color: {{ $konsultasi->diagnosa->color }}; line-height: 1; margin-bottom: 0.5rem; text-shadow: 2px 4px 10px rgba(0,0,0,0.1); z-index: 1;" id="confidenceCounter">
                {{ $confidence }}%
            </div>
            <div style="font-size: 1rem; color: var(--color-gray-600); font-weight: 600; margin-bottom: 2.5rem; z-index: 1; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                Akurasi Analisis Sistem Pakar
            </div>
            
            <p class="condition-desc" style="max-width: 650px; font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700); background: rgba(255,255,255,0.7); padding: 1.5rem 2rem; border-radius: 16px; backdrop-filter: blur(10px); z-index: 1; margin: 0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5), 0 4px 15px rgba(0,0,0,0.03);">
                {{ $konsultasi->diagnosa->deskripsi }}
            </p>
        </div>

        <!-- Gejala yang Teridentifikasi -->
        <div class="symptoms-section">
            <h2 class="section-title">🔍 Gejala yang Teridentifikasi</h2>
            <div class="pill-group" style="display: flex; flex-direction: column; gap: 0.75rem;">
                @if ($konsultasi->gejala->isEmpty())
                    <p style="color: var(--color-gray-400); font-size: 0.9rem; font-style: italic;">Tidak ada gejala signifikan yang dilaporkan (Semua jawaban bernilai Tidak).</p>
                @else
                    @foreach ($konsultasi->gejala as $g)
                        <div style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <div style="font-weight: 500; color: var(--color-gray-700); font-size: 0.95rem;">
                                {{ $g->nama }}
                            </div>
                            <div style="padding: 0.35rem 1rem; border-radius: 50px; font-weight: 700; font-size: 0.85rem; background: #dcfce7; color: #16a34a;">
                                Ya
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <h2 class="section-title">✨ Rekomendasi Penanganan</h2>
        <div class="recommendation-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            @php
                $saran = explode("\n", $konsultasi->diagnosa->saran);
                $icons = ['🧘', '✈️', '⚖️', '😴', '🍎'];
            @endphp
            @foreach ($saran as $index => $rec)
                @if (trim($rec))
                <div class="rec-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 16px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="rec-icon" style="width: 48px; height: 48px; border-radius: 12px; background: {{ $konsultasi->diagnosa->bg_light }}; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            {{ $icons[$index % count($icons)] }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem;">Prioritas {{ $index + 1 }}</div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--color-gray-800); line-height: 1.4;">{{ Str::before($rec, ':') }}</h3>
                        </div>
                    </div>
                    <div style="color: var(--color-gray-600); font-size: 0.95rem; line-height: 1.6; padding-left: 0.5rem; border-left: 3px solid {{ $konsultasi->diagnosa->color }}40;">
                        {{ Str::after($rec, ':') }}
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
                    
                    @if(isset($tracing['cf_combine_gejala']) && isset($tracing['cf_pakar_rule']))
                    <div style="background: rgba(255,255,255,0.1); padding: 0.75rem; border-radius: 8px; margin: 0.75rem 0; font-family: monospace; font-size: 0.85rem; line-height: 1.6;">
                        <div style="color: rgba(255,255,255,0.8);">Rumus: CF_final = CF_combine_gejala × CF_pakar</div>
                        <div>CF_final = {{ number_format($tracing['cf_combine_gejala'], 4) }} × {{ number_format($tracing['cf_pakar_rule'], 2) }} = <strong>{{ number_format($konsultasi->cf_final, 4) }}</strong></div>
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

    // Animation for confidence counter
    function animateProgress() {
        const target = {{ $confidence }};
        const counter = document.getElementById('confidenceCounter');
        if (!counter) return;

        const duration = 2000;
        const startTime = performance.now();

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out expo
            const easedProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            
            const currentValue = Math.floor(easedProgress * target);
            counter.innerText = currentValue + '%';
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }



    window.addEventListener('load', animateProgress);
</script>
@endpush

