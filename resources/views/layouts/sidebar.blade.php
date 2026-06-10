@php
    $user = Auth::user();
    $role = $user->role;
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
@endphp

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
        </div>
        <span class="sidebar-brand__text">Sanctuary<span>Hub</span></span>
    </div>

    <div class="sidebar-user">
        <div class="avatar">{{ $initials }}</div>
        <div class="sidebar-user__info">
            <div class="sidebar-user__name">{{ $user->nama }}</div>
            <div class="sidebar-user__role">{{ strtoupper($role) }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">{{ __('Menu Utama') }}</div>

        @if($role === 'karyawan')
            <a href="{{ route('karyawan.dashboard') }}" class="nav-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('karyawan.deteksi.intro') }}" class="nav-item {{ request()->routeIs('karyawan.deteksi') || request()->routeIs('karyawan.deteksi.intro') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                {{ __('Check-in Kerja') }}
            </a>
            <a href="{{ route('karyawan.history') }}" class="nav-item {{ request()->routeIs('karyawan.history') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                {{ __('Riwayat Saya') }}
            </a>
            <a href="{{ route('help') }}" class="nav-item {{ request()->routeIs('help') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.82 1c0 2-3 2-3 4"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                {{ __('Dukungan') }}
            </a>
        @endif

        @if($role === 'hrd')
            <a href="{{ route('hrd.dashboard') }}" class="nav-item {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('hrd.reports') }}" class="nav-item {{ request()->routeIs('hrd.reports') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                {{ __('Laporan Agregat') }}
            </a>
            <a href="{{ route('hrd.employees') }}" class="nav-item {{ request()->routeIs('hrd.employees') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ __('Kondisi Kerja') }}
            </a>
        @endif

        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                {{ __('Dashboard Organisasi') }}
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ __('Kelola Pengguna') }}
            </a>
            <a href="{{ route('admin.knowledge') }}" class="nav-item {{ request()->routeIs('admin.knowledge') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h9z"/></svg>
                {{ __('Basis Pengetahuan') }}
            </a>
            <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                {{ __('Aktivitas Sistem') }}
            </a>
        @endif

        <a href="{{ route('notifications') }}" class="nav-item {{ request()->routeIs('notifications') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            {{ __('Notifikasi') }}
        </a>

        <div class="nav-label">{{ __('Akun') }}</div>

        <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ __('Profil & Privasi') }}
        </a>
    </nav>

    @if($role === 'karyawan')
        <div class="sidebar-footer" style="padding: 1.25rem;">
            <div style="border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; border-radius:16px; padding:0.9rem; font-size:0.78rem; line-height:1.55; font-weight:700;">
                <strong style="display:block; margin-bottom:0.25rem;">Privasi Terjaga</strong>
                Check-in digunakan untuk dukungan kerja, bukan ranking performa individu.
            </div>
        </div>
    @else
        <div class="sidebar-footer" style="padding: 1.5rem;"></div>
    @endif
</aside>
