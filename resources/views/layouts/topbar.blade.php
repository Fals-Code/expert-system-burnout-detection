@php
    $user = Auth::user();
    $role = $user->role;
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
    $display_role = $role === 'karyawan' ? 'KARYAWAN' : strtoupper($role);
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
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <div class="topbar__title-group">
            <h1 class="topbar__title" style="text-transform:none;">{{ $friendlyTitle }}</h1>
            <nav class="topbar__breadcrumb">
                <a href="{{ route($role . '.dashboard') }}" style="color: inherit; text-decoration: none;">Sanctuary Hub</a>
                @if($role === 'karyawan')
                    › <span style="color: var(--color-primary); font-weight: 600;">{{ $friendlyTitle }}</span>
                @else
                    @foreach(request()->segments() as $segment)
                        › <span style="{{ $loop->last ? 'color: var(--color-primary); font-weight: 600;' : 'color: inherit; text-decoration: none;' }} text-transform: capitalize;">{{ str_replace('-', ' ', $segment) }}</span>
                    @endforeach
                @endif
            </nav>
        </div>
    </div>

    <div class="topbar__right">
        <!-- Language Switcher -->
        <div style="position: relative; display: inline-block; margin-right: 0.5rem;" id="langSelector">
            <button onclick="document.getElementById('langDropdown').classList.toggle('active')" style="background: none; border: 1px solid var(--color-gray-200); color: var(--color-gray-700); cursor: pointer; display: flex; align-items: center; gap: 6px; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                <span>🌐 {{ App::getLocale() === 'en' ? 'EN' : 'ID' }}</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="langDropdown" style="position: absolute; top: 110%; right: 0; background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-gray-200); border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: none; flex-direction: column; z-index: 1000; min-width: 120px; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);">
                <a href="{{ url('/locale/id') }}" style="padding: 0.6rem 1rem; color: var(--color-gray-700); text-decoration: none; font-size: 0.85rem; font-weight: 500; display: block; border-bottom: 1px solid var(--color-gray-100); transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">Indonesia</a>
                <a href="{{ url('/locale/en') }}" style="padding: 0.6rem 1rem; color: var(--color-gray-700); text-decoration: none; font-size: 0.85rem; font-weight: 500; display: block; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">English</a>
            </div>
            <style>
                #langDropdown.active { display: flex !important; }
            </style>
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle Dark Mode" style="background: none; border: none; color: var(--color-gray-500); cursor: pointer; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px; transition: 0.2s;">
            <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>

        <div class="topbar__date">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>{{ date('l, d M Y') }}</span>
        </div>

        @if($role === 'karyawan')
            <div style="display:flex; align-items:center; gap:.45rem; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:.45rem .75rem; border-radius:999px; font-size:.78rem; font-weight:900; white-space:nowrap;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                <span>Privasi terjaga</span>
            </div>
        @endif

        <!-- Notifications -->
        <div class="topbar__notif">
            <div class="notif-bell" id="globalBellBtn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                @php
                    $unreadCount = $user->unreadNotifications ? $user->unreadNotifications->count() : 0;
                @endphp
                @if($unreadCount > 0)
                    <div class="notif-badge" id="globalBellBadge">{{ $unreadCount }}</div>
                @else
                    <div class="notif-badge" id="globalBellBadge" style="display: none;">0</div>
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
                            <a href="{{ route('notifications.read_redirect', $notif->id) }}" class="dropdown-item" style="padding: 1rem;">
                                <div class="dropdown-item__body">
                                    <div class="dropdown-item__title" style="font-size: 0.9rem;">
                                        @php
                                            $parsedTopbarMsg = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $notif->message ?? 'Notifikasi Baru');
                                            $parsedTopbarMsg = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedTopbarMsg);
                                        @endphp
                                        {!! $parsedTopbarMsg !!}
                                    </div>
                                    <div class="dropdown-item__text" style="font-size: 0.8rem; color: var(--color-gray-500);">{{ $notif->created_at->diffForHumans() }}</div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div style="padding: 2rem; text-align: center; color: var(--color-gray-400);">Belum ada notifikasi.</div>
                    @endif
                </div>
                <div class="dropdown-footer">
                    <a href="{{ route('notifications') }}">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <div class="topbar__user" id="userMenuBtn" style="cursor: pointer;">
            <div class="topbar__user-info">
                <span class="topbar__user-name">{{ $user->nama }}</span>
                <span class="topbar__user-role">{{ $display_role }}</span>
            </div>
            <div class="topbar__avatar">
                {{ $initials }}
            </div>

            <div class="user-dropdown" id="userMenuDropdown">
                <div class="dropdown-header">
                    <h3>Akun</h3>
                </div>
                <div class="dropdown-list">
                    <a href="{{ route('profile') }}" class="dropdown-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem;">
                        <div class="dropdown-item__icon" style="background: var(--color-primary-50); color: var(--color-primary); flex-shrink: 0; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <div class="dropdown-item__body" style="flex: 1;">
                            <div class="dropdown-item__title" style="margin: 0; font-weight: 600;">Profil & Privasi</div>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="border-top: 1px solid var(--color-gray-50); display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem;">
                        <div class="dropdown-item__icon" style="background: var(--color-error-bg); color: var(--color-error); flex-shrink: 0; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </div>
                        <div class="dropdown-item__body" style="flex: 1;">
                            <div class="dropdown-item__title" style="margin: 0; font-weight: 600; color: var(--color-error);">Keluar</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    .topbar__user {
        position: relative;
    }

    .user-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 280px;
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--color-gray-100);
        display: none;
        overflow: hidden;
        z-index: 9999;
        animation: slideDown 0.25s ease;
    }

    .user-dropdown.active,
    .user-dropdown.show,
    .notif-dropdown.active,
    .notif-dropdown.show {
        display: block !important;
    }

    .notif-dropdown,
    .user-dropdown {
        pointer-events: auto;
    }
</style>

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
    if (currentTheme === 'dark') {
        if(sunIcon && moonIcon) {
            sunIcon.style.display = 'block';
            moonIcon.style.display = 'none';
        }
    }

    if(themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || document.body.getAttribute('data-theme') === 'dark';
            const nextTheme = isDark ? 'light' : 'dark';

            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                document.body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                if(sunIcon && moonIcon) {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                }
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if(sunIcon && moonIcon) {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }

            if (window.activeCharts) {
                window.activeCharts.forEach(chart => {
                    if (chart && chart.updateOptions) {
                        chart.updateOptions({ theme: { mode: nextTheme } });
                    }
                });
            }
        });
    }

    if (bellBtn && bellDropdown) {
        bellBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            bellDropdown.classList.toggle('active');
            bellDropdown.classList.toggle('show', bellDropdown.classList.contains('active'));
            if (userDropdown) {
                userDropdown.classList.remove('active', 'show');
            }
        });
    }

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            userDropdown.classList.toggle('show', userDropdown.classList.contains('active'));
            if (bellDropdown) {
                bellDropdown.classList.remove('active', 'show');
            }
        });
    }

    if (bellDropdown) {
        bellDropdown.addEventListener('click', (e) => e.stopPropagation());
    }

    if (userDropdown) {
        userDropdown.addEventListener('click', (e) => e.stopPropagation());
    }

    window.addEventListener('click', function(e) {
        const selector = document.getElementById('langSelector');
        const dropdown = document.getElementById('langDropdown');
        if (selector && dropdown && !selector.contains(e.target)) {
            dropdown.classList.remove('active');
        }
        if (userDropdown && !e.target.closest('#userMenuBtn')) {
            userDropdown.classList.remove('active', 'show');
        }
        if (bellDropdown && !e.target.closest('#globalBellBtn') && !e.target.closest('#globalBellDropdown')) {
            bellDropdown.classList.remove('active', 'show');
        }
    });
});
</script>
