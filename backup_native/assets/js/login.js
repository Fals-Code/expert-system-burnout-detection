/**
 * BurnoutXpert – Login Page JavaScript
 * Handles: client-side validation, password toggle,
 *          demo autofill, loading state, accordion panel
 */

(function () {
    'use strict';

    // ── Element References ───────────────────────────────────
    const form          = document.getElementById('loginForm');
    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError    = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const emailGroup    = document.getElementById('emailGroup');
    const passwordGroup = document.getElementById('passwordGroup');
    const togglePw      = document.getElementById('togglePassword');
    const eyeIcon       = document.getElementById('eyeIcon');
    const btnLogin      = document.getElementById('btnLogin');
    const demoToggle    = document.getElementById('demoToggle');
    const demoPanel     = document.getElementById('demoPanel');

    // ── Helpers ──────────────────────────────────────────────
    function setError(input, errorEl, group, message) {
        input.classList.add('is-invalid');
        errorEl.textContent = message;
    }

    function clearError(input, errorEl) {
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ── Real-time Validation ─────────────────────────────────
    emailInput.addEventListener('input', function () {
        if (!this.value.trim()) {
            setError(this, emailError, emailGroup, 'Email tidak boleh kosong.');
        } else if (!isValidEmail(this.value.trim())) {
            setError(this, emailError, emailGroup, 'Format email tidak valid.');
        } else {
            clearError(this, emailError);
        }
    });

    emailInput.addEventListener('blur', function () {
        if (!this.value.trim()) {
            setError(this, emailError, emailGroup, 'Email tidak boleh kosong.');
        } else if (!isValidEmail(this.value.trim())) {
            setError(this, emailError, emailGroup, 'Format email tidak valid.');
        }
    });

    passwordInput.addEventListener('input', function () {
        if (!this.value) {
            setError(this, passwordError, passwordGroup, 'Password tidak boleh kosong.');
        } else if (this.value.length < 6) {
            setError(this, passwordError, passwordGroup, 'Password minimal 6 karakter.');
        } else {
            clearError(this, passwordError);
        }
    });

    passwordInput.addEventListener('blur', function () {
        if (!this.value) {
            setError(this, passwordError, passwordGroup, 'Password tidak boleh kosong.');
        }
    });

    // ── Form Submit Validation ───────────────────────────────
    form.addEventListener('submit', function (e) {
        let hasError = false;

        // Validate email
        if (!emailInput.value.trim()) {
            setError(emailInput, emailError, emailGroup, 'Email tidak boleh kosong.');
            hasError = true;
        } else if (!isValidEmail(emailInput.value.trim())) {
            setError(emailInput, emailError, emailGroup, 'Format email tidak valid.');
            hasError = true;
        }

        // Validate password
        if (!passwordInput.value) {
            setError(passwordInput, passwordError, passwordGroup, 'Password tidak boleh kosong.');
            hasError = true;
        } else if (passwordInput.value.length < 6) {
            setError(passwordInput, passwordError, passwordGroup, 'Password minimal 6 karakter.');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            // Shake animation on the card
            const card = document.getElementById('loginCard');
            card.style.animation = 'none';
            void card.offsetWidth; // force reflow
            card.style.animation = 'shakeCard 0.4s ease';
            return;
        }

        // Show loading state
        btnLogin.classList.add('is-loading');
        btnLogin.disabled = true;
    });

    // Shake animation keyframes injected dynamically
    const shakeStyle = document.createElement('style');
    shakeStyle.textContent = `
        @keyframes shakeCard {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-8px); }
            40%      { transform: translateX(8px); }
            60%      { transform: translateX(-6px); }
            80%      { transform: translateX(6px); }
        }
    `;
    document.head.appendChild(shakeStyle);

    // ── Password Toggle ──────────────────────────────────────
    if (togglePw) {
        togglePw.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            togglePw.classList.toggle('is-active', isHidden);
            togglePw.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');

            // Swap icon
            eyeIcon.innerHTML = isHidden
                ? /* eye-off */`
                    <path d="M1.5 1.5L16.5 16.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    <path d="M7.37 5.13A6.5 6.5 0 0 1 9 4.87c4.5 0 7.5 4.13 7.5 4.13s-.96 1.6-2.65 2.9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    <path d="M12.45 12.45A6.45 6.45 0 0 1 9 13.13C4.5 13.13 1.5 9 1.5 9s.96-1.6 2.65-2.9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    <path d="M6.7 6.7A2.25 2.25 0 0 0 9 11.25a2.25 2.25 0 0 0 2.3-1.95" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>`
                : /* eye */`
                    <path d="M1.5 9C1.5 9 4.5 3.75 9 3.75C13.5 3.75 16.5 9 16.5 9C16.5 9 13.5 14.25 9 14.25C4.5 14.25 1.5 9 1.5 9Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="9" cy="9" r="2.25" stroke="currentColor" stroke-width="1.4"/>`;
        });
    }

    // ── Demo Credentials Accordion ───────────────────────────
    if (demoToggle && demoPanel) {
        demoToggle.addEventListener('click', function () {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!isExpanded));
            demoPanel.setAttribute('aria-hidden', String(isExpanded));
            demoPanel.classList.toggle('is-open', !isExpanded);
        });
    }

    // ── Demo Auto-fill ───────────────────────────────────────
    window.fillDemo = function (email, password) {
        emailInput.value    = email;
        passwordInput.value = password;
        clearError(emailInput, emailError);
        clearError(passwordInput, passwordError);

        // Visual feedback
        [emailInput, passwordInput].forEach(function (el) {
            el.style.transition = 'background 0.3s ease';
            el.style.background = '#F0FFF4';
            setTimeout(function () { el.style.background = ''; }, 600);
        });

        // Close demo panel
        demoToggle.setAttribute('aria-expanded', 'false');
        demoPanel.setAttribute('aria-hidden', 'true');
        demoPanel.classList.remove('is-open');

        // Focus the button
        btnLogin.focus();
    };

    // ── Auto-dismiss PHP error alert after 6s ────────────────
    const alertBox = document.getElementById('alertBox');
    if (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alertBox.style.opacity    = '0';
            alertBox.style.transform  = 'translateY(-8px)';
            setTimeout(function () { alertBox.remove(); }, 400);
        }, 6000);
    }

})();
