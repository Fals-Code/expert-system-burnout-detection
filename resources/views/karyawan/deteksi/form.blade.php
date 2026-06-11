@extends('layouts.app')

@section('title', 'Wawancara Backward Chaining')

@section('content')
<main class="bc-shell">
    <header class="bc-header">
        <span>Goal-directed expert interview</span>
        <h1>Wawancara Adaptif Backward Chaining</h1>
        <p>Sistem hanya menanyakan fakta yang masih diperlukan untuk membuktikan atau menolak hipotesis aktif.</p>
    </header>

    @if ($errors->any())
        <section class="bc-alert danger" role="alert">
            <strong>Jawaban belum dapat diproses.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="bc-status">
        <div>
            <small>Goal yang sedang diuji</small>
            <strong>{{ $currentGoalLabel }}</strong>
        </div>
        <div>
            <small>Fakta yang sudah diketahui</small>
            <strong>{{ $answeredCount }}</strong>
        </div>
        <div>
            <small>Premis berikutnya</small>
            <strong>{{ $question->code }}</strong>
        </div>
    </section>

    <form action="{{ route('karyawan.deteksi.next') }}" method="POST" class="bc-card">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        <input type="hidden" name="item_code" value="{{ $question->code }}">

        <p class="bc-kicker">Fakta yang diminta engine</p>
        <h2>{{ $question->prompt_text }}</h2>

        @if ($question->dimension === 'CB')
            <p class="bc-context">Penerima layanan mencakup pelanggan, pasien, siswa, pengguna, warga, atau pihak internal yang menerima hasil pekerjaan.</p>
        @endif

        <div class="bc-options">
            @foreach ($responseOptions as $key => $option)
                <label>
                    <input
                        type="radio"
                        name="answer_key"
                        value="{{ $key }}"
                        required
                        @checked(old('answer_key') === $key)
                    >
                    <span>
                        <strong>{{ $option['label'] }}</strong>
                        <small>
                            {{ in_array($key, config('expert_system.true_answer_keys', []), true) ? 'TRUE' : 'FALSE' }}
                        </small>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="bc-actions">
            <button type="submit">Simpan Fakta dan Lanjutkan</button>
        </div>
    </form>

    <section class="bc-trace">
        <h2>Jejak inferensi saat ini</h2>
        @forelse ($tracePreview as $trace)
            <article>
                <span>{{ $trace->sequence }}</span>
                <p>{{ $trace->message }}</p>
            </article>
        @empty
            <p>Engine sedang menyiapkan jalur pembuktian.</p>
        @endforelse
    </section>

    <section class="bc-alert info">
        <strong>Catatan metodologis</strong>
        <p>Jawaban Selalu dan Sering dikonversi menjadi TRUE. Jawaban lainnya menjadi FALSE. Kesimpulan merupakan inferensi rule-based kustom, bukan skor rata-rata resmi CBI.</p>
    </section>
</main>

<style>
.bc-shell{max-width:920px;margin:0 auto;padding:1rem 0 3rem}.bc-header span,.bc-kicker{color:#2563eb;font-size:.75rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.bc-header h1{margin:.35rem 0 .6rem;color:#0f172a;font-size:2.2rem;font-weight:950;letter-spacing:-.04em}.bc-header p,.bc-context{color:#64748b;line-height:1.7}.bc-status{display:grid;grid-template-columns:2fr 1fr 1fr;gap:.75rem;margin:1.25rem 0}.bc-status div{padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.bc-status small{display:block;color:#64748b;margin-bottom:.35rem}.bc-status strong{color:#0f172a}.bc-card{padding:1.5rem;border:1px solid #dbeafe;border-radius:22px;background:#fff;box-shadow:0 16px 45px rgba(15,23,42,.07)}.bc-card h2{color:#0f172a;line-height:1.55}.bc-options{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.6rem;margin-top:1.25rem}.bc-options label{display:block;border:1px solid #e2e8f0;border-radius:14px;padding:.8rem;cursor:pointer}.bc-options input{margin-right:.35rem}.bc-options span{display:inline-flex;flex-direction:column;gap:.2rem}.bc-options small{color:#64748b}.bc-actions{display:flex;justify-content:flex-end;margin-top:1.25rem}.bc-actions button{border:0;border-radius:999px;background:#0f172a;color:#fff;padding:.9rem 1.3rem;font-weight:900;cursor:pointer}.bc-trace{margin-top:1.25rem;padding:1.2rem;border:1px solid #e2e8f0;border-radius:18px;background:#f8fafc}.bc-trace h2{font-size:1rem}.bc-trace article{display:flex;gap:.75rem;align-items:flex-start}.bc-trace article span{display:inline-flex;min-width:1.8rem;height:1.8rem;align-items:center;justify-content:center;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:900}.bc-trace article p{margin:.2rem 0 .8rem;color:#475569}.bc-alert{padding:1rem 1.2rem;border-radius:16px;margin:1rem 0}.bc-alert p{margin:.35rem 0 0;line-height:1.65}.danger{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}@media(max-width:800px){.bc-status,.bc-options{grid-template-columns:1fr}}
</style>
@endsection
