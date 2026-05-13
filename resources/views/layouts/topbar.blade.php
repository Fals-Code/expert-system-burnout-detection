@php
    $user = Auth::user();
    $role = $user->role;
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user->nama), 0, 2)));
    $display_role = strtoupper($role);
    $page_title = $page_title ?? 'Dashboard';
    $folder = ucfirst($role);
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
            <h1 class="topbar__title">{{ $page_title }}</h1>
            <nav class="topbar__breadcrumb">
                BurnoutXpert › {{ $folder }} › {{ $page_title }}
            </nav>
        </div>
    </div>

    <div class="topbar__right">
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

        <!-- Notifications -->
        <div class="topbar__notif">
            <div class="notif-bell" id="globalBellBtn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <div class="notif-badge" style="display: none;">0</div>
            </div>

            <div class="notif-dropdown" id="globalBellDropdown">
                <div class="dropdown-header">
                    <h3>Notifikasi Terbaru</h3>
                    <span>0 Baru</span>
                </div>
                <div class="dropdown-list">
                    <div style="padding: 2rem; text-align: center; color: var(--color-gray-400);">Belum ada notifikasi.</div>
                </div>
                <div class="dropdown-footer">
                    <a href="#">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <div class="topbar__user">
            <div class="topbar__user-info">
                <span class="topbar__user-name">{{ $user->nama }}</span>
                <span class="topbar__user-role">{{ $display_role }}</span>
            </div>
            <div class="topbar__avatar">
                {{ $initials }}
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bellBtn = document.getElementById('globalBellBtn');
    const bellDropdown = document.getElementById('globalBellDropdown');
    const themeBtn = document.getElementById('themeToggleBtn');
    const sunIcon = themeBtn.querySelector('.sun-icon');
    const moonIcon = themeBtn.querySelector('.moon-icon');

    // Theme Logic
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
        });
    }

    if (bellBtn && bellDropdown) {
        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            bellDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!bellDropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                bellDropdown.classList.remove('show');
            }
        });
    }
});
</script>
