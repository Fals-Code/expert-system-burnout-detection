@extends('layouts.app')

@section('title', 'Survei Evaluasi Kenyamanan Lingkungan Kerja Karyawan – BurnoutXpert')

@section('content')
<style>
    .survey-shell {
        max-width: 980px;
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }
    .survey-hero {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #ecfdf5 100%);
        border: 1px solid #dbeafe;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
    }
    .survey-hero h1 {
        margin: 0 0 0.75rem;
        color: var(--color-primary);
        font-size: clamp(1.6rem, 3vw, 2.35rem);
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: -0.03em;
    }
    .survey-hero p {
        margin: 0;
        color: var(--color-gray-600);
        max-width: 760px;
        line-height: 1.75;
        font-size: 0.98rem;
    }
    .progress-panel {
        margin-top: 1.5rem;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
    }
    .progress-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--color-gray-600);
    }
    .progress-track {
        height: 9px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #10b981);
    }
    .question-list {
        max-width: 896px;
        margin: 0 auto;
        padding: 0.5rem 1rem 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .question-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .question-text {
        margin: 0 0 1.5rem;
        color: #1e293b;
        font-size: 1.2rem;
        line-height: 1.55;
        font-weight: 900;
        letter-spacing: -0.02em;
        text-align: center;
    }
    .likert-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .likert-option {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .likert-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }
    .likert-option input {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        margin-top: 0.15rem;
        accent-color: #ea580c;
    }
    .likert-label {
        display: block;
        font-weight: 900;
        color: #1e293b;
        font-size: 0.9rem;
        line-height: 1.35;
    }
    .likert-desc {
        display: block;
        margin-top: 0.18rem;
        color: #94a3b8;
        font-size: 0.76rem;
        line-height: 1.4;
    }
    .survey-actions {
        max-width: 896px;
        margin: 1.5rem auto 0;
        padding: 0 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .neutral-note {
        color: var(--color-gray-500);
        font-size: 0.82rem;
        line-height: 1.6;
        max-width: 560px;
    }
    @media (max-width: 768px) {
        .survey-hero, .question-card { padding: 1.25rem; }
        .question-list { padding: 0.5rem 0.75rem 0; }
        .question-text { font-size: 1.05rem; text-align: left; }
        .likert-grid { grid-template-columns: 1fr; }
        .survey-actions { flex-direction: column; align-items: stretch; padding: 0 0.75rem; }
        .survey-actions button { width: 100%; justify-content: center; }
    }
</style>

<div class="main-wrapper" style="margin-left: 0; padding: 0;">
    <main class="survey-shell">
        <section class="survey-hero">
            <p style="font-size:0.78rem; font-weight:900; text-transform:uppercase; letter-spacing:0.18em; color:#2563eb; margin-bottom:0.65rem;">
                Survei Lingkungan Kerja
            </p>
            <h1>Survei Evaluasi Kenyamanan Lingkungan Kerja Karyawan</h1>
            <p>
                Jawablah setiap pernyataan sesuai kondisi yang benar-benar Anda alami. Tidak ada jawaban benar atau salah; data ini digunakan untuk membantu sistem memberi evaluasi kerja yang lebih objektif sesuai hak akses yang berlaku.
            </p>

            @php
                $progressPercent = $total_gejala > 0 ? min(round(($progress / $total_gejala) * 100), 100) : 0;
            @endphp

            <div class="progress-panel">
                <div class="progress-row">
                    <span>Proses evaluasi sedang berjalan</span>
                    <span>Jawab sesuai kondisi harian</span>
                </div>
                <div class="progress-track" aria-label="Progres pengisian survei">
                    <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                </div>
            </div>
        </section>

        <form id="workWellnessSurveyForm" action="{{ route('karyawan.deteksi.next') }}" method="POST" onsubmit="return handleSubmit(event)">
            @csrf

            <div class="question-list">
                @foreach ($questions as $index => $q)
                    <section class="question-card">
                        <h2 class="question-text">
                            <span style="color:var(--color-primary); font-weight:900;">{{ $index + 1 }}.</span>
                            {{ $q->nama }}
                        </h2>

                        <div class="likert-grid">
                            @foreach([
                                'Tidak'         => ['Tidak Pernah', '0 hari dalam satu minggu terakhir'],
                                'Sangat Jarang' => ['Sangat Jarang', '1 hari atau lebih jarang'],
                                'Jarang'        => ['Jarang', '1–2 hari dalam satu minggu terakhir'],
                                'Kadang'        => ['Kadang-kadang', '3 hari atau pada kondisi tertentu'],
                                'Sering'        => ['Sering', '4–5 hari dalam satu minggu terakhir'],
                                'Sangat Sering' => ['Sangat Sering / Selalu', 'Hampir setiap hari dan sangat terasa'],
                            ] as $value => $meta)
                                <label class="likert-option">
                                    <input type="radio" name="{{ $q->kode }}" value="{{ $value }}" required>
                                    <span>
                                        <span class="likert-label">{{ $meta[0] }}</span>
                                        <span class="likert-desc">{{ $meta[1] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="survey-actions">
                <p class="neutral-note">
                    Hasil evaluasi digunakan untuk membaca pola kerja secara lebih objektif. Gunakan jawaban yang paling dekat dengan kondisi nyata, bukan jawaban yang terlihat “aman” atau “ideal”. Karena ya, sistem pakar tetap butuh data jujur agar tidak berubah jadi mesin tebak-tebakan.
                </p>

                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <button type="button" class="btn-nav btn-prev" onclick="handleSaveLater()" style="border-color: var(--color-primary); color: var(--color-primary); font-weight: 800;">
                        Simpan Progres
                    </button>
                    <button type="submit" class="btn-nav btn-result">
                        Lihat Ringkasan Evaluasi
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<form id="saveLaterForm" action="{{ route('karyawan.deteksi.save') }}" method="POST" style="display:none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    function handleSaveLater() {
        const sourceForm = document.getElementById('workWellnessSurveyForm');
        const saveForm = document.getElementById('saveLaterForm');

        if (!sourceForm || !saveForm) return;

        sourceForm.querySelectorAll('input[type="radio"]:checked').forEach((input) => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = input.name;
            hidden.value = input.value;
            saveForm.appendChild(hidden);
        });

        if (typeof showLoader === 'function') {
            showLoader('Menyimpan progres survei...');
        }

        saveForm.submit();
    }

    function handleSubmit() {
        if (typeof showLoader === 'function') {
            showLoader('Menganalisis data evaluasi...');
        }

        return true;
    }
</script>
@endpush
