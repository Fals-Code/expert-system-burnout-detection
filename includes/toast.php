<?php
/**
 * BurnoutXpert - Toast Notification System
 * Include this file in any page that needs modern notifications.
 * Use JS: showToast('Message', 'type') where type is 'success', 'error', 'info', 'warning'
 */
?>
<style>
    .toast-container {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .toast {
        min-width: 300px;
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-left: 5px solid var(--color-primary);
        animation: toastIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        transform: translateX(120%);
    }

    .toast.success { border-left-color: var(--color-success); }
    .toast.error { border-left-color: var(--color-error); }
    .toast.warning { border-left-color: var(--color-warning); }
    .toast.info { border-left-color: var(--color-info); }

    .toast-icon { font-size: 1.25rem; }
    .toast-content { flex: 1; font-size: 0.9rem; font-weight: 600; color: var(--color-gray-800); }
    .toast-close { cursor: pointer; color: var(--color-gray-400); font-size: 1.2rem; }

    @keyframes toastIn { to { transform: translateX(0); } }
    @keyframes toastOut { to { transform: translateX(150%); opacity: 0; } }
</style>

<div class="toast-container" id="toastContainer"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = '✨';
        if (type === 'success') icon = '✅';
        if (type === 'error') icon = '❌';
        if (type === 'warning') icon = '⚠️';
        if (type === 'info') icon = 'ℹ️';

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <div class="toast-content">${message}</div>
            <span class="toast-close" onclick="this.parentElement.remove()">&times;</span>
        `;

        container.appendChild(toast);

        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.5s ease-in forwards';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // Replace native alert (optional, but good for UI consistency)
    // window.alert = (msg) => showToast(msg, 'info');
</script>
