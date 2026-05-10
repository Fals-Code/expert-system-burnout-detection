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

// Ambil notifikasi nyata dari store
$user_id = $user['id'];
$raw_notifications = $_SESSION['bx_store']['user_alerts'][$user_id] ?? [];

// Sort by time descending
usort($raw_notifications, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

$notifications = [];
foreach ($raw_notifications as $rn) {
    $icon = '📄'; $color = '#28A745';
    if ($rn['category'] === 'peringatan') { $icon = '🔥'; $color = '#DC3545'; }
    elseif ($rn['category'] === 'pengingat') { $icon = '📅'; $color = '#FFC107'; }
    
    $notifications[] = [
        'id'       => $rn['id'],
        'category' => $rn['category'],
        'title'    => $rn['title'],
        'message'  => $rn['message'],
        'time'     => $rn['time'],
        'is_read'  => $rn['read'] ?? false,
        'icon'     => $rn['icon'] ?? $icon,
        'color'    => $rn['color'] ?? $color
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Notifikasi – BurnoutXpert</title>
    <?php include '../includes/head.php'; ?>
    
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
