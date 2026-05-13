@extends('layouts.app')

@section('title', 'Pusat Bantuan – BurnoutXpert')

@section('content')
    <h1 class="page-title">Pusat Bantuan & FAQ</h1>

    <div style="max-width: 800px; margin: 0 auto;">
        <div class="content-card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--color-primary), #4f46e5); color: white; padding: 2.5rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👋</div>
            <h2 style="margin: 0; font-size: 1.75rem;">Ada yang bisa kami bantu?</h2>
            <p style="opacity: 0.9; margin-top: 0.5rem;">Temukan jawaban atas pertanyaan umum mengenai penggunaan sistem BurnoutXpert.</p>
        </div>

        @foreach($faqs as $index => $faq)
        <div class="content-card" style="margin-bottom: 1rem; cursor: pointer;" onclick="toggleFaq({{ $index }})">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.05rem; color: var(--color-gray-800);">{{ $faq['q'] }}</h3>
                <div id="icon-{{ $index }}" style="transition: transform 0.3s ease;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>
            <div id="answer-{{ $index }}" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; color: var(--color-gray-600); line-height: 1.6;">
                {{ $faq['a'] }}
            </div>
        </div>
        @endforeach

        <div class="content-card" style="margin-top: 3rem; border-left: 5px solid #10b981;">
            <h3 style="margin: 0 0 0.5rem 0; color: #065f46;">Butuh bantuan lebih lanjut?</h3>
            <p style="color: #047857; font-size: 0.9rem; margin-bottom: 1.5rem;">Jika pertanyaan Anda tidak terjawab di sini, silakan hubungi tim IT atau Departemen HRD melalui email resmi perusahaan.</p>
            <a href="mailto:support@perusahaan.com" class="btn-cta" style="background: #10b981; border: none; text-decoration: none; display: inline-block;">Hubungi Support</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleFaq(index) {
        const answer = document.getElementById('answer-' + index);
        const icon = document.getElementById('icon-' + index);
        
        if (answer.style.display === 'none') {
            answer.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            answer.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
</script>
@endpush
