@extends('layouts.app')

@section('title', 'Notifikasi – BurnoutXpert')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0;">Pusat Notifikasi</h1>
    </div>

    @if(count($notifications) === 0)
        <div class="content-card" style="text-align: center; padding: 4rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;">🔔</div>
            <h3 style="color: var(--color-gray-700);">Semua Beres!</h3>
            <p style="color: var(--color-gray-500);">Saat ini Anda tidak memiliki notifikasi baru.</p>
        </div>
    @else
        <div style="display: grid; gap: 1rem;">
            @foreach($notifications as $n)
            <div class="content-card" style="padding: 1.25rem; border-left: 4px solid {{ $n->is_read ? '#e2e8f0' : 'var(--color-primary)' }}; position: relative; transition: 0.3s; {{ $n->is_read ? 'opacity: 0.7;' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: var(--color-gray-800);">{{ $n->title }}</span>
                            @if(!$n->is_read)
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary);"></span>
                            @endif
                        </div>
                        <p style="margin: 0; font-size: 0.9rem; color: var(--color-gray-600); line-height: 1.5;">{{ $n->message }}</p>
                        <div style="margin-top: 0.75rem; font-size: 0.75rem; color: var(--color-gray-400);">
                            {{ $n->created_at->diffForHumans() }}
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        @if(!$n->is_read)
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-icon" title="Tandai Sudah Dibaca">✔️</button>
                        </form>
                        @endif
                        <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon" title="Hapus">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
@endsection
