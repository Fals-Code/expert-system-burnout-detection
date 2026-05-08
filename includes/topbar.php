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
