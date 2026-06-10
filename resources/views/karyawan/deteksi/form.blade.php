@extends('layouts.app')

@section('title', 'Check-in Kerja – Sanctuary Hub')

@section('content')
<style>
    .survey-shell {
        max-width: 900px;
        margin: 0 auto;
        padding: 1rem 0 2.25rem;
    }
    .survey-hero {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 65%, #ecfdf5 100%);
        border: 1px solid #dbeafe;
        border-radius: 22px;
        padding: 1.4rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }
    .survey-hero h1 {
        margin: 0 0 0.35rem;
        color: var(--color-primary);
        font-size: clamp(1.45rem, 3vw, 2rem);
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: -0.03em;
    }
    .survey-hero p {
        margin: 0;
        color: var(--color-gray-500);
        max-width: 680px;
        line-height: 1.65;
        font-size: 0.92rem;
    }
    .progress-panel {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .progress-track {
        flex: 1;
        height: 8px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #10b981);
    }
    .progress-label {
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .question-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .question-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        padding: 1.4rem;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.045);
    }
    .question-text {
        margin: 0 0 1.1rem;
        color: #1e293b;
        font-size: 1.08rem;
        line-height: 1.55;
        font-weight: 900;
        letter-spacing: -0.02em;
    }
    .likert-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .likert-option {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .likert-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .likert-option input {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        margin-top: 0.1rem;
        accent-color: #2563eb;
    }
    .likert-label {
        display: block;
        font-weight: 900;
        color: #1e293b;
        font-size: 0.88rem;
        line-height: 1.35;
    }
    .likert-desc {
        display: block;
        margin-top: 0.15rem;
        color: #94a3b8;
        font-size: 0.74rem;
        line-height: 1.35;
    }
    .survey-actions {
        margin-top: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .neutral-note {
        color: var(--color-gray-500);
        font-size: 0.82rem;
        line-height: 1.55;
        max-width: 520px;
    }
    @media (max-width: 768px) {
        .survey-hero, .question-card { padding: 1.1rem; }
        .progress-panel { align-items: flex-start; flex-direction: column; gap: 0.65rem; }
        .progress-track { width: 100%; flex: unset; }
        .likert-grid { grid-template-columns: 1fr; }
        .survey-actions { align-items: stretch; flex-direction: column; }
        .survey-actions button { width: 100%; justify-content: center; }
    }
</style>

<div class="main-wrapper" style="margin-left: 0; padding: 0;">
    <main class="survey-shell">
        <section class="survey-hero">
            <h1>Check-in Kerja</h1>
            <p>Jawab sesuai kondisi 7 hari terakhir. Tidak ada jawaban benar atau salah.</p>

            @php
                $progressPercent = $total_gejala > 0 ? min(round(($progress / $total_gejala) * 100), 100) : 0;
            @endphp

            <div class="progress-panel">
                <div class="progress-track" aria-label="Progres pengisian check-in">
                    <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                </div>
                <span class="progress-label">Proses berjalan</span>
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
                                'Tidak'         => ['Tidak Pernah', '0 hari'],
                                'Sangat Jarang' => ['Sangat Jarang', '1 hari atau lebih jarang'],
                                'Jarang'        => ['Jarang', '1–2 hari'],
                                'Kadang'        => ['Kadang-kadang', '3 hari'],
                                'Sering'        => ['Sering', '4–5 hari'],
                                'Sangat Sering' => ['Sangat Sering', 'Hampir setiap hari'],
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
                <p class="neutral-note">Jawaban digunakan untuk membaca pola kerja dan kebutuhan dukungan.</p>

                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <button type="button" class="btn-nav btn-prev" onclick="handleSaveLater()" style="border-color: var(--color-primary); color: var(--color-primary); font-weight: 800;">
                        Simpan
                    </button>
                    <button type="submit" class="btn-nav btn-result">
                        Lanjut
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
            showLoader('Menyimpan...');
        }

        saveForm.submit();
    }

    function handleSubmit() {
        if (typeof showLoader === 'function') {
            showLoader('Menyimpan jawaban...');
        }

        return true;
    }
</script>
@endpush
