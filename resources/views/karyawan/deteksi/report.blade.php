<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan SanctuaryHub - {{ $konsultasi->user->nama }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
    <style>
        :root {
            --color-primary: #1A2B40;
            --color-accent: #3b82f6;
            --color-gray-100: #f1f5f9;
            --color-gray-200: #e2e8f0;
            --color-gray-400: #94a3b8;
            --color-gray-500: #64748b;
        }
    </style>
</head>
<body class="report-body">

    <div class="controls">
        <a href="{{ route('karyawan.history') }}" class="btn btn-back" style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 19"></polyline></svg>
            Kembali ke Riwayat
        </a>
        <button onclick="generatePDF()" class="btn btn-print" style="display: inline-flex; align-items: center; gap: 6px;">
            <svg class="btn-pdf-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            Unduh Laporan (PDF)
        </button>
    </div>

    <!-- html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.querySelector('.report-paper');
            
            // Simpan style lama
            const originalShadow = element.style.boxShadow;
            element.style.boxShadow = 'none';

            const opt = {
                margin:       0,
                filename:     'Laporan_Burnout_{{ str_replace(' ', '_', $konsultasi->user->nama) }}_{{ $konsultasi->created_at->format('Ymd') }}.pdf',
                image:        { type: 'jpeg', quality: 1.0 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true,
                    letterRendering: true,
                    scrollX: 0,
                    scrollY: 0
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            const btn = document.querySelector('.btn-print');
            const btnHTML = btn.innerHTML;
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin-right: 6px;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></svg> Memproses...';
            btn.disabled = true;

            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = btnHTML;
                btn.disabled = false;
                element.style.boxShadow = originalShadow;
            }).catch(err => {
                btn.innerHTML = btnHTML;
                btn.disabled = false;
                element.style.boxShadow = originalShadow;
            });
        }
    </script>

    <div class="report-paper">
        <!-- Kop Surat -->
        <header class="report-header">
            <div class="brand-kop">Sanctuary<span>Hub</span></div>
            <div class="report-type">
                <h1>Laporan Analisis Burnout</h1>
                <p>Dokumen Resmi Hasil Deteksi Sistem Pakar</p>
            </div>
        </header>

        <!-- Informasi Karyawan -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Karyawan</div>
                <div class="info-value">{{ $konsultasi->user->nama }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Deteksi</div>
                <div class="info-value">{{ $konsultasi->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Divisi / Posisi</div>
                <div class="info-value">
                    {{ $konsultasi->user->divisi?->nama ?? '-' }}
                    {{ isset($konsultasi->user->posisi) ? ' / ' . $konsultasi->user->posisi : '' }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Laporan</div>
                <div class="info-value" style="font-family: monospace; font-size: 0.9em;">SH-{{ $konsultasi->created_at->format('Ymd') }}-{{ $konsultasi->id }}</div>
            </div>
        </div>

        <!-- Hasil Deteksi -->
        <div class="result-box" style="border-left: 4px solid {{ $konsultasi->diagnosa->color }}; background: {{ $konsultasi->diagnosa->bg_light }};">
            <div class="info-label" style="color: {{ $konsultasi->diagnosa->color }};">Hasil Diagnosis Utama</div>
            <div class="result-level" style="color: {{ $konsultasi->diagnosa->color }};">{{ $konsultasi->diagnosa->nama }}</div>
            <div class="result-conf">Tingkat Keyakinan Sistem: <strong>{{ $confidence }}%</strong></div>
        </div>

        <!-- Deskripsi -->
        <h2 class="section-title">Analisis Kondisi</h2>
        <p class="content-para">{{ $konsultasi->diagnosa->deskripsi }}</p>

        <!-- Penjelasan Pakar & MBI Analysis -->
        @if(isset($explanation))
        <h2 class="section-title">Penjelasan Sistem Pakar</h2>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1rem; font-size: 0.9em; line-height: 1.6; color: #334155;">
            <strong>Ringkasan Pakar:</strong> 
            {{ $explanation['summary'] }}
        </div>
        
        @if(isset($explanation['mbi_analysis']))
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #fff8f8; border: 1px solid #fee2e2; border-radius: 8px; padding: 0.75rem; text-align: center;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #b91c1c; letter-spacing: 0.5px;">AREA EMOSIONAL</div>
                <div style="font-size: 1.25rem; font-weight: 900; color: #b91c1c; margin: 0.25rem 0;">{{ $explanation['mbi_analysis']['ee_score'] }}%</div>
                <span style="font-size: 0.7rem; background: #fee2e2; color: #991b1b; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $explanation['mbi_analysis']['ee_label'] }}</span>
            </div>
            <div style="background: #fdfaf7; border: 1px solid #ffedd5; border-radius: 8px; padding: 0.75rem; text-align: center;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #c2410c; letter-spacing: 0.5px;">AREA PERILAKU</div>
                <div style="font-size: 1.25rem; font-weight: 900; color: #c2410c; margin: 0.25rem 0;">{{ $explanation['mbi_analysis']['dp_score'] }}%</div>
                <span style="font-size: 0.7rem; background: #ffedd5; color: #7c2d12; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $explanation['mbi_analysis']['dp_label'] }}</span>
            </div>
            <div style="background: #faf5ff; border: 1px solid #f3e8ff; border-radius: 8px; padding: 0.75rem; text-align: center;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #6b21a8; letter-spacing: 0.5px;">AREA KOGNITIF</div>
                <div style="font-size: 1.25rem; font-weight: 900; color: #6b21a8; margin: 0.25rem 0;">{{ $explanation['mbi_analysis']['pa_score'] }}%</div>
                <span style="font-size: 0.7rem; background: #f3e8ff; color: #581c87; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $explanation['mbi_analysis']['pa_label'] }}</span>
            </div>
        </div>
        @endif
        @endif

        <!-- Rincian Jawaban & Skor Gejala -->
        @if (isset($tracing['gejala_details']) && count($tracing['gejala_details']) > 0)
        <h2 class="section-title">Rincian Jawaban & Skor Gejala</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85em; margin-bottom: 1.5rem;">
            <thead>
                <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 0.6rem 0.75rem; text-align: left; font-weight: 700; color: #334155; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Kode</th>
                    <th style="padding: 0.6rem 0.75rem; text-align: left; font-weight: 700; color: #334155; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Gejala</th>
                    <th style="padding: 0.6rem 0.75rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Jawaban</th>
                    <th style="padding: 0.6rem 0.75rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">CF User</th>
                    <th style="padding: 0.6rem 0.75rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tracing['gejala_details'] as $detail)
                @php
                    $cf = $detail['cf_user'] ?? 0;
                    $ansBg = '#f1f5f9'; $ansCol = '#64748b';
                    if ($cf >= 0.8) { $ansBg = '#fee2e2'; $ansCol = '#dc2626'; }
                    elseif ($cf > 0) { $ansBg = '#ffedd5'; $ansCol = '#ea580c'; }
                    elseif ($cf < 0) { $ansBg = '#dcfce7'; $ansCol = '#16a34a'; }
                @endphp
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 0.5rem 0.75rem; font-family: monospace; font-weight: 600; color: #64748b;">{{ $detail['kode'] }}</td>
                    <td style="padding: 0.5rem 0.75rem; color: #334155;">{{ $detail['gejala'] }}</td>
                    <td style="padding: 0.5rem 0.75rem; text-align: center;">
                        <span style="background: {{ $ansBg }}; color: {{ $ansCol }}; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 700; font-size: 0.8em; white-space: nowrap;">{{ $detail['user_ans'] ?? '-' }}</span>
                    </td>
                    <td style="padding: 0.5rem 0.75rem; text-align: center; font-family: monospace; font-weight: 600; color: {{ $ansCol }};">{{ number_format($cf, 2) }}</td>
                    <td style="padding: 0.5rem 0.75rem; text-align: center; font-family: monospace; font-weight: 600;">{{ number_format(($detail['cf_sub'] ?? 0) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif ($konsultasi->gejala->isNotEmpty())
        <h2 class="section-title">Gejala yang Terdeteksi</h2>
        <ul class="content-para" style="padding-left: 1.5rem; line-height: 2;">
            @foreach ($konsultasi->gejala as $g)
            <li>{{ $g->nama }}</li>
            @endforeach
        </ul>
        @endif

        <!-- Rekomendasi -->
        <h2 class="section-title">Rekomendasi Tindak Lanjut</h2>
        <div class="rec-list">
            @php
                $saran = explode("\n", $konsultasi->diagnosa->saran);
                $icons = ['🧘', '✈️', '⚖️', '😴', '🍎'];
            @endphp
            @foreach ($saran as $index => $rec)
                @if (trim($rec))
                <div class="rec-item">
                    <div class="rec-bullet">{{ $index + 1 }}</div>
                    <div class="rec-text">
                        <h3>{{ Str::before($rec, ':') }}</h3>
                        <p>{{ Str::after($rec, ':') }}</p>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Penutup / Tanda Tangan -->
        <div class="report-footer">
            <div style="font-size: 0.7rem; color: var(--color-gray-400); max-width: 320px; line-height: 1.6;">
                *Laporan ini dihasilkan secara otomatis oleh Sistem Pakar SanctuaryHub menggunakan metode
                <em>Backward Chaining</em> dengan algoritma <em>Certainty Factor</em>.
                Hasil ini bersifat indikatif dan tidak menggantikan diagnosis profesional.
            </div>
            <div class="signature">
                <div style="font-size: 0.8rem; margin-bottom: 0.5rem;">Dicetak pada: {{ date('d M Y H:i') }}</div>
                <div class="signature-line">Sistem Pakar SanctuaryHub</div>
            </div>
        </div>
    </div>

</body>
</html>
