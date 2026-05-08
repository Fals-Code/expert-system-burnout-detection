<?php
/**
 * Global Topbar Template
 * Variables: $nama, $initials, $active_menu (optional)
 */
$current_role = $_SESSION['user']['role'] ?? 'User';
$display_role = strtoupper($current_role);
$page_title = $page_title ?? ucfirst($active_menu ?? 'Dashboard');

// Determine breadcrumb based on folder
$folder = 'Karyawan';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) $folder = 'Admin';
if (strpos($_SERVER['PHP_SELF'], '/hrd/') !== false) $folder = 'HRD';
?>
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
            <h1 class="topbar__title"><?= $page_title ?></h1>
            <nav class="topbar__breadcrumb">
                BurnoutXpert › <?= $folder ?> › <?= $page_title ?>
            </nav>
        </div>
    </div>

    <div class="topbar__right">
        <?php if (isset($topbar_extra)) echo $topbar_extra; ?>

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
            <span><?= date('l, d M Y') ?></span>
        </div>

        <!-- Notifications -->
        <div class="topbar__notif">
            <div class="notif-bell" id="globalBellBtn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <div class="notif-badge">3</div>
            </div>

            <div class="notif-dropdown" id="globalBellDropdown">
                <div class="dropdown-header">
                    <h3>Notifikasi Terbaru</h3>
                    <span>3 Baru</span>
                </div>
                <div class="dropdown-list">
                    <?php 
                    // Fallback mock notifications if not set
                    $notifs = $notifications ?? [
                        ['title' => 'Deteksi Berhasil', 'message' => 'Hasil analisis burnout Anda sudah tersedia.', 'icon' => '✅', 'color' => '#10B981', 'is_read' => false],
                        ['title' => 'Pengingat Mingguan', 'message' => 'Jangan lupa cek kondisi kesehatan mental Anda hari ini.', 'icon' => '📅', 'color' => '#3B82F6', 'is_read' => false],
                        ['title' => 'Sistem Update', 'message' => 'Fitur baru "Rekomendasi Psikolog" kini tersedia.', 'icon' => '🚀', 'color' => '#F4845F', 'is_read' => true],
                    ];
                    foreach (array_slice($notifs, 0, 4) as $n): 
                    ?>
                    <a href="#" class="dropdown-item <?= !($n['is_read'] ?? true) ? 'unread' : '' ?>">
                        <div class="dropdown-item__icon" style="background: <?= $n['color'] ?? '#eee' ?>15; color: <?= $n['color'] ?? '#333' ?>">
                            <?= $n['icon'] ?? '🔔' ?>
                        </div>
                        <div class="dropdown-item__body">
                            <div class="dropdown-item__title"><?= $n['title'] ?></div>
                            <div class="dropdown-item__text"><?= mb_strimwidth($n['message'], 0, 50, '...') ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="dropdown-footer">
                    <a href="<?= (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '#' : 'notifikasi.php' ?>">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <div class="topbar__user">
            <div class="topbar__user-info">
                <span class="topbar__user-name"><?= htmlspecialchars($nama ?? 'User') ?></span>
                <span class="topbar__user-role"><?= $display_role ?></span>
            </div>
            <div class="topbar__avatar">
                <?= htmlspecialchars($initials ?? 'U') ?>
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
        document.body.setAttribute('data-theme', 'dark');
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
    }

    themeBtn.addEventListener('click', () => {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        if (isDark) {
            document.body.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            sunIcon.style.display = 'none';
            moonIcon.style.display = 'block';
        } else {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            sunIcon.style.display = 'block';
            moonIcon.style.display = 'none';
        }
    });

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

    // Add page-fade-in to main-wrapper
    const mainWrapper = document.querySelector('.main-wrapper');
    if (mainWrapper) {
        mainWrapper.classList.add('page-fade-in');
    }
});
</script>
