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
    <title>Notifikasi – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    <style>
        body { background: var(--color-gray-50); display: flex; min-height: 100vh; }

        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }

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

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 768px) {

            .page-content { padding: 1.25rem; }
            .filter-tabs { padding-bottom: 1rem; }
            .topbar__actions { gap: 0.75rem; }
            .topbar__actions span { display: none; }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar_karyawan.php'; ?>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
                    
    <?php include '../includes/topbar.php'; ?>


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
