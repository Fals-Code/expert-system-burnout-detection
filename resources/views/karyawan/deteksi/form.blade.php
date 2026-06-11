@extends('layouts.app')

@section('title', 'Skrining Copenhagen Burnout Inventory')

@section('content')
<main class="cbi-shell">
    <header class="cbi-header">
        <span>Instrumen akses terbuka</span>
        <h1>Copenhagen Burnout Inventory (CBI)</h1>
        <p>Jawab seluruh 19 item. Setiap jawaban dikonversi menjadi nilai 0, 25, 50, 75, atau 100 dan dihitung terpisah untuk tiga dimensi.</p>
    </header>

    @if ($errors->any())
        <section class="cbi-alert cbi-alert-danger" role="alert">
            <strong>Jawaban belum dapat diproses.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (! $instrumentReady)
        <section class="cbi-alert cbi-alert-warning" role="alert">
            <strong>Instrumen belum siap digunakan.</strong>
            <p>Sistem memerlukan tepat {{ $expectedItemCount }} item aktif. Jalankan migration dan CbiInstrumentSeeder.</p>
        </section>
    @else
        <form action="{{ route('karyawan.deteksi.next') }}" method="POST">
            @csrf

            @foreach ($items->groupBy('dimension') as $dimension => $dimensionItems)
                <section class="cbi-dimension">
                    <h2>
                        @switch($dimension)
                            @case('PB') Personal Burnout @break
                            @case('WB') Work-related Burnout @break
                            @default Client-related Burnout
                        @endswitch
                    </h2>

                    @if ($dimension === 'CB')
                        <p class="cbi-note">“Penerima layanan” mencakup pelanggan, pasien, siswa, pengguna, warga, atau pihak internal yang menerima hasil pekerjaan Anda.</p>
                    @endif

                    @foreach ($dimensionItems as $item)
                        <fieldset class="cbi-question">
                            <legend><span>{{ $item->position }}.</span> {{ $item->prompt_text }}</legend>

                            <div class="cbi-options">
                                @foreach ($responseOptions as $key => $option)
                                    <label>
                                        <input type="radio" name="responses[{{ $item->code }}]" value="{{ $key }}" required @checked(old('responses.'.$item->code) === $key)>
                                        <strong>{{ $option['label'] }}</strong>
                                        <small>{{ $option['score'] }}</small>
                                    </label>
                                @endforeach
                            </div>

                            @if ($item->is_reverse)
                                <small class="cbi-reverse-note">Item positif. Pembalikan skor dilakukan otomatis oleh sistem.</small>
                            @endif
                        </fieldset>
                    @endforeach
                </section>
            @endforeach

            <section class="cbi-alert cbi-alert-info">
                <strong>Catatan bahasa</strong>
                <p>{{ $translationNote }}</p>
            </section>

            <div class="cbi-actions">
                <button type="submit">Hitung Profil CBI</button>
            </div>
        </form>
    @endif
</main>

<style>
.cbi-shell{max-width:1120px;margin:0 auto;padding:1rem 0 3rem}.cbi-header{margin-bottom:1.5rem}.cbi-header>span{font-size:.75rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#2563eb}.cbi-header h1{margin:.35rem 0 .6rem;color:#0f172a;font-size:2.2rem;font-weight:950;letter-spacing:-.04em}.cbi-header p,.cbi-note{color:#64748b;line-height:1.7}.cbi-alert{padding:1rem 1.2rem;border-radius:16px;margin:1rem 0}.cbi-alert p{margin:.35rem 0 0;line-height:1.6}.cbi-alert-danger{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}.cbi-alert-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e}.cbi-alert-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}.cbi-dimension{margin:1.5rem 0}.cbi-dimension h2{color:#0f172a}.cbi-question{padding:1.15rem;margin:.9rem 0;border:1px solid #e2e8f0;border-radius:16px;background:#fff}.cbi-question legend{padding:0 .5rem;color:#1e293b;font-weight:800;line-height:1.55}.cbi-question legend span{color:#2563eb}.cbi-options{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.55rem;margin-top:.85rem}.cbi-options label{display:flex;align-items:center;gap:.45rem;padding:.7rem;border:1px solid #e2e8f0;border-radius:12px;cursor:pointer}.cbi-options small{margin-left:auto;color:#64748b}.cbi-reverse-note{display:block;margin-top:.65rem;color:#64748b}.cbi-actions{display:flex;justify-content:flex-end;margin-top:1.25rem}.cbi-actions button{border:0;border-radius:999px;padding:.9rem 1.4rem;background:#0f172a;color:#fff;font-weight:900;cursor:pointer}@media(max-width:850px){.cbi-options{grid-template-columns:1fr}}
</style>
@endsection
