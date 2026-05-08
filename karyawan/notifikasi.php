<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'karyawan') {
    header('Location: ../index.php');
    exit();
}
$user = $_SESSION['user'];
$nama = $user['nama'];
$active_menu = 'notifikasi';
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $nama), 0, 2)));

// Mock Notifications Data
$notifications = [
    [
        'id' => 1,
        'category' => 'peringatan',
        'title' => 'Peringatan Burnout',
        'message' => 'Tingkat burnout Anda meningkat ke level TINGGI. Segera konsultasikan dengan HRD.',
        'time' => '2025-04-12 09:00:00',
        'is_read' => false,
        'icon' => '🔥',
        'color' => '#DC3545'
    ],
    [
        'id' => 2,
        'category' => 'pengingat',
        'title' => 'Pengingat Deteksi',
        'message' => 'Sudah 30 hari sejak deteksi terakhir Anda. Lakukan deteksi ulang untuk memantau kondisi Anda.',
        'time' => '2025-04-01 08:00:00',
        'is_read' => false,
        'icon' => '📅',
        'color' => '#FFC107'
    ],
    [
        'id' => 3,
        'category' => 'informasi',
        'title' => 'Informasi Sistem',
        'message' => 'Laporan bulanan burnout divisi IT telah tersedia. Klik untuk melihat.',
        'time' => '2025-03-31 07:00:00',
        'is_read' => false,
        'icon' => '📄',
        'color' => '#28A745'
    ],
    [
        'id' => 4,
        'category' => 'informasi',
        'title' => 'Informasi Sistem',
        'message' => 'Profil Anda berhasil diperbarui.',
        'time' => '2025-03-15 14:30:00',
        'is_read' => true,
        'icon' => '✅',
        'color' => '#28A745'
    ],
    [
        'id' => 5,
        'category' => 'informasi',
        'title' => 'Informasi Sistem',
        'message' => 'Selamat datang di BurnoutXpert! Mulai deteksi pertama Anda.',
        'time' => '2025-01-01 08:00:00',
        'is_read' => true,
        'icon' => '👋',
        'color' => '#28A745'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            padding: 0.4rem;
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
        }

        /* ── Navbar ── */
        .topbar { background: #fff; border-bottom: 1px solid var(--color-gray-200); padding: 0.75rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 90; }
        .topbar__title { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
        .topbar__actions { display: flex; align-items: center; gap: 1.5rem; }

        /* Bell Notification */
        .notif-bell { position: relative; cursor: pointer; color: var(--color-gray-400); transition: 0.2s; padding: 0.5rem; border-radius: 50%; }
        .notif-bell:hover { background: var(--color-gray-50); color: var(--color-primary); }
        .notif-badge { position: absolute; top: 2px; right: 2px; background: #DC3545; color: #fff; font-size: 0.65rem; font-weight: 800; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; }

        .notif-dropdown { position: absolute; top: 100%; right: 0; width: 320px; background: #fff; border-radius: 12px; box-shadow: var(--shadow-xl); border: 1px solid var(--color-gray-100); display: none; margin-top: 0.5rem; animation: slideDown 0.2s ease; overflow: hidden; }
        .notif-dropdown.show { display: block; }
        @keyframes slideDown { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }

        .dropdown-header { padding: 1rem; border-bottom: 1px solid var(--color-gray-100); font-weight: 800; font-size: 0.875rem; color: var(--color-primary); display: flex; justify-content: space-between; align-items: center; }
        .dropdown-item { padding: 1rem; border-bottom: 1px solid var(--color-gray-50); display: flex; gap: 0.75rem; transition: 0.2s; text-decoration: none; }
        .dropdown-item:hover { background: var(--color-gray-50); }
        .dropdown-item.unread { background: #F0F7FF; }
        .dropdown-item__icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .dropdown-item__body { flex: 1; }
        .dropdown-item__title { font-size: 0.8rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.2rem; }
        .dropdown-item__text { font-size: 0.75rem; color: var(--color-gray-500); line-height: 1.4; }
        .dropdown-footer { padding: 0.75rem; text-align: center; background: var(--color-gray-50); }
        .dropdown-footer a { font-size: 0.8rem; font-weight: 700; color: var(--color-primary); text-decoration: none; }

        /* ── Content ── */
        .page-content { padding: 2rem; max-width: 900px; margin: 0 auto; width: 100%; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn-mark-read { background: none; border: none; color: var(--color-primary); font-weight: 700; font-size: 0.875rem; cursor: pointer; text-decoration: underline; }

        /* Filters */
        .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem; overflow-x: auto; }
        .filter-tab { padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 700; color: var(--color-gray-500); cursor: pointer; transition: 0.2s; white-space: nowrap; }
        .filter-tab:hover { color: var(--color-primary); background: var(--color-gray-100); }
        .filter-tab.active { color: var(--color-primary); background: var(--color-primary-50); border: 1px solid var(--color-primary-100); }

        /* Notif List */
        .notif-list { display: flex; flex-direction: column; gap: 1rem; }
        .notif-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--color-gray-100); box-shadow: var(--shadow-sm); display: flex; gap: 1.25rem; transition: 0.2s; cursor: pointer; position: relative; }
        .notif-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--color-primary-100); }
        .notif-card.unread { background: #F0F7FF; border-left: 4px solid var(--color-primary); }
        
        .notif-card__icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        .notif-card__body { flex: 1; }
        .notif-card__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
        .notif-card__title { font-size: 1rem; font-weight: 800; color: var(--color-primary); }
        .notif-card__time { font-size: 0.75rem; color: var(--color-gray-400); font-weight: 600; }
        .notif-card__text { font-size: 0.9rem; color: var(--color-gray-600); line-height: 1.5; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .topbar { padding: 0.75rem 1rem; }
            .page-content { padding: 1.25rem; }
            .filter-tabs { padding-bottom: 1rem; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <button class="hamburger" onclick="toggleSidebar()" id="hamburger-btn" aria-label="Toggle menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="topbar__title">Notifikasi</div>
            </div>
            <div class="topbar__actions">
                <!-- Bell Icon -->
                <div class="notif-bell" id="bellBtn">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <div class="notif-badge" id="bellBadge">3</div>
                    
                    <!-- Bell Dropdown -->
                    <div class="notif-dropdown" id="bellDropdown">
                        <div class="dropdown-header">
                            Notifikasi Terbaru
                            <span style="font-size: 0.7rem; color: var(--color-accent);">3 Belum Dibaca</span>
                        </div>
                        <div class="dropdown-list">
                            <?php foreach (array_slice($notifications, 0, 3) as $n): ?>
                            <a href="#" class="dropdown-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                                <div class="dropdown-item__icon" style="background: <?= $n['color'] ?>15; color: <?= $n['color'] ?>"><?= $n['icon'] ?></div>
                                <div class="dropdown-item__body">
                                    <div class="dropdown-item__title"><?= $n['title'] ?></div>
                                    <div class="dropdown-item__text"><?= mb_strimwidth($n['message'], 0, 50, '...') ?></div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="dropdown-footer">
                            <a href="notifikasi.php">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-gray-700);"><?= $nama ?></span>
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;"><?= $initials ?></div>
                </div>
            </div>
        </header>

        <main class="page-content">
            <div class="page-header">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">Pusat Notifikasi</h1>
                <button class="btn-mark-read" onclick="markAllRead()">Tandai Semua Sudah Dibaca</button>
            </div>

            <!-- Filters -->
            <div class="filter-tabs">
                <div class="filter-tab active" onclick="filterNotif('semua', this)">Semua</div>
                <div class="filter-tab" onclick="filterNotif('unread', this)">Belum Dibaca</div>
                <div class="filter-tab" onclick="filterNotif('peringatan', this)">Peringatan</div>
                <div class="filter-tab" onclick="filterNotif('pengingat', this)">Pengingat</div>
                <div class="filter-tab" onclick="filterNotif('informasi', this)">Informasi</div>
            </div>

            <!-- Notif List -->
            <div class="notif-list" id="notifList">
                <?php foreach ($notifications as $n): ?>
                <div class="notif-card <?= !$n['is_read'] ? 'unread' : '' ?>" 
                     data-category="<?= $n['category'] ?>" 
                     data-read="<?= $n['is_read'] ? '1' : '0' ?>"
                     onclick="readNotif(this)">
                    <div class="notif-card__icon" style="background: <?= $n['color'] ?>15; color: <?= $n['color'] ?>">
                        <?= $n['icon'] ?>
                    </div>
                    <div class="notif-card__body">
                        <div class="notif-card__top">
                            <h3 class="notif-card__title"><?= $n['title'] ?></h3>
                            <span class="notif-card__time" data-time="<?= $n['time'] ?>">Memuat...</span>
                        </div>
                        <p class="notif-card__text"><?= $n['message'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle Bell Dropdown
        const bellBtn = document.getElementById('bellBtn');
        const bellDropdown = document.getElementById('bellDropdown');
        
        bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            bellDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            bellDropdown.classList.remove('show');
        });

        // Relative Time Function
        function getRelativeTime(timestamp) {
            const now = new Date();
            const past = new Date(timestamp);
            const diffInSeconds = Math.floor((now - past) / 1000);

            if (diffInSeconds < 60) return 'Baru saja';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} menit lalu`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} jam lalu`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} hari lalu`;
            
            return past.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function updateTimestamps() {
            document.querySelectorAll('.notif-card__time').forEach(el => {
                const time = el.getAttribute('data-time');
                el.innerText = getRelativeTime(time);
            });
        }

        // Filtering
        function filterNotif(category, btn) {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('.notif-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                const isUnread = card.getAttribute('data-read') === '0';

                if (category === 'semua') {
                    card.style.display = 'flex';
                } else if (category === 'unread') {
                    card.style.display = isUnread ? 'flex' : 'none';
                } else {
                    card.style.display = (cardCat === category) ? 'flex' : 'none';
                }
            });
        }

        // Interaction
        function readNotif(card) {
            if (card.classList.contains('unread')) {
                card.classList.remove('unread');
                card.setAttribute('data-read', '1');
                updateBadge();
            }
        }

        function markAllRead() {
            document.querySelectorAll('.notif-card.unread').forEach(card => {
                card.classList.remove('unread');
                card.setAttribute('data-read', '1');
            });
            updateBadge();
        }

        function updateBadge() {
            const unreadCount = document.querySelectorAll('.notif-card[data-read="0"]').length;
            const badge = document.getElementById('bellBadge');
            if (unreadCount > 0) {
                badge.innerText = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        // Init
        updateTimestamps();
    </script>

</body>
</html>
