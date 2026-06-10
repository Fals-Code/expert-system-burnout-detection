@extends('layouts.app')

@section('title', 'Check-in Kerja – Sanctuary Hub')

@section('content')
<style>
    .survey-shell {
        max-width: 860px;
        margin: 0 auto;
        padding: 0.5rem 0 2rem;
    }
    .survey-hero {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0 0 1.25rem;
        margin-bottom: 1.1rem;
        box-shadow: none;
    }
    .survey-hero h1 {
        margin: 0 0 0.25rem;
        color: #0f172a;
        font-size: clamp(1.7rem, 3vw, 2.25rem);
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: -0.05em;
    }
    .survey-hero p {
        margin: 0;
        color: var(--color-gray-500);
        max-width: 680px;
        line-height: 1.6;
        font-size: 0.92rem;
    }
    .progress-panel {
        margin-top: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .progress-track {
        flex: 1;
        height: 7px;
        border-radius: 999px;
        overflow: hidden;
        background: #e5e7eb;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: #2563eb;
    }
    .progress-label {
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 900;
        white-space: nowrap;
    }
    .question-list {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }
    .survey-shell .question-card {
        min-height: auto !important;
        display: block !important;
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 18px;
        padding: 1.25rem 1.4rem 1.35rem;
        box-shadow: none;
        backdrop-filter: none;
    }
    .question-text {
        margin: 0 0 0.95rem;
        color: #1e293b;
        font-size: 1rem;
        line-height: 1.45;
        font-weight: 900;
        letter-spacing: -0.02em;
        text-align: left;
    }
    .likert-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
    }
    .likert-option {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 52px;
        padding: 0.62rem 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 13px;
        cursor: pointer;
        background: #ffffff;
        transition: background 0.18s ease, border-color 0.18s ease;
    }
    .likert-option:hover {
        border-color: rgba(37, 99, 235, 0.28);
        background: #f8fafc;
        transform: none;
    }
    .likert-option:has(input:checked) {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .likert-option input {
        flex-shrink: 0;
        width: 0.95rem;
        height: 0.95rem;
        margin: 0;
        accent-color: #2563eb;
    }
    .likert-label {
        display: block;
        font-weight: 900;
        color: #1e293b;
        font-size: 0.85rem;
        line-height: 1.2;
    }
    .likert-desc {
        display: block;
        margin-top: 0.12rem;
        color: #94a3b8;
        font-size: 0.7rem;
        line-height: 1.25;
    }
    .survey-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .neutral-note {
        color: var(--color-gray-500);
        font-size: 0.8rem;
        line-height: 1.5;
        max-width: 520px;
    }
    @media (max-width: 768px) {
        .survey-shell { padding: 0.25rem 0 1.5rem; }
        .survey-shell .question-card { padding: 1rem; border-radius: 16px; }
        .progress-panel { align-items: flex-start; flex-direction: column; gap: 0.55rem; }
        .progress-track { width: 100%; flex: unset; }
        .likert-grid { grid-template-columns: 1fr; }
        .likert-option { min-height: 50px; }
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
