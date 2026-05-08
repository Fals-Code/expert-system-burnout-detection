<?php
/**
 * BurnoutXpert - Sidebar HRD
 */
?>

<!-- Sidebar Styles -->
<style>
    /* ── Sidebar ── */
    .sidebar {
        width: 260px;
        min-height: 100vh;
        background: linear-gradient(180deg, #0D1F36 0%, #1E3A5F 60%, #2A5080 100%);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0;
        z-index: 100;
        box-shadow: 4px 0 24px rgba(14,31,54,0.18);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.75rem 1.5rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-brand__icon {
        width: 40px; height: 40px;
        background: rgba(244,132,95,0.18);
        border: 1px solid rgba(244,132,95,0.35);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-brand__icon svg { color: #F4845F; }

    .sidebar-brand__text {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.02em;
    }

    .sidebar-brand__text span { color: #F4845F; }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #28A745 0%, #1a7a33 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
        border: 2px solid rgba(255,255,255,0.2);
    }

    .sidebar-user__info { overflow: hidden; }
    .sidebar-user__name {
        font-size: 0.875rem; font-weight: 700; color: #fff;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-user__role {
        font-size: 0.7rem; font-weight: 600;
        color: #98EE99;
        text-transform: uppercase; letter-spacing: 0.06em;
    }

    .sidebar-nav {
        flex: 1;
        padding: 1rem 0.75rem;
        display: flex; flex-direction: column; gap: 0.25rem;
    }

    .nav-label {
        font-size: 0.65rem; font-weight: 700;
        color: rgba(255,255,255,0.35);
        text-transform: uppercase; letter-spacing: 0.1em;
        padding: 0.75rem 0.75rem 0.4rem;
    }

    .nav-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-radius: 10px;
        color: rgba(255,255,255,0.65);
        font-size: 0.875rem; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
    }

    .nav-item svg { flex-shrink: 0; width: 18px; height: 18px; }

    .nav-item:hover {
        background: rgba(255,255,255,0.08);
        color: #fff;
    }

    .nav-item.active {
        background: linear-gradient(135deg, rgba(244,132,95,0.22) 0%, rgba(244,132,95,0.10) 100%);
        color: #fff;
        border: 1px solid rgba(244,132,95,0.3);
    }

    .nav-item.active::before {
        content: '';
        position: absolute; left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 60%;
        background: #F4845F;
        border-radius: 0 3px 3px 0;
    }

    .nav-item.active svg { color: #F4845F; }

    .sidebar-footer {
        padding: 1rem 0.75rem 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-logout {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-radius: 10px;
        color: rgba(255,255,255,0.55);
        font-size: 0.875rem; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        width: 100%; background: none; border: none; cursor: pointer;
        font-family: inherit;
    }

    .sidebar-logout:hover {
        background: rgba(220,53,69,0.15);
        color: #ff6b6b;
    }

    .sidebar-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 90;
    }

    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .sidebar-overlay.open { display: block; }
    }
</style>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3 1.07.56 2 1.25 2 3a2.5 2.5 0 0 1-2.5 2.5z"></path>
                <path d="M15 16.5c0-1-1-2-1-3 2 1.5 3 3 3 5a5 5 0 0 1-10 0c0-2 1-4 3-6a8 8 0 0 1 5 4z"></path>
            </svg>
        </div>
        <span class="sidebar-brand__text">Burnout<span>Xpert</span></span>
        <button class="hamburger" onclick="toggleSidebar()" style="margin-left: auto; color: #fff; background: none; border: none; cursor: pointer; display: none;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <style>
        @media (max-width: 768px) {
            .sidebar-brand .hamburger { display: block !important; }
        }
    </style>

    <div class="sidebar-user">
        <div class="avatar"><?= htmlspecialchars($initials ?? 'H') ?></div>
        <div class="sidebar-user__info">
            <div class="sidebar-user__name"><?= htmlspecialchars($nama ?? 'HRD Manager') ?></div>
            <div class="sidebar-user__role">HRD Manager</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu HRD</div>

        <a href="dashboard.php" class="nav-item <?= $active_menu === 'dashboard' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <a href="karyawan.php" class="nav-item <?= $active_menu === 'karyawan' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Data Karyawan
        </a>

        <a href="laporan.php" class="nav-item <?= $active_menu === 'laporan' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Laporan Burnout
        </a>

        <a href="notifikasi.php" class="nav-item <?= $active_menu === 'notifikasi' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Notifikasi
        </a>

        <a href="profil.php" class="nav-item <?= $active_menu === 'profil' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Profil
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="sidebar-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Keluar
        </a>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open');
    }
</script>
