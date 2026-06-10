@extends('layouts.app')

@section('title', 'Notifikasi – Ruang Check-in')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
        <div>
            <h1 class="page-title" style="margin:0 0 0.35rem;">Notifikasi</h1>
            <p style="margin:0; color:var(--color-gray-500); line-height:1.6; font-size:0.92rem;">Pembaruan singkat dari check-in dan sistem.</p>
        </div>
    </div>

    @if(count($notifications) === 0)
        <div class="content-card" style="text-align:center; padding:3rem;">
            <div style="color:var(--color-gray-300); margin-bottom:1rem;">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            </div>
            <h3 style="color:var(--color-gray-700); margin-bottom:0.35rem;">Belum Ada Notifikasi</h3>
            <p style="color:var(--color-gray-500); margin:0;">Pembaruan baru akan muncul di sini.</p>
        </div>
    @else
        <div style="display:grid; gap:0.75rem;">
            @foreach($notifications as $n)
                @php
                    $parsedMsg = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $n->message);
                    $parsedMsg = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedMsg);
                @endphp

                <div class="content-card" style="padding:1rem; border-left:4px solid {{ $n->is_read ? '#e2e8f0' : ($n->color ?? 'var(--color-primary)') }}; {{ $n->is_read ? 'opacity:.72;' : '' }}">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.35rem; flex-wrap:wrap;">
                                <strong style="color:var(--color-gray-800); font-size:0.95rem;">{{ $n->title }}</strong>
                                @if(!$n->is_read)
                                    <span style="width:8px; height:8px; border-radius:999px; background:{{ $n->color ?? 'var(--color-primary)' }};"></span>
                                @endif
                                <span style="font-size:0.72rem; padding:0.2rem 0.55rem; border-radius:999px; background:{{ $n->color ?? 'var(--color-primary)' }}18; color:{{ $n->color ?? 'var(--color-primary)' }}; text-transform:capitalize; font-weight:800;">{{ $n->category ?? 'informasi' }}</span>
                            </div>
                            <p style="margin:0; font-size:0.88rem; color:var(--color-gray-600); line-height:1.55;">
                                {!! $parsedMsg !!}
                            </p>
                            <div style="margin-top:0.55rem; font-size:0.74rem; color:var(--color-gray-400);">
                                {{ $n->created_at->diffForHumans() }}
                            </div>
                        </div>

                        <div style="display:flex; gap:0.4rem; flex-shrink:0;">
                            @if(!$n->is_read)
                                <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-icon" title="Tandai dibaca" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Hapus" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
