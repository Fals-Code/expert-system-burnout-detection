@extends('layouts.app')

@section('title', 'Dashboard Karyawan – BurnoutXpert')

@section('content')
    <!-- Welcome Banner Section -->
    <div class="welcome-banner" data-intro="Selamat datang di Dashboard Karyawan! Di sini Anda dapat melihat ringkasan aktivitas dan kondisi kesehatan mental Anda secara berkala." data-step="1">
        <div class="welcome-content">
            <h1 class="welcome-title">{{ $greet }}, {{ Auth::user()->nama }}!</h1>
            <p class="welcome-subtitle">Bagaimana perasaan Anda hari ini? Lakukan asesmen rutin untuk menjaga keseimbangan dan perkembangan kesehatan mental Anda.</p>
            <div style="margin-top: 1.5rem;">
                <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" data-intro="Klik tombol ini kapan saja Anda ingin memulai tes baru atau melanjutkan sesi tersimpan." data-step="2">Mulai Deteksi Sekarang</a>
            </div>
        </div>
        <div class="welcome-illustration">
            <svg width="200" height="150" viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="75" r="70" fill="white" fill-opacity="0.1"/>
                <path d="M140 90C140 112.091 122.091 130 100 130C77.9086 130 60 112.091 60 90C60 67.9086 77.9086 50 100 50C122.091 50 140 67.9086 140 90Z" fill="white" fill-opacity="0.2"/>
                <path d="M100 70V100M85 85H115" stroke="white" stroke-width="8" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <!-- Stat Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Stat 1: Total Deteksi -->
        <div class="content-card stat-card" data-intro="Pantau total asesmen yang telah Anda lakukan secara berkala." data-step="3">
            <div class="stat-icon" style="background: var(--color-primary-50); color: var(--color-primary);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $total_deteksi }}</div>
                <div class="stat-label">Total Deteksi Anda</div>
            </div>
        </div>

        <!-- Stat 2: Tren & Perubahan Skor -->
        <div class="content-card stat-card">
            @if($trend_direction === 'up')
                <div class="stat-icon" style="background: #FFF5F5; color: #E53E3E;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="color: #E53E3E;">+{{ round($score_change * 100, 1) }}%</div>
                    <div class="stat-label">Tren Skor (Meningkat)</div>
                </div>
            @elseif($trend_direction === 'down')
                <div class="stat-icon" style="background: #F0FFF4; color: #38A169;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="color: #38A169;">{{ round($score_change * 100, 1) }}%</div>
                    <div class="stat-label">Tren Skor (Membaik)</div>
                </div>
            @else
                <div class="stat-icon" style="background: #F7FAFC; color: #4A5568;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="color: #4A5568;">Stabil</div>
                    <div class="stat-label">Perubahan Terakhir</div>
                </div>
            @endif
        </div>

        <!-- Stat 3: Status / Hasil Terakhir -->
        <div class="content-card stat-card" data-intro="Hasil diagnosis dari asesmen terbaru Anda." data-step="4">
            <div class="stat-icon" style="background: {{ $last_result->diagnosa->bg_light ?? '#F8FAFB' }}; color: {{ $last_result->diagnosa->color ?? 'var(--color-gray-400)' }};">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color: {{ $last_result->diagnosa->color ?? 'inherit' }}; font-size: 1.1rem; line-height: 1.4;">
                    {{ $last_result ? $last_result->diagnosa->nama : 'Belum Ada' }}
                </div>
                <div class="stat-label">Hasil Terakhir</div>
            </div>
        </div>
    </div>

    <!-- Early Detection Warning Box -->
    @if($warning_flag)
        <div class="content-card warning-alert" style="background: rgba(220, 38, 38, 0.05); border-left: 4px solid #dc2626; padding: 1.5rem; margin-top: 1.5rem; display: flex; align-items: flex-start; gap: 1rem; border-radius: 8px; backdrop-filter: blur(10px);" data-intro="Sistem mendeteksi lonjakan stres yang cepat. Perhatikan rekomendasi pencegahan di sini!" data-step="5">
            <div style="background: #dc2626; color: white; border-radius: 50%; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem;">⚠️</div>
            <div>
                <h4 style="margin: 0 0 0.25rem 0; color: #dc2626; font-weight: 700;">Early Detection: Terdeteksi Lonjakan Stres Signifikan!</h4>
                <p style="margin: 0; font-size: 0.9rem; color: var(--color-gray-700); line-height: 1.5;">
                    Skor stres Certainty Factor Anda melonjak sebesar <strong>{{ round($score_change * 100, 1) }}%</strong> dibandingkan dengan hasil asesmen sebelumnya. Kami sangat menganjurkan Anda untuk segera mengambil jeda istirahat mikro (micro-break), membatasi jam kerja lembur, serta berkonsultasi ringan dengan HRD atau tim pakar demi menghindari kemerosotan burnout.
                </p>
            </div>
        </div>
    @endif

    <!-- Perkembangan Kesehatan Mental Anda (ApexCharts & Analytics) & HRIS Sync -->
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Left Panel: Longitudinal Trend Chart & HRIS Stats -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="content-card" style="padding: 1.5rem;" data-intro="Melacak perkembangan level kesehatan mental Anda dari waktu ke waktu secara interaktif." data-step="6">
                <h2 class="card-title" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    {{ __('Perkembangan Kesehatan Mental Anda') }}
                </h2>
                @if(count($chart_dates) > 0)
                    <div id="longitudinalTrendChart" style="min-height: 320px;"></div>
                @else
                    <div style="padding: 4rem 1rem; text-align: center; color: var(--color-gray-400);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 1rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Selesaikan deteksi pertama Anda untuk mulai memetakan grafik perkembangan kesehatan mental.</p>
                    </div>
                @endif
            </div>

            <!-- HRIS Integration Card -->
            <div class="content-card" style="padding: 1.5rem;" data-intro="Status sinkronisasi dengan sistem absensi & ketenagakerjaan internal perusahaan." data-step="9">
                <h2 class="card-title" style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        {{ __('Integrasi Data HRIS & Absensi') }}
                    </span>
                    <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 9999px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; background: #0284c7; border-radius: 50%;"></span>
                        SYNCED
                    </span>
                </h2>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">{{ $hrisMetrics['total_hours'] }}h</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 600; margin-top: 0.25rem;">Jam Kerja Bulan Ini</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: #ea580c;">{{ $hrisMetrics['overtime_hours'] }}h</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 600; margin-top: 0.25rem;">{{ __('Lembur bulan ini') }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: #dc2626;">{{ $hrisMetrics['late_arrivals'] }}x</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 600; margin-top: 0.25rem;">{{ __('Late clock-in') }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; color: #16a34a;">{{ $hrisMetrics['remaining_leaves'] }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-500); font-weight: 600; margin-top: 0.25rem;">{{ __('Sisa cuti tahunan') }}</div>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 12px; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="background: #16a34a; color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.75rem; font-weight: 800;">💡</div>
                    <div>
                        <strong style="font-size: 0.85rem; color: #15803d; display: block; margin-bottom: 0.15rem;">{{ __('Korelasi Stress') }}</strong>
                        <p style="margin: 0; font-size: 0.8rem; color: #166534; line-height: 1.4;">
                            {{ $hrisMetrics['correlation_message'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Simple Predictive Score & Empathetic Recommendations -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Simple Predictive Score Card -->
            <div class="content-card" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white;" data-intro="Sistem AI memprediksi tingkat stres Anda di bulan depan berdasarkan laju riwayat historis Anda menggunakan Simple Linear Regression." data-step="7">
                <h3 class="card-title" style="color: white; display: flex; align-items: center; gap: 8px; margin-bottom: 0.75rem; font-size: 1.1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    {{ __('Simple Predictive Stress Score') }}
                </h3>
                @if($predicted_score !== null)
                    <div style="margin-bottom: 0.75rem;">
                        <span style="font-size: 2rem; font-weight: 800; color: #38bdf8;">
                            {{ round($predicted_score * 100, 1) }}%
                        </span>
                        <div style="display: inline-block; margin-left: 8px; padding: 4px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(255,255,255,0.1); color: {{ $predicted_color }}">
                            {{ $predicted_status }}
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; line-height: 1.5; opacity: 0.85; margin: 0;">
                        Diproyeksikan menggunakan formula regresi linier riwayat historis Anda. Ambil langkah pencegahan dini hari ini untuk membelokkan laju stres ke arah yang lebih sehat!
                    </p>
                @else
                    <div style="padding: 1.5rem 0; text-align: center; color: rgba(255,255,255,0.4);">
                        Lakukan deteksi minimal 2 kali untuk mengaktifkan pemodelan prediksi stres masa depan.
                    </div>
                @endif
            </div>

            <!-- Tailored Recommendations -->
            <div class="content-card" style="flex: 1;" data-intro="Rekomendasi pemulihan adaptif yang disesuaikan secara personal dari para pakar kesehatan mental." data-step="8">
                <h3 class="card-title" style="margin-bottom: 0.75rem; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    {{ __('Saran Pemulihan Karyawan') }}
                </h3>
                <div style="font-size: 0.9rem; line-height: 1.6; color: var(--color-gray-600);">
                    <p style="margin-bottom: 0.75rem;"><strong>{{ __('Karyawan Berisiko Tinggi (Early Warning)') }}:</strong> {{ $recommendations['risk_narrative'] }}</p>
                    
                    <div style="margin-top: 1rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--color-primary); margin-bottom: 0.25rem;">
                            <span>📆</span> {{ __('Saran Cuti') }}
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--color-gray-700);">{{ $recommendations['leave_recommendation'] }}</p>
                    </div>

                    <div style="margin-top: 1rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #ea580c; margin-bottom: 0.25rem;">
                            <span>⏱️</span> Penyesuaian Jadwal
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--color-gray-700);">{{ $recommendations['schedule_recommendation'] }}</p>
                    </div>

                    <div style="margin-top: 1rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #16a34a; margin-bottom: 0.25rem;">
                            <span>🧘</span> {{ __('Saran Aktivitas') }}
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--color-gray-700);">{{ $recommendations['activity_recommendation'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Render Longitudinal Trend Chart using ApexCharts ──
    @if(count($chart_dates) > 0)
        const trendOptions = {
            series: [{
                name: 'Skor CF Stres (%)',
                data: {!! json_encode($chart_scores) !!}
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3.5
            },
            xaxis: {
                categories: {!! json_encode($chart_dates) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 5,
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0) + '%';
                    },
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                }
            },
            markers: {
                size: 5,
                colors: ['#3b82f6'],
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: { size: 7 }
            }
        };

        new ApexCharts(document.querySelector('#longitudinalTrendChart'), trendOptions).render();
    @endif

    // ── Tour Guide Dashboard ──
    if (!localStorage.getItem('tour_completed_karyawan_v2')) {
        setTimeout(() => {
            introJs().setOptions({
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Mengerti',
                showStepNumbers: true,
                showBullets: true,
                overlayOpacity: 0.6
            }).start().oncomplete(function() {
                localStorage.setItem('tour_completed_karyawan_v2', 'true');
            }).onexit(function() {
                localStorage.setItem('tour_completed_karyawan_v2', 'true');
            });
        }, 500);
    }
});
</script>
@endpush
