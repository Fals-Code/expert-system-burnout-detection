<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Burnout – {{ $konsultasi->user->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
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
            const opt = {
                margin:       0.5,
                filename:     'Laporan_Burnout_{{ $konsultasi->id }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            const btn = document.querySelector('.btn-print');
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin-right: 6px;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></svg> Sedang Memproses...';
            btn.disabled = true;

            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = '<svg class="btn-pdf-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg> Unduh Laporan (PDF)';
                btn.disabled = false;
            });
        }
    </script>

    <div class="report-paper">
        <!-- Kop Surat -->
        <header class="report-header">
            <div class="brand-kop">Burnout<span>Xpert</span></div>
            <div class="report-type">
                <h1>Laporan Analisis Burnout</h1>
                <p>Dokumen Resmi Hasil Deteksi Sistem Pakar</p>
            </div>
        </header>

        <!-- Informasi Karyawan -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Karyawan</div>
                <div class="info-value">{{ $konsultasi->user->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Deteksi</div>
                <div class="info-value">{{ $konsultasi->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Divisi / Posisi</div>
                <div class="info-value">
                    {{ $konsultasi->user->divisi ?? '-' }}
                    {{ isset($konsultasi->user->posisi) ? ' / ' . $konsultasi->user->posisi : '' }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Laporan</div>
                <div class="info-value" style="font-family: monospace; font-size: 0.9em;">BX-{{ $konsultasi->created_at->format('Ymd') }}-{{ $konsultasi->id }}</div>
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

        <!-- Gejala Terdeteksi -->
        @if ($konsultasi->gejala->isNotEmpty())
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
                *Laporan ini dihasilkan secara otomatis oleh Sistem Pakar BurnoutXpert menggunakan metode
                <em>Backward Chaining</em> dengan algoritma <em>Certainty Factor</em>.
                Hasil ini bersifat indikatif dan tidak menggantikan diagnosis profesional.
            </div>
            <div class="signature">
                <div style="font-size: 0.8rem; margin-bottom: 0.5rem;">Dicetak pada: {{ date('d M Y H:i') }}</div>
                <div class="signature-line">Sistem Pakar BurnoutXpert v2.0</div>
            </div>
        </div>
    </div>

</body>
</html>
