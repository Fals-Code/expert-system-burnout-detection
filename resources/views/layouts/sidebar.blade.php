@php
    $user = Auth::user();
    $role = $user->role;
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
@endphp

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand__icon" style="font-weight:900; font-size:0.78rem;">RC</div>
        <span class="sidebar-brand__text">Ruang<span>Check-in</span></span>
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
                <span style="width:22px; text-align:center; font-weight:900;">D</span>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('karyawan.deteksi.intro') }}" class="nav-item {{ request()->routeIs('karyawan.deteksi') || request()->routeIs('karyawan.deteksi.intro') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">C</span>
                {{ __('Check-in Kerja') }}
            </a>
            <a href="{{ route('karyawan.history') }}" class="nav-item {{ request()->routeIs('karyawan.history') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">R</span>
                {{ __('Riwayat Saya') }}
            </a>
            <a href="{{ route('help') }}" class="nav-item {{ request()->routeIs('help') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">B</span>
                {{ __('Dukungan') }}
            </a>
        @endif

        @if($role === 'hrd')
            <a href="{{ route('hrd.dashboard') }}" class="nav-item {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">D</span>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('hrd.reports') }}" class="nav-item {{ request()->routeIs('hrd.reports') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">L</span>
                {{ __('Laporan Agregat') }}
            </a>
            <a href="{{ route('hrd.employees') }}" class="nav-item {{ request()->routeIs('hrd.employees') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">K</span>
                {{ __('Kondisi Kerja') }}
            </a>
        @endif

        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">D</span>
                {{ __('Dashboard Organisasi') }}
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">U</span>
                {{ __('Kelola Pengguna') }}
            </a>
            <a href="{{ route('admin.knowledge') }}" class="nav-item {{ request()->routeIs('admin.knowledge') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">B</span>
                {{ __('Basis Pengetahuan') }}
            </a>
            <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                <span style="width:22px; text-align:center; font-weight:900;">A</span>
                {{ __('Aktivitas Sistem') }}
            </a>
        @endif

        <a href="{{ route('notifications') }}" class="nav-item {{ request()->routeIs('notifications') ? 'active' : '' }}">
            <span style="width:22px; text-align:center; font-weight:900;">N</span>
            {{ __('Notifikasi') }}
        </a>

        <div class="nav-label">{{ __('Akun') }}</div>

        <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
            <span style="width:22px; text-align:center; font-weight:900;">P</span>
            {{ __('Profil & Privasi') }}
        </a>
    </nav>

    @if($role === 'karyawan')
        <div class="sidebar-footer" style="padding:1.25rem;">
            <div style="border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; border-radius:16px; padding:0.9rem; font-size:0.78rem; line-height:1.55; font-weight:700;">
                <strong style="display:block; margin-bottom:0.25rem;">Privasi Terjaga</strong>
                Check-in digunakan untuk dukungan kerja, bukan ranking performa individu.
            </div>
        </div>
    @else
        <div class="sidebar-footer" style="padding:1.5rem;"></div>
    @endif
</aside>
