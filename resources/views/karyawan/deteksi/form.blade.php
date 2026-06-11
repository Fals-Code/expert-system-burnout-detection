@extends('layouts.app')

@section('title', 'Skrining MBI-GS')

@section('content')
<main style="max-width:1000px;margin:0 auto;padding:24px">
    <h1>Maslach Burnout Inventory – General Survey</h1>
    <p>Jawab seluruh 16 item pada skala frekuensi 0 sampai 6. Skor EX, CY, dan PE dihitung terpisah.</p>

    @if ($errors->any())
        <div role="alert" style="padding:16px;background:#fef2f2;border:1px solid #fecaca">
            <strong>Jawaban belum dapat diproses.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (! $instrumentReady)
        <div role="alert" style="padding:16px;background:#fffbeb;border:1px solid #fde68a">
            <strong>Instrumen belum siap digunakan.</strong>
            <p>Diperlukan {{ $expectedItemCount }} item aktif beserta teks resmi dari paket lisensi organisasi.</p>
        </div>
    @else
        <form action="{{ route('karyawan.deteksi.next') }}" method="POST">
            @csrf

            @foreach ($items as $item)
                <fieldset style="margin:18px 0;padding:16px;border:1px solid #e2e8f0">
                    <legend>{{ $loop->iteration }}. {{ $item->prompt_text }}</legend>
                    @foreach ($responseScale as $value => $label)
                        <label style="display:block;margin:8px 0">
                            <input type="radio" name="responses[{{ $item->code }}]" value="{{ $value }}" required @checked((string) old('responses.'.$item->code) === (string) $value)>
                            <strong>{{ $value }}</strong> — {{ $label }}
                        </label>
                    @endforeach
                </fieldset>
            @endforeach

            <fieldset style="margin:22px 0;padding:16px;border:1px solid #fed7aa;background:#fff7ed">
                <legend>Pemeriksaan keselamatan terpisah</legend>
                <p>Respons berikut tidak memengaruhi skor MBI-GS dan hanya digunakan untuk menampilkan rekomendasi dukungan profesional.</p>
                <p><strong>Seberapa sering Anda merasakan keputusasaan berat terkait masa depan kerja?</strong></p>
                @foreach ($responseScale as $value => $label)
                    <label style="display:block;margin:8px 0">
                        <input type="radio" name="safety[G14]" value="{{ $value }}" @checked((string) old('safety.G14') === (string) $value)>
                        <strong>{{ $value }}</strong> — {{ $label }}
                    </label>
                @endforeach
            </fieldset>

            <button type="submit">Hitung Profil Dimensional</button>
        </form>
    @endif
</main>
@endsection
