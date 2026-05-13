<?php
/**
 * Global Head Template - BurnoutXpert
 * This file centralizes fonts, metadata, and global styles.
 */
$base_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/hrd/') !== false || strpos($_SERVER['PHP_SELF'], '/karyawan/') !== false) ? '../' : '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/dashboard.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/table.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/wizard.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/empty-state.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/report.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/profile.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/notifikasi.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/detail-karyawan.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/hasil.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/bantuan.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/admin-knowledge.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/kelola-pengguna.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/pengaturan.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/log-aktivitas.css">
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- Lottie Web Player -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<!-- Intro.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Simple-DataTables -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/style.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/umd/simple-datatables.js"></script>

<?php include $base_path . 'includes/favicon.php'; ?>
<script>
    // Immediate Theme Application (Prevent FOUC)
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body && document.body.setAttribute('data-theme', 'dark');
        }
    })();
</script>

<script>
    // Global Sidebar Toggle for Mobile
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    }

    // Modal Helpers
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 10);
        }
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => { modal.style.display = 'none'; }, 300); // match transition time
        }
    }

    // Page Transition Interceptor
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('a[href]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = this.getAttribute('target');
                
                // Skip if it's external, a hash link, javascript, or open in new tab
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank') {
                    return;
                }

                // Skip if it is a download link or has default prevented
                if (this.hasAttribute('download') || e.defaultPrevented) return;
                
                // Only intercept internal navigation
                const isInternal = href.startsWith('/') || href.startsWith('./') || href.startsWith('../') || !href.includes('://');
                
                if (isInternal) {
                    e.preventDefault();
                    const mainWrapper = document.querySelector('.main-wrapper');
                    if (mainWrapper) {
                        mainWrapper.classList.remove('page-fade-in');
                        mainWrapper.classList.add('page-fade-out');
                    }
                    setTimeout(() => {
                        window.location.href = href;
                    }, 250);
                }
            });
        });

        // Ensure browser back/forward buttons work correctly by handling pageshow event
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const mainWrapper = document.querySelector('.main-wrapper');
                if (mainWrapper) {
                    mainWrapper.classList.remove('page-fade-out');
                    mainWrapper.classList.add('page-fade-in');
                }
            }
        });
    });
</script>

<style>
    /* Force Poppins for all elements */
    * { font-family: 'Poppins', sans-serif !important; }
    
    /* Hamburger Button (Hidden by default, shown via style.css media query) */
    .hamburger {
        display: none;
        background: none;
        border: none;
        color: var(--color-primary);
        cursor: pointer;
        padding: 5px;
    }
</style>
