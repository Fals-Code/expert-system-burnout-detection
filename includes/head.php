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
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
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
