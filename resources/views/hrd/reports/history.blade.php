@extends('layouts.app')

@section('title', 'Riwayat Dukungan Karyawan – Ruang Check-in')

@section('content')
@php
    $histories = $user->konsultasi->sortBy('created_at')->values();
    $latest = $histories->last();
    $previous = $histories->count() > 1 ? $histories[$histories->count() - 2] : null;

    $labelFor = function ($diagnosisId) {
        return match ((int) $diagnosisId) {
            1 => 'Keseimbangan Stabil',
            2 => 'Butuh Dukungan Ekstra',
            3 => 'Perlu Pemantauan',
            4 => 'Perhatian Ringan',
            default => 'Ringkasan Evaluasi',
        };
    };

    $toneFor = function ($diagnosisId) {
        return match ((int) $diagnosisId) {
            1 => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
            2 => ['bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
            3 => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            4 => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            default => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'],
        };
    };

    $latestTone = $toneFor($latest?->diagnosa?->id);
    $latestLabel = $latest ? $labelFor($latest->diagnosa?->id) : 'Belum ada check-in';
    $latestScore = $latest ? number_format($latest->cf_final * 100, 1) : '-';
    $previousScore = $previous ? number_format($previous->cf_final * 100, 1) : null;
    $chartDates = $histories->map(fn($item) => $item->created_at->translatedFormat('d M'))->toArray();
    $chartScores = $histories->map(fn($item) => round($item->cf_final * 100, 1))->toArray();
@endphp

<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <div style="display:flex; align-items:flex-start; gap:1rem;">
        <a href="{{ route('hrd.employees') }}" class="btn-nav" style="padding:0.5rem; border-radius:999px; width:42px; height:42px; display:flex; align-items:center; justify-content:center; text-decoration:none;">←</a>
        <div>
            <p style="display:inline-flex; margin:0 0 .5rem; padding:.35rem .75rem; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase;">
                Support History
            </p>
            <h1 class="page-title" style="margin:0 0 .35rem;">Riwayat Dukungan Kerja</h1>
            <p style="margin:0; color:var(--color-gray-500); line-height:1.7; max-width:720px;">
                Halaman ini membantu HRD membaca pola check-in karyawan secara suportif. Gunakan data sebagai bahan dukungan dan perbaikan lingkungan kerja, bukan sebagai ranking performa individu. Akhirnya, dashboard tidak harus menjadi alat menakut-nakuti manusia.
            </p>
        </div>
    </div>
</div>

<section class="content-card" style="margin-bottom:1.5rem; background:linear-gradient(135deg,#eff6ff 0%,#ffffff 55%,#ecfdf5 100%); border:1px solid #dbeafe; padding:1.5rem;">
    <div style="display:grid; grid-template-columns:1.2fr repeat(3, minmax(0, .8fr)); gap:1rem; align-items:stretch;">
        <div style="background:white; border:1px solid #e2e8f0; border-radius:18px; padding:1rem;">
            <div style="font-size:.75rem; color:#64748b; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem;">Karyawan</div>
            <div style="font-size:1.2rem; font-weight:950; color:#0f172a; line-height:1.25;">{{ $user->nama }}</div>
            <div style="font-size:.86rem; color:#64748b; margin-top:.35rem;">{{ $user->divisi->nama ?? 'Unit belum tersedia' }}</div>
        </div>

        <div style="background:white; border:1px solid #e2e8f0; border-radius:18px; padding:1rem;">
            <div style="font-size:.75rem; color:#64748b; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem;">Total Check-in</div>
            <div style="font-size:2rem; font-weight:950; color:#1d4ed8; line-height:1;">{{ $histories->count() }}</div>
            <div style="font-size:.78rem; color:#64748b; margin-top:.5rem;">catatan tersimpan</div>
        </div>

        <div style="background:{{ $latestTone['bg'] }}; border:1px solid {{ $latestTone['border'] }}; border-radius:18px; padding:1rem;">
            <div style="font-size:.75rem; color:{{ $latestTone['text'] }}; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem;">Kondisi Terakhir</div>
            <div style="font-size:1rem; font-weight:950; color:{{ $latestTone['text'] }}; line-height:1.35;">{{ $latestLabel }}</div>
            <div style="font-size:.78rem; color:#64748b; margin-top:.5rem;">{{ $latest?->created_at?->translatedFormat('d M Y, H:i') ?? 'Belum ada data' }}</div>
        </div>

        <div style="background:white; border:1px solid #e2e8f0; border-radius:18px; padding:1rem;">
            <div style="font-size:.75rem; color:#64748b; font-weight:900; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem;">Skor Sistem</div>
            <div style="font-size:2rem; font-weight:950; color:#0f172a; line-height:1;">{{ $latestScore }}</div>
            <div style="font-size:.78rem; color:#64748b; margin-top:.5rem;">
                @if($previousScore)
                    sebelumnya {{ $previousScore }}
                @else
                    belum ada pembanding
                @endif
            </div>
        </div>
    </div>
</section>

<section class="content-card" style="margin-bottom:1.5rem; background:#f8fafc; border-color:#e2e8f0; padding:1.5rem;">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div style="background:white; border:1px solid #e2e8f0; border-radius:18px; padding:1rem;">
            <h3 style="margin:0 0 .6rem; color:#1e293b; font-size:1rem;">Prinsip Pembacaan HRD</h3>
            <ul style="margin:0; padding-left:1.1rem; color:#64748b; line-height:1.8; font-size:.88rem;">
                <li>Gunakan data untuk dukungan kerja, bukan hukuman.</li>
                <li>Hindari membandingkan karyawan sebagai ranking.</li>
                <li>Diskusi personal sebaiknya dilakukan dengan konteks dan persetujuan.</li>
            </ul>
        </div>
        <div style="background:white; border:1px solid #e2e8f0; border-radius:18px; padding:1rem;">
            <h3 style="margin:0 0 .6rem; color:#1e293b; font-size:1rem;">Arah Tindak Lanjut</h3>
            <ul style="margin:0; padding-left:1.1rem; color:#64748b; line-height:1.8; font-size:.88rem;">
                <li>Lihat pola beban kerja dan waktu kemunculan.</li>
                <li>Prioritaskan dukungan pada faktor kerja yang bisa diperbaiki.</li>
                <li>Gunakan bahasa suportif saat melakukan follow-up.</li>
            </ul>
        </div>
    </div>
</section>

@if($histories->isEmpty())
    <div class="content-card" style="text-align:center; padding:3rem;">
        <div style="width:64px; height:64px; border-radius:999px; background:#eff6ff; color:#1d4ed8; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:1.4rem; font-weight:900;">◷</div>
        <h3 style="color:var(--color-gray-700); margin-bottom:.5rem;">Belum Ada Check-in</h3>
        <p style="color:var(--color-gray-500); margin:0 auto; max-width:520px; line-height:1.7;">Karyawan ini belum memiliki catatan check-in kerja. Belum ada data yang perlu ditindaklanjuti.</p>
    </div>
@else
    <div style="display:grid; grid-template-columns:1.1fr .9fr; gap:1.5rem; align-items:start;">
        <section class="content-card" style="padding:1.5rem;">
            <h2 class="card-title" style="margin-bottom:.6rem;">Tren Check-in</h2>
            <p style="margin:0 0 1rem; color:var(--color-gray-500); line-height:1.7; font-size:.9rem;">Grafik ini membaca perubahan skor sistem dari waktu ke waktu. Angka ini adalah sinyal evaluasi, bukan nilai performa personal.</p>
            <div id="employeeHistoryChart" style="min-height:320px;"></div>
        </section>

        <section class="content-card" style="padding:1.5rem; background:#fff7ed; border-color:#fed7aa;">
            <h2 class="card-title" style="margin-bottom:.75rem; color:#9a3412;">Catatan Etis</h2>
            <p style="margin:0; color:#7c2d12; line-height:1.8; font-size:.9rem;">
                Jika kondisi terakhir menunjukkan kebutuhan dukungan, gunakan pendekatan percakapan yang aman: tanyakan beban kerja, hambatan, dan dukungan yang dibutuhkan. Jangan membuka percakapan dengan label kondisi. Itu bukan empati, itu jump scare administratif.
            </p>
        </section>
    </div>

    <section class="content-card" style="margin-top:1.5rem; padding:1.5rem;">
        <h2 class="card-title" style="margin-bottom:1rem;">Timeline Check-in</h2>
        <div class="timeline">
            @foreach($histories->sortByDesc('created_at') as $h)
                @php
                    $tone = $toneFor($h->diagnosa?->id);
                    $supportLabel = $labelFor($h->diagnosa?->id);
                    $areas = $h->gejala ?? collect();
                @endphp
                <article style="position:relative; padding-left:2rem; margin-bottom:1.25rem;">
                    <div style="position:absolute; left:0; top:.9rem; width:12px; height:12px; border-radius:999px; background:{{ $tone['text'] }}; box-shadow:0 0 0 4px {{ $tone['bg'] }};"></div>
                    @if(!$loop->last)
                        <div style="position:absolute; left:5px; top:1.65rem; width:2px; height:calc(100% + .5rem); background:#e2e8f0;"></div>
                    @endif

                    <div style="background:white; border:1px solid #e2e8f0; border-left:5px solid {{ $tone['text'] }}; border-radius:18px; padding:1.25rem; box-shadow:0 8px 18px rgba(15,23,42,.04);">
                        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
                            <div>
                                <div style="font-size:.78rem; color:#64748b; font-weight:800; margin-bottom:.4rem;">{{ $h->created_at->translatedFormat('d F Y, H:i') }}</div>
                                <h3 style="margin:0; color:{{ $tone['text'] }}; font-size:1.15rem;">{{ $supportLabel }}</h3>
                                <p style="margin:.45rem 0 0; color:#64748b; font-size:.88rem; line-height:1.65; max-width:720px;">{{ $h->diagnosa->deskripsi ?? 'Deskripsi ringkasan belum tersedia.' }}</p>
                            </div>
                            <div style="background:{{ $tone['bg'] }}; color:{{ $tone['text'] }}; border:1px solid {{ $tone['border'] }}; border-radius:14px; padding:.75rem 1rem; min-width:130px; text-align:center;">
                                <div style="font-size:1.35rem; line-height:1; font-weight:950;">{{ number_format($h->cf_final * 100, 1) }}</div>
                                <div style="font-size:.72rem; margin-top:.35rem; font-weight:800;">Skor Sistem</div>
                            </div>
                        </div>

                        <div style="margin-top:1.1rem;">
                            <div style="font-size:.75rem; font-weight:900; color:#475569; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.55rem;">Area yang Perlu Dukungan</div>
                            @if($areas->isEmpty())
                                <span style="display:inline-flex; background:#f8fafc; border:1px solid #e2e8f0; color:#64748b; font-size:.78rem; padding:.35rem .7rem; border-radius:999px;">Tidak ada rincian area tercatat</span>
                            @else
                                <div style="display:flex; flex-wrap:wrap; gap:.5rem;">
                                    @foreach($areas->take(5) as $g)
                                        <span class="badge" style="background:#f8fafc; border:1px solid #e2e8f0; color:#475569; font-size:.76rem; line-height:1.45;">{{ $g->nama }}</span>
                                    @endforeach
                                    @if($areas->count() > 5)
                                        <span class="badge" style="background:#e2e8f0; color:#475569; font-size:.76rem;">+{{ $areas->count() - 5 }} area lain</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(!$histories->isEmpty())
        const employeeHistoryChart = new ApexCharts(document.querySelector('#employeeHistoryChart'), {
            series: [{
                name: 'Skor Sistem',
                data: {!! json_encode($chartScores) !!}
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Poppins, sans-serif'
            },
            colors: ['#2563eb'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.04, stops: [0, 90, 100] }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: {!! json_encode($chartDates) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 5,
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                y: { formatter: function(val) { return val.toFixed(1) + ' skor sistem'; } }
            },
            markers: { size: 5, colors: ['#2563eb'], strokeColors: '#ffffff', strokeWidth: 2, hover: { size: 7 } }
        });

        employeeHistoryChart.render();
    @endif
});
</script>
@endpush

<style>
    @media (max-width: 960px) {
        [style*="grid-template-columns:1.2fr"],
        [style*="grid-template-columns:1.1fr"],
        [style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns:1fr !important;
        }
    }
</style>
