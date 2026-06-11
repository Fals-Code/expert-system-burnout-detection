@extends('layouts.app')

@section('title', 'Skrining MBI-GS')

@section('content')
<div class="main-wrapper" style="margin-left:0;padding:0;">
    <main class="mbi-shell">
        <header class="mbi-header">
            <span>Instrumen Berlisensi</span>
            <h1>Maslach Burnout Inventory – General Survey</h1>
            <p>Jawab seluruh 16 item menggunakan skala frekuensi 0–6. Sistem menghitung Exhaustion, Cynicism, dan Professional Efficacy secara terpisah.</p>
        </header>

        @if ($errors->any())
            <section class="mbi-alert mbi-alert-error">
                <strong>Jawaban belum dapat diproses.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (! $instrumentReady)
            <section class="mbi-alert mbi-alert-warning">
                <h2>Instrumen belum siap digunakan</h2>
                <p>Sistem menemukan {{ $items->count() }} dari {{ $expectedItemCount }} slot item, atau masih ada teks item berlisensi yang belum dimuat. Masukkan teks dan scoring key resmi melalui data lisensi organisasi. Teks item tidak disimpan di repositori publik.</p>
            </section>
        @else
            <form action="{{ route('karyawan.deteksi.next') }}" method="POST">
                @csrf

                <div class="mbi-question-list">
                    @foreach ($items as $item)
                        <section class="mbi-question">
                            <div class="mbi-question-title">
                                <span>{{ $loop->iteration }}</span>
                                <p>{{ $item->prompt_text }}</p>
                            </div>

                            <div class="mbi-scale">
                                @foreach ($responseScale as $value => $label)
                                    <label>
                                        <input
                                            type="radio"
                                            name="responses[{{ $item->code }}]"
                                            value="{{ $value }}"
                                            required
                                            @checked((string) old('responses.'.$item->code) === (string) $value)
                                        >
                                        <strong>{{ $value }}</strong>
                                        <small>{{ $label }}</small>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <section class="mbi-safety">
                    <strong>Pemeriksaan keselamatan terpisah</strong>
                    <p>Pertanyaan berikut bukan bagian dari MBI-GS dan tidak memengaruhi skor EX, CY, atau PE. Pertanyaan ini hanya menentukan apakah rekomendasi dukungan profesional perlu ditampilkan.</p>
                    <h2>Dalam beberapa minggu terakhir, seberapa sering Anda merasa sangat putus asa mengenai masa depan karier atau kehidupan kerja Anda?</h2>
                    <div class="mbi-scale">
                        @foreach ($responseScale as $value => $label)
                            <label>
                                <input type="radio" name="safety[G14]" value="{{ $value }}" @checked((string) old('safety.G14') === (string) $value)>
                                <strong>{{ $value }}</strong>
                                <small>{{ $label }}</small>
                            </label>
                        @endforeach
                    </div>
                </section>

                <div class="mbi-actions">
                    <button type="submit">Hitung Profil Dimensional</button>
                </div>
            </form>
        @endif
    </main>
</div>

<style>
.mbi-shell{max-width:1100px;margin:0 auto;padding:1rem 0 3rem}.mbi-header{margin-bottom:1.5rem}.mbi-header>span{color:#2563eb;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:.75rem}.mbi-header h1{margin:.35rem 0 .65rem;font-size:2.2rem;color:#0f172a;font-weight:950;letter-spacing:-.04em}.mbi-header p{margin:0;max-width:820px;color:#64748b;line-height:1.75}.mbi-alert{margin-bottom:1.25rem;padding:1.1rem;border-radius:16px}.mbi-alert h2,.mbi-alert p{margin:.25rem 0}.mbi-alert-error{border:1px solid #fecaca;background:#fef2f2;color:#991b1b}.mbi-alert-warning{border:1px solid #fde68a;background:#fffbeb;color:#92400e}.mbi-question-list{display:flex;flex-direction:column;gap:1rem}.mbi-question{padding:1.25rem;border:1px solid #e2e8f0;border-radius:18px;background:#fff}.mbi-question-title{display:flex;gap:.75rem;align-items:flex-start;margin-bottom:1rem}.mbi-question-title>span{display:inline-flex;min-width:2rem;height:2rem;align-items:center;justify-content:center;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-weight:900}.mbi-question-title p{margin:.2rem 0 0;color:#1e293b;font-weight:800;line-height:1.6}.mbi-scale{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.5rem}.mbi-scale label{display:flex;flex-direction:column;gap:.35rem;padding:.75rem .45rem;border:1px solid #e2e8f0;border-radius:12px;text-align:center;cursor:pointer;background:#fff}.mbi-scale strong{color:#0f172a}.mbi-scale small{font-size:.68rem;color:#64748b;line-height:1.3}.mbi-safety{margin-top:1.25rem;padding:1.25rem;border:1px solid #fed7aa;background:#fff7ed;border-radius:18px;color:#7c2d12}.mbi-safety p{line-height:1.65}.mbi-safety h2{font-size:1rem;line-height:1.6}.mbi-actions{display:flex;justify-content:flex-end;margin-top:1.25rem}.mbi-actions button{border:0;border-radius:999px;background:#0f172a;color:#fff;padding:.9rem 1.4rem;font-weight:900;cursor:pointer}@media(max-width:900px){.mbi-scale{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endsection
