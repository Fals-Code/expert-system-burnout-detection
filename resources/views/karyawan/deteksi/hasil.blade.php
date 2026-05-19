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
            <div class="header-icon" style="display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            </div>
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
            <h2 class="section-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Gejala yang Teridentifikasi
            </h2>
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

        {{-- ── Explanation Facility ── --}}
        @if(isset($explanation))
        <div class="explanation-section" style="margin-bottom: 2.5rem;">
            <h2 class="section-title" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Penjelasan Sistem Pakar
            </h2>
            
            {{-- Summary --}}
            <div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid var(--color-gray-200); border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; line-height: 1.8; font-size: 0.95rem; color: var(--color-gray-700);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span style="background: {{ $konsultasi->diagnosa->color }}20; color: {{ $konsultasi->diagnosa->color }}; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 800;">{{ $explanation['confidence_label'] }}</span>
                    <span style="font-size: 0.8rem; color: var(--color-gray-400);">Tingkat Keyakinan Sistem</span>
                </div>
                @php
                    $parsedSummary = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $explanation['summary']);
                    $parsedSummary = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedSummary);
                @endphp
                {!! $parsedSummary !!}
            </div>

            {{-- MBI Dimensions Analysis Visual --}}
            @if(isset($explanation['mbi_analysis']))
            <div style="background: white; border: 1px solid var(--color-gray-200); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.02);">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.1rem; font-weight: 800; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    Analisis Profil Maslach Burnout Inventory (MBI)
                </h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; align-items: center;">
                    {{-- Chart Canvas --}}
                    <div style="position: relative; max-width: 320px; margin: 0 auto; width: 100%; height: 320px;">
                        <canvas id="mbiRadarChart"></canvas>
                    </div>
                    
                    {{-- Horizontal visual progress bars --}}
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        {{-- EE --}}
                        <div style="background: #fff8f8; border: 1px solid #fee2e2; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.8rem; font-weight: 800; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px;">Kelelahan Emosional (EE)</span>
                                    <span style="font-size: 1rem; font-weight: 900; color: #b91c1c;">{{ $explanation['mbi_analysis']['ee_score'] }}%</span>
                                </div>
                                <div style="height: 6px; background: #fee2e2; border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <div style="width: {{ $explanation['mbi_analysis']['ee_score'] }}%; height: 100%; background: #b91c1c; border-radius: 3px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.75rem; color: #7f1d1d; line-height: 1.3;">Merasa terkuras secara fisik dan mental akibat beban tugas.</span>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 800; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 50px;">{{ $explanation['mbi_analysis']['ee_label'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- DP --}}
                        <div style="background: #fdfaf7; border: 1px solid #ffedd5; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.8rem; font-weight: 800; color: #c2410c; text-transform: uppercase; letter-spacing: 0.5px;">Depersonalisasi (DP)</span>
                                    <span style="font-size: 1rem; font-weight: 900; color: #c2410c;">{{ $explanation['mbi_analysis']['dp_score'] }}%</span>
                                </div>
                                <div style="height: 6px; background: #ffedd5; border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <div style="width: {{ $explanation['mbi_analysis']['dp_score'] }}%; height: 100%; background: #c2410c; border-radius: 3px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.75rem; color: #7c2d12; line-height: 1.3;">Perasaan sinis, dingin, dan hilangnya empati profesional.</span>
                                    <span class="badge" style="background: #ffedd5; color: #7c2d12; font-weight: 800; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 50px;">{{ $explanation['mbi_analysis']['dp_label'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- PA --}}
                        <div style="background: #faf5ff; border: 1px solid #f3e8ff; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.8rem; font-weight: 800; color: #6b21a8; text-transform: uppercase; letter-spacing: 0.5px;">Pencapaian Diri Rendah (PA)</span>
                                    <span style="font-size: 1rem; font-weight: 900; color: #6b21a8;">{{ $explanation['mbi_analysis']['pa_score'] }}%</span>
                                </div>
                                <div style="height: 6px; background: #f3e8ff; border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <div style="width: {{ $explanation['mbi_analysis']['pa_score'] }}%; height: 100%; background: #6b21a8; border-radius: 3px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.75rem; color: #581c87; line-height: 1.3;">Perasaan tidak kompeten dan penurunan kepuasan berprestasi.</span>
                                    <span class="badge" style="background: #f3e8ff; color: #581c87; font-weight: 800; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 50px;">{{ $explanation['mbi_analysis']['pa_label'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Personalized therapeutic recommendation card based on highest dimension --}}
            @php
                $eeVal = $explanation['mbi_analysis']['ee_score'] ?? 0;
                $dpVal = $explanation['mbi_analysis']['dp_score'] ?? 0;
                $paVal = $explanation['mbi_analysis']['pa_score'] ?? 0;
                
                $highestDimension = 'Kelelahan Emosional';
                $recommendationText = 'Fokus utama Anda saat ini adalah pemulihan energi fisik dan mental secara menyeluruh. Istirahatkan diri Anda, ambil jeda kecil, dan batasi beban kerja tambahan.';
                $recColor = '#b91c1c';
                $recBg = '#fff8f8';
                
                if ($dpVal > $eeVal && $dpVal > $paVal) {
                    $highestDimension = 'Depersonalisasi (Sinisme)';
                    $recommendationText = 'Anda terindikasi mengalami kejenuhan sosial dan hilangnya empati kerja. Cobalah untuk mengambil liburan kecil, ubah rutinitas harian, dan jalin kembali obrolan hangat dengan rekan dekat.';
                    $recColor = '#c2410c';
                    $recBg = '#fdfaf7';
                } elseif ($paVal > $eeVal && $paVal > $dpVal) {
                    $highestDimension = 'Pencapaian Diri Rendah';
                    $recommendationText = 'Anda mengalami krisis kepercayaan diri dan keraguan akan makna pekerjaan. Diskusikan dengan atasan Anda untuk penyesuaian pendelegasian tugas, rayakan setiap pencapaian kecil, dan cari mentor profesional.';
                    $recColor = '#6b21a8';
                    $recBg = '#faf5ff';
                }
            @endphp
            <div style="background: linear-gradient(135deg, {{ $recBg }} 0%, #ffffff 100%); border: 1.5px solid {{ $recColor }}30; border-radius: 20px; padding: 1.75rem 2rem; margin-bottom: 2.5rem; display: flex; align-items: flex-start; gap: 1.25rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $recColor }}20; color: {{ $recColor }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: {{ $recColor }};">
                        Rekomendasi Terapi Khusus: Fokus {{ $highestDimension }}
                    </h4>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.95rem; line-height: 1.7; color: var(--color-gray-700); font-weight: 600;">
                        {{ $recommendationText }}
                    </p>
                </div>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                {{-- Reasoning Chain --}}
                <div style="background: white; border: 1px solid var(--color-gray-200); border-radius: 16px; padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem 0; font-size: 0.9rem; font-weight: 800; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        Alur Penalaran (Reasoning Chain)
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($explanation['reasoning_chain'] as $i => $step)
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; flex-shrink: 0; margin-top: 2px;">{{ $i + 1 }}</div>
                            <p style="margin: 0; font-size: 0.85rem; line-height: 1.6; color: var(--color-gray-600);">{{ $step }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Dominant Symptoms --}}
                <div style="background: white; border: 1px solid var(--color-gray-200); border-radius: 16px; padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem 0; font-size: 0.9rem; font-weight: 800; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Gejala Dominan (Top 3)
                    </h3>
                    @if(count($explanation['dominant_symptoms']) > 0)
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @foreach($explanation['dominant_symptoms'] as $j => $sym)
                            <div style="background: var(--color-gray-50); border-radius: 12px; padding: 1rem; border-left: 4px solid {{ $konsultasi->diagnosa->color }};">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-gray-500);">#{{ $j + 1 }} {{ $sym['kode'] }}</span>
                                    <span style="font-size: 0.8rem; font-weight: 800; color: {{ $konsultasi->diagnosa->color }};">{{ $sym['impact'] }}%</span>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--color-gray-700);">{{ $sym['nama'] }}</p>
                                <div style="margin-top: 0.5rem; height: 4px; background: var(--color-gray-100); border-radius: 2px; overflow: hidden;">
                                    <div style="width: {{ min($sym['impact'], 100) }}%; height: 100%; background: {{ $konsultasi->diagnosa->color }}; border-radius: 2px;"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: var(--color-gray-400); font-size: 0.85rem; font-style: italic;">Tidak ada gejala dominan yang terdeteksi.</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <h2 class="section-title" style="display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            Rekomendasi Penanganan
        </h2>
        <div class="recommendation-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            @php
                $saran = explode("\n", $konsultasi->diagnosa->saran);
                $svgs = [
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>', // Time/meditation
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>', // Travel/escape
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>', // Balance
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16h20V4H2z"></path><path d="M2 8h20"></path><path d="M6 4v16"></path></svg>', // Rest
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>' // Health
                ];
            @endphp
            @foreach ($saran as $index => $rec)
                @if (trim($rec))
                <div class="rec-card" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 16px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="rec-icon" style="width: 48px; height: 48px; border-radius: 12px; background: {{ $konsultasi->diagnosa->bg_light }}; display: flex; align-items: center; justify-content: center; color: {{ $konsultasi->diagnosa->color }};">
                            {!! $svgs[$index % count($svgs)] !!}
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Initialize Maslach Burnout Inventory dimensions Radar Chart
    function initMBIRadarChart() {
        const ctx = document.getElementById('mbiRadarChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: [
                    ['Kelelahan', 'Emosional (EE)'],
                    ['Depersonalisasi', '(DP)'],
                    ['Pencapaian Diri', 'Rendah (PA)']
                ],
                datasets: [{
                    label: 'Skor Dimensi (%)',
                    data: [
                        {{ $explanation['mbi_analysis']['ee_score'] ?? 0 }},
                        {{ $explanation['mbi_analysis']['dp_score'] ?? 0 }},
                        {{ $explanation['mbi_analysis']['pa_score'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 20,
                        right: 20
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            color: 'rgba(226, 232, 240, 0.8)'
                        },
                        grid: {
                            color: 'rgba(226, 232, 240, 0.8)'
                        },
                        pointLabels: {
                            font: {
                                size: 10,
                                weight: '800',
                                family: "'Inter', sans-serif"
                            },
                            color: '#475569',
                            padding: 12
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const labelText = Array.isArray(context.label) ? context.label.join(' ') : context.label;
                                return 'Skor ' + labelText + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    window.addEventListener('load', () => {
        animateProgress();
        initMBIRadarChart();
    });
</script>
@endpush

