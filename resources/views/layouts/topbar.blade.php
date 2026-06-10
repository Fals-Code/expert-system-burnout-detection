@php
    $user = Auth::user();
    $role = $user->role;
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
    $display_role = $role === 'karyawan' ? 'Karyawan' : strtoupper($role);
    $page_title = $page_title ?? 'Dashboard';

    $routeName = request()->route()?->getName();
    $friendlyTitle = match ($routeName) {
        'karyawan.dashboard' => 'Dashboard Pribadi',
        'karyawan.deteksi.intro', 'karyawan.deteksi' => 'Check-in Kerja',
        'karyawan.hasil' => 'Insight Kondisi Kerja',
        'karyawan.history' => 'Riwayat Saya',
        'profile' => 'Profil & Privasi',
        default => ucwords(str_replace('-', ' ', last(request()->segments()) ?: $page_title)),
    };
@endphp

<header class="topbar">
    <div class="topbar__left">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
            <span style="font-size:1.4rem; line-height:1; font-weight:900;">☰</span>
        </button>
        <div class="topbar__title-group">
            <h1 class="topbar__title" style="text-transform:none;">{{ $friendlyTitle }}</h1>
            <nav class="topbar__breadcrumb">
                <a href="{{ route($role . '.dashboard') }}" style="color:inherit; text-decoration:none;">Ruang Check-in</a>
                @if($role === 'karyawan')
                    › <span style="color:var(--color-primary); font-weight:600;">{{ $friendlyTitle }}</span>
                @else
                    @foreach(request()->segments() as $segment)
                        › <span style="{{ $loop->last ? 'color: var(--color-primary); font-weight: 600;' : 'color: inherit; text-decoration: none;' }} text-transform: capitalize;">{{ str_replace('-', ' ', $segment) }}</span>
                    @endforeach
                @endif
            </nav>
        </div>
    </div>

    <div class="topbar__right">
        <div style="position:relative; display:inline-block; margin-right:.5rem;" id="langSelector">
            <button onclick="document.getElementById('langDropdown').classList.toggle('active')" style="background:none; border:1px solid var(--color-gray-200); color:var(--color-gray-700); cursor:pointer; display:flex; align-items:center; gap:6px; padding:.35rem .75rem; border-radius:8px; font-size:.85rem; font-weight:600;">
                <span>{{ App::getLocale() === 'en' ? 'EN' : 'ID' }}</span>
                <span aria-hidden="true">⌄</span>
            </button>
            <div id="langDropdown" style="position:absolute; top:110%; right:0; background:var(--color-bg-card,#ffffff); border:1px solid var(--color-gray-200); border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,.1); display:none; flex-direction:column; z-index:1000; min-width:120px; overflow:hidden;">
                <a href="{{ url('/locale/id') }}" style="padding:.6rem 1rem; color:var(--color-gray-700); text-decoration:none; font-size:.85rem; font-weight:500; display:block; border-bottom:1px solid var(--color-gray-100);">Indonesia</a>
                <a href="{{ url('/locale/en') }}" style="padding:.6rem 1rem; color:var(--color-gray-700); text-decoration:none; font-size:.85rem; font-weight:500; display:block;">English</a>
            </div>
            <style>#langDropdown.active { display:flex !important; }</style>
        </div>

        <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle Dark Mode" style="background:none; border:none; color:var(--color-gray-500); cursor:pointer; display:flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:10px; transition:.2s;">
            <span class="sun-icon" style="display:none;">☀</span>
            <span class="moon-icon">◐</span>
        </button>

        <div class="topbar__date">
            <span>{{ date('l, d M Y') }}</span>
        </div>

        @if($role === 'karyawan')
            <div style="display:flex; align-items:center; gap:.45rem; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:.45rem .75rem; border-radius:999px; font-size:.78rem; font-weight:900; white-space:nowrap;">
                <span>✓</span>
                <span>Privasi terjaga</span>
            </div>
        @endif

        <div class="topbar__notif">
            <div class="notif-bell" id="globalBellBtn">
                <span style="font-size:1.25rem;">○</span>
                @php $unreadCount = $user->unreadNotifications ? $user->unreadNotifications->count() : 0; @endphp
                @if($unreadCount > 0)
                    <div class="notif-badge" id="globalBellBadge">{{ $unreadCount }}</div>
                @else
                    <div class="notif-badge" id="globalBellBadge" style="display:none;">0</div>
                @endif
            </div>

            <div class="notif-dropdown" id="globalBellDropdown">
                <div class="dropdown-header">
                    <h3>Notifikasi Terbaru</h3>
                    <span id="globalBellHeaderCount">{{ $unreadCount }} Baru</span>
                </div>
                <div class="dropdown-list" id="globalBellList">
                    @if($user->unreadNotifications && $user->unreadNotifications->count() > 0)
                        @foreach($user->unreadNotifications->take(5) as $notif)
                            <a href="{{ route('notifications.read_redirect', $notif->id) }}" class="dropdown-item" style="padding:1rem;">
                                <div class="dropdown-item__body">
                                    <div class="dropdown-item__title" style="font-size:.9rem;">
                                        @php
                                            $parsedTopbarMsg = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $notif->message ?? 'Notifikasi Baru');
                                            $parsedTopbarMsg = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedTopbarMsg);
                                        @endphp
                                        {!! $parsedTopbarMsg !!}
                                    </div>
                                    <div class="dropdown-item__text" style="font-size:.8rem; color:var(--color-gray-500);">{{ $notif->created_at->diffForHumans() }}</div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div style="padding:2rem; text-align:center; color:var(--color-gray-400);">Belum ada notifikasi.</div>
                    @endif
                </div>
                <div class="dropdown-footer">
                    <a href="{{ route('notifications') }}">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <div class="topbar__user" id="userMenuBtn" style="cursor:pointer;">
            <div class="topbar__user-info">
                <span class="topbar__user-name">{{ $user->nama }}</span>
                <span class="topbar__user-role">{{ $display_role }}</span>
            </div>
            <div class="topbar__avatar">{{ $initials }}</div>

            <div class="user-dropdown" id="userMenuDropdown">
                <div class="dropdown-header">
                    <h3>Ruang Akun</h3>
                </div>
                <div class="dropdown-list">
                    <a href="{{ route('profile') }}" class="dropdown-item" style="display:flex; align-items:center; gap:1rem; padding:1rem 1.5rem;">
                        <div class="dropdown-item__icon" style="background:var(--color-primary-50); color:var(--color-primary); flex-shrink:0; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:50%;">👤</div>
                        <div class="dropdown-item__body" style="flex:1;">
                            <div class="dropdown-item__title" style="margin:0; font-weight:600;">Profil & Privasi</div>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display:none;">@csrf</form>
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="border-top:1px solid var(--color-gray-50); display:flex; align-items:center; gap:1rem; padding:1rem 1.5rem;">
                        <div class="dropdown-item__icon" style="background:var(--color-error-bg); color:var(--color-error); flex-shrink:0; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:50%;">↪</div>
                        <div class="dropdown-item__body" style="flex:1;">
                            <div class="dropdown-item__title" style="margin:0; font-weight:600; color:var(--color-error);">Keluar</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bellBtn = document.getElementById('globalBellBtn');
    const bellDropdown = document.getElementById('globalBellDropdown');
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userMenuDropdown');
    const themeBtn = document.getElementById('themeToggleBtn');
    const sunIcon = themeBtn ? themeBtn.querySelector('.sun-icon') : null;
    const moonIcon = themeBtn ? themeBtn.querySelector('.moon-icon') : null;

    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark' && sunIcon && moonIcon) {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || document.body.getAttribute('data-theme') === 'dark';
            const nextTheme = isDark ? 'light' : 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                document.body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                if (sunIcon && moonIcon) { sunIcon.style.display = 'none'; moonIcon.style.display = 'block'; }
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if (sunIcon && moonIcon) { sunIcon.style.display = 'block'; moonIcon.style.display = 'none'; }
            }
            if (window.activeCharts) window.activeCharts.forEach(chart => { if (chart && chart.updateOptions) chart.updateOptions({ theme: { mode: nextTheme } }); });
        });
    }

    if (bellBtn && bellDropdown) {
        bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            bellDropdown.classList.toggle('active');
            if (userDropdown) userDropdown.classList.remove('active');
        });
    }

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            if (bellDropdown) bellDropdown.classList.remove('active');
        });
    }

    window.addEventListener('click', function(e) {
        const selector = document.getElementById('langSelector');
        const dropdown = document.getElementById('langDropdown');
        if (selector && dropdown && !selector.contains(e.target)) dropdown.classList.remove('active');
        if (userDropdown && !e.target.closest('#userMenuBtn')) userDropdown.classList.remove('active');
        if (bellDropdown && !e.target.closest('#globalBellBtn') && !e.target.closest('#globalBellDropdown')) bellDropdown.classList.remove('active');
    });
});
</script>
