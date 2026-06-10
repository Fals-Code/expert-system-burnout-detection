@extends('layouts.app')

@section('title', 'Dukungan – Sanctuary Hub')

@section('content')
<div class="support-page">
    <header class="support-header">
        <p class="support-kicker">Dukungan</p>
        <h1>Bantuan tanpa ribet</h1>
        <p>Panduan singkat untuk memahami check-in, riwayat, dan cara meminta bantuan bila diperlukan.</p>
    </header>

    <section class="support-links" aria-label="Aksi dukungan cepat">
        <a href="{{ route('karyawan.deteksi.intro') }}">Mulai check-in</a>
        <a href="{{ route('karyawan.history') }}">Lihat riwayat</a>
        <a href="mailto:support@perusahaan.com">Hubungi support</a>
    </section>

    <section class="support-section">
        <h2>Pertanyaan Umum</h2>
        <div class="faq-list">
            @foreach($faqs as $index => $faq)
                <details class="faq-item" {{ $index === 0 ? 'open' : '' }}>
                    <summary>
                        <span>{{ $faq['q'] }}</span>
                        <b>⌄</b>
                    </summary>
                    <p>{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="support-section support-contact">
        <h2>Butuh bantuan lebih lanjut?</h2>
        <p>Hubungi tim support jika ada kendala login, data tidak muncul, atau perlu bantuan membaca ringkasan check-in.</p>
        <a href="mailto:support@perusahaan.com">support@perusahaan.com</a>
    </section>
</div>
@endsection

<style>
    .support-page { max-width: 840px; margin: 0 auto; }
    .support-header { padding: .75rem 0 1.5rem; border-bottom: 1px solid rgba(148,163,184,.18); }
    .support-kicker { margin:0 0 .6rem; color:#2563eb; font-size:.72rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    .support-header h1 { margin:0; color:#0f172a; font-size:clamp(2rem,4vw,3.2rem); line-height:1.05; letter-spacing:-.06em; }
    .support-header p { margin:.85rem 0 0; color:#64748b; line-height:1.75; max-width:640px; }
    .support-links { display:flex; gap:.75rem; flex-wrap:wrap; padding:1.25rem 0; border-bottom:1px solid rgba(148,163,184,.18); }
    .support-links a, .support-contact a { color:#2563eb; font-weight:900; text-decoration:none; border-radius:999px; padding:.7rem 1rem; background:#eff6ff; }
    .support-section { padding:1.75rem 0; border-bottom:1px solid rgba(148,163,184,.18); }
    .support-section h2 { margin:0 0 .9rem; color:#0f172a; font-size:1.25rem; font-weight:950; letter-spacing:-.03em; }
    .faq-list { display:flex; flex-direction:column; }
    .faq-item { border-bottom:1px solid rgba(148,163,184,.18); }
    .faq-item summary { cursor:pointer; list-style:none; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 0; }
    .faq-item summary::-webkit-details-marker { display:none; }
    .faq-item summary span { color:#0f172a; font-weight:950; }
    .faq-item summary b { color:#94a3b8; transition:transform .2s ease; }
    .faq-item[open] summary b { transform:rotate(180deg); }
    .faq-item p { margin:0; padding:0 0 1rem; color:#64748b; line-height:1.75; }
    .support-contact p { margin:0 0 1.25rem; color:#64748b; line-height:1.75; }
</style>
@endsection