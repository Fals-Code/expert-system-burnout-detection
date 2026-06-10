@extends('layouts.app')

@section('title', 'Dashboard Karyawan – BurnoutXpert')

@section('content')
@php
    $lastDiagnosisId = (int) ($last_result->diagnosa->id ?? 0);
    $lastLabel = match ($lastDiagnosisId) {
        1 => 'Keseimbangan Stabil',
        2 => 'Butuh Dukungan Ekstra',
        3 => 'Perlu Pemantauan',
        4 => 'Perhatian Ringan',
        default => 'Belum ada check-in',
    };

    $trendLabel = match ($trend_direction) {
        'up' => 'Ada sinyal beban meningkat',
        'down' => 'Kondisi cenderung membaik',
        default => 'Kondisi relatif stabil',
    };

    $trendColor = match ($trend_direction) {
        'up' => '#f97316',
        'down' => '#16a34a',
        default => '#2563eb',
    };
@endphp

<section class="welcome-banner" style="background:linear-gradient(135deg,#eff6ff 0%,#ffffff 58%,#ecfdf5 100%); color:#0f172a;" data-intro="Dashboard ini adalah ruang pribadi untuk melihat check-in kerja, riwayat, dan rekomendasi dukungan." data-step="1">
    <div class="welcome-content">
        <p style="display:inline-flex; align-items:center; gap:0.5rem; margin:0 0 0.75rem; padding:0.35rem 0.75rem; border-radius:999px; background:#dbeafe; color:#1d4ed8; font-size:0.75rem; font-weight:900; letter-spacing:0.1em; text-transform:uppercase;">
            Ruang pribadi karyawan
        </p>
        <h1 class="welcome-title" style="color:#0f172a;">{{ $greet }}, {{ Auth::user()->nama }}!</h1>
        <p class="welcome-subtitle" style="color:#475569; max-width:680px; line-height:1.75;">
            Bagaimana kondisi kerja Anda minggu ini? Check-in singkat membantu memahami pola energi, beban kerja, dan dukungan yang Anda rasakan tanpa menjadikannya penilaian performa.
        </p>
        <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="{{ route('karyawan.deteksi.intro') }}" class="btn-cta" data-intro="Mulai check-in kerja singkat atau lanjutkan sesi yang tersimpan." data-step="2">Mulai Check-in Kerja</a>
            <a href="{{ route('karyawan.history') }}" class="btn-nav btn-prev" style="text-decoration:none; border-color:#bfdbfe; color:#1d4ed8;">Lihat Riwayat Saya</a>
        </div>
    </div>
</section>

<div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; margin-top:1.5rem;">
    <div class="content-card stat-card" style="background:#eff6ff; border-color:#bfdbfe;" data-intro="Jumlah check-in pribadi yang pernah Anda isi." data-step="3">
        <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8;">✓</div>
        <div class="stat-info">
            <div class="stat-value" style="color:#1e3a8a;">{{ $total_deteksi }}</div>
            <div class="stat-label">Total Check-in</div>
        </div>
    </div>

    <div class="content-card stat-card" style="background:#f8fafc; border-color:#e2e8f0;">
        <div class="stat-icon" style="background:#ffffff; color:{{ $trendColor }};">◐</div>
        <div class="stat-info">
            <div class="stat-value" style="color:{{ $trendColor }}; font-size:1rem; line-height:1.35;">{{ $trendLabel }}</div>
            <div class="stat-label">Perubahan Terakhir</div>
        </div>
    </div>

    <div class="content-card stat-card" style="background:{{ $last_result->diagnosa->bg_light ?? '#f8fafc' }}; border-color:#e2e8f0;">
        <div class="stat-icon" style="background:#ffffff; color:{{ $last_result->diagnosa->color ?? '#64748b' }};">◇</div>
        <div class="stat-info">
            <div class="stat-value" style="color:{{ $last_result->diagnosa->color ?? '#334155' }}; font-size:1rem; line-height:1.35;">{{ $lastLabel }}</div>
            <div class="stat-label">Kondisi Terakhir</div>
        </div>
    </div>
</div>

@if($warning_flag)
    <div class="content-card" style="background:#fff7ed; border-left:4px solid #f97316; padding:1.5rem; margin-top:1.5rem; display:flex; align-items:flex-start; gap:1rem; border-radius:16px;" data-intro="Jika ada perubahan cukup terasa, dashboard memberi pengingat yang tenang dan suportif." data-step="4">
        <div style="background:#fed7aa; color:#9a3412; border-radius:999px; width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:900;">i</div>
        <div>
            <h4 style="margin:0 0 0.35rem; color:#9a3412; font-weight:900;">Kondisi Minggu Ini Perlu Perhatian</h4>
            <p style="margin:0; font-size:0.9rem; color:#7c2d12; line-height:1.65;">
                Ada perubahan skor yang cukup terasa dibanding check-in sebelumnya. Ini bukan alarm performa. Gunakan sebagai pengingat untuk mengatur prioritas, mengambil jeda, atau mendiskusikan beban kerja jika kondisi berlanjut.
            </p>
        </div>
    </div>
@endif

<div style="display:grid; grid-template-columns:1.4fr 1fr; gap:1.5rem; margin-top:1.5rem;">
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        <section class="content-card" style="padding:1.5rem;" data-intro="Grafik ini membantu melihat perubahan kondisi pribadi dari waktu ke waktu." data-step="5">
            <h2 class="card-title" style="margin-bottom:0.75rem;">Riwayat Pribadi</h2>
            <p style="margin:0 0 1rem; color:var(--color-gray-500); line-height:1.7; font-size:0.9rem;">
                Pantau perubahan kondisi kerja Anda dari waktu ke waktu. Angka di sini adalah sinyal refleksi pribadi, bukan ranking atau penilaian performa.
            </p>
            @if(count($chart_dates) > 0)
                <div id="longitudinalTrendChart" style="min-height:320px;"></div>
            @else
                <div style="padding:4rem 1rem; text-align:center; color:var(--color-gray-400); background:#f8fafc; border-radius:18px; border:1px dashed #cbd5e1;">
                    <p style="margin:0 0 0.5rem; font-weight:900; color:#475569;">Belum ada grafik pribadi</p>
                    <p style="margin:0;">Isi check-in pertama untuk mulai melihat perkembangan kondisi kerja Anda.</p>
                </div>
            @endif
        </section>

        <section class="content-card" style="padding:1.5rem; background:#f8fafc; border-color:#e2e8f0;">
            <h2 class="card-title" style="margin-bottom:0.75rem;">Transparansi Data</h2>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                    <div style="font-weight:900; color:#1e293b; margin-bottom:0.5rem;">Yang Anda lihat</div>
                    <ul style="margin:0; padding-left:1.1rem; color:#64748b; line-height:1.8; font-size:0.88rem;">
                        <li>Riwayat pribadi</li>
                        <li>Insight kondisi kerja</li>
                        <li>Rekomendasi dukungan</li>
                    </ul>
                </div>
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:1rem;">
                    <div style="font-weight:900; color:#1e293b; margin-bottom:0.5rem;">Yang ditekankan sistem</div>
                    <ul style="margin:0; padding-left:1.1rem; color:#64748b; line-height:1.8; font-size:0.88rem;">
                        <li>Pola beban kerja</li>
                        <li>Kebutuhan dukungan</li>
                        <li>Perbaikan lingkungan kerja</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        <section class="content-card" style="background:linear-gradient(135deg,#eff6ff,#f8fafc); border-color:#bfdbfe; padding:1.5rem;" data-intro="Saran kecil yang bisa dilakukan tanpa membuat proses terasa menghakimi." data-step="6">
            <h3 class="card-title" style="margin-bottom:0.75rem;">Rekomendasi Kecil Minggu Ini</h3>
            <div style="display:flex; flex-direction:column; gap:0.9rem; font-size:0.9rem; line-height:1.65; color:#475569;">
                <div style="background:white; border:1px solid #dbeafe; border-radius:14px; padding:0.9rem;">
                    <strong style="color:#1d4ed8; display:block; margin-bottom:0.25rem;">Atur prioritas</strong>
                    Catat satu tugas yang paling menguras energi, lalu pilih langkah kecil pertama yang paling realistis.
                </div>
                <div style="background:white; border:1px solid #dbeafe; border-radius:14px; padding:0.9rem;">
                    <strong style="color:#1d4ed8; display:block; margin-bottom:0.25rem;">Jaga ritme kerja</strong>
                    Gunakan jeda pendek agar energi tidak turun drastis di tengah hari.
                </div>
                <div style="background:white; border:1px solid #dbeafe; border-radius:14px; padding:0.9rem;">
                    <strong style="color:#1d4ed8; display:block; margin-bottom:0.25rem;">Minta dukungan bila perlu</strong>
                    Jika beban kerja terasa berat berulang, diskusikan prioritas atau hambatan dengan pihak yang tepat.
                </div>
            </div>
        </section>

        <section class="content-card" style="padding:1.5rem;">
            <h3 class="card-title" style="margin-bottom:0.75rem;">Konteks Kerja</h3>
            <p style="margin:0 0 1rem; color:var(--color-gray-500); line-height:1.7; font-size:0.88rem;">
                Data kerja pendukung ditampilkan sebagai konteks pribadi, bukan vonis. Sistem wellbeing yang baik tidak perlu berubah jadi CCTV pakai dasbor.
            </p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div style="background:#f8fafc; padding:0.9rem; border-radius:12px; border:1px solid #e2e8f0; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:900; color:#2563eb;">{{ $hrisMetrics['total_hours'] }}h</div>
                    <div style="font-size:0.72rem; color:#64748b; font-weight:700; margin-top:0.25rem;">Jam kerja bulan ini</div>
                </div>
                <div style="background:#fff7ed; padding:0.9rem; border-radius:12px; border:1px solid #fed7aa; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:900; color:#f97316;">{{ $hrisMetrics['overtime_hours'] }}h</div>
                    <div style="font-size:0.72rem; color:#64748b; font-weight:700; margin-top:0.25rem;">Lembur bulan ini</div>
                </div>
                <div style="background:#f8fafc; padding:0.9rem; border-radius:12px; border:1px solid #e2e8f0; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:900; color:#475569;">{{ $hrisMetrics['late_arrivals'] }}x</div>
                    <div style="font-size:0.72rem; color:#64748b; font-weight:700; margin-top:0.25rem;">Keterlambatan</div>
                </div>
                <div style="background:#f0fdf4; padding:0.9rem; border-radius:12px; border:1px solid #bbf7d0; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:900; color:#16a34a;">{{ $hrisMetrics['remaining_leaves'] }}</div>
                    <div style="font-size:0.72rem; color:#64748b; font-weight:700; margin-top:0.25rem;">Sisa cuti</div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($chart_dates) > 0)
        const trendOptions = {
            series: [{
                name: 'Skor Keseimbangan',
                data: {!! json_encode($chart_scores) !!}
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#2563eb'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.32, opacityTo: 0.04, stops: [0, 90, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: {!! json_encode($chart_dates) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 5,
                labels: {
                    formatter: function(val) { return val.toFixed(0); },
                    style: { colors: '#94a3b8', fontSize: '11px' }
                }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                y: { formatter: function(val) { return val.toFixed(1) + ' skor keseimbangan'; } }
            },
            markers: { size: 5, colors: ['#2563eb'], strokeColors: '#ffffff', strokeWidth: 2, hover: { size: 7 } }
        };

        new ApexCharts(document.querySelector('#longitudinalTrendChart'), trendOptions).render();
    @endif

    if (window.OnboardingHelper && window.OnboardingHelper.shouldShow('karyawan_dashboard')) {
        setTimeout(() => {
            introJs().setOptions({
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Mengerti',
                showStepNumbers: true,
                showBullets: true,
                overlayOpacity: 0.6
            }).start();
        }, 1200);
    }
});
</script>
@endpush

<style>
    @media (max-width: 960px) {
        [style*="grid-template-columns:repeat(3"],
        [style*="grid-template-columns:1.4fr 1fr"],
        [style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns:1fr !important;
        }
    }
</style>
