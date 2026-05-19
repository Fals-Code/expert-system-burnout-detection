@extends('layouts.auth')

@section('title', 'Login – BurnoutXpert')

@section('content')
    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="shape shape--1"></div>
        <div class="shape shape--2"></div>
        <div class="shape shape--3"></div>
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
        <div class="bg-circle bg-circle--3"></div>
    </div>

    <!-- ===== Top-left Brand Logo ===== -->
    <a href="{{ url('/') }}" class="brand-logo" aria-label="BurnoutXpert Home">
        <div class="brand-logo__icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="14" cy="14" r="14" fill="#F4845F"/>
                <path d="M14 6C14 6 8 10 8 15C8 18.314 10.686 21 14 21C17.314 21 20 18.314 20 15C20 10 14 6 14 6Z" fill="white" opacity="0.9"/>
                <path d="M14 11C14 11 11 13.5 11 16C11 17.657 12.343 19 14 19C15.657 19 17 17.657 17 16C17 13.5 14 11 14 11Z" fill="#1E3A5F"/>
            </svg>
        </div>
        <span class="brand-logo__text">Burnout<span class="brand-logo__accent">Xpert</span></span>
    </a>

    <!-- ===== Main Login Card ===== -->
    <main class="login-wrapper" role="main">
        <div class="login-card" id="loginCard">

            <!-- Card Header -->
            <div class="login-card__header">
                <div class="login-card__icon" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="24" fill="#F4845F" opacity="0.12"/>
                        <circle cx="24" cy="24" r="18" fill="#F4845F" opacity="0.18"/>
                        <path d="M24 10C24 10 14 17.5 14 26C14 31.5228 18.4772 36 24 36C29.5228 36 34 31.5228 34 26C34 17.5 24 10 24 10Z" fill="#F4845F"/>
                        <path d="M24 18C24 18 19 22 19 26C19 28.7614 21.2386 31 24 31C26.7614 31 29 28.7614 29 26C29 22 24 18 24 18Z" fill="white" fill-opacity="0.9"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Selamat Datang</h1>
                <p class="login-card__subtitle animate-delay-1">Masuk ke <strong>BurnoutXpert</strong> untuk memulai deteksi burnout karyawan</p>
            </div>

            <!-- Error / Success Alert -->
            @if ($errors->any())
            <div class="alert alert--error" role="alert" id="alertBox">
                <svg class="alert__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="8" cy="8" r="7.5" stroke="currentColor"/>
                    <path d="M8 4.5V8.5" stroke="currentColor" stroke-linecap="round"/>
                    <circle cx="8" cy="11" r="0.75" fill="currentColor"/>
                </svg>
                <span>{{ $errors->first() }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()" aria-label="Tutup pesan">&times;</button>
            </div>
            @endif

            <!-- Login Form -->
            <form class="login-form animate-delay-2" id="loginForm" method="POST" action="{{ url('/login') }}" novalidate>
                @csrf

                <!-- Email Field -->
                <div class="form-group animate-delay-3" id="emailGroup">
                    <label class="form-label" for="email">
                        <svg class="form-label__icon" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="1" y="3" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M1 5L7 8.5L13 5" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        Alamat Email
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="contoh@burnoutxpert.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            aria-required="true"
                        />
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group" id="passwordGroup">
                    <label class="form-label" for="password">
                        <svg class="form-label__icon" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2.5" y="6" width="9" height="6.5" rx="1.2" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M4.5 6V4.5C4.5 3.12 5.62 2 7 2C8.38 2 9.5 3.12 9.5 4.5V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            <circle cx="7" cy="9.25" r="0.9" fill="currentColor"/>
                        </svg>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input form-input--password"
                            placeholder="Masukkan password Anda"
                            autocomplete="current-password"
                            required
                            aria-required="true"
                        />
                        <button
                            type="button"
                            class="input-toggle-pw"
                            id="togglePassword"
                            aria-label="Tampilkan/sembunyikan password"
                            title="Toggle password visibility"
                        >
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1.5 9C1.5 9 4.5 3.75 9 3.75C13.5 3.75 16.5 9 16.5 9C16.5 9 13.5 14.25 9 14.25C4.5 14.25 1.5 9 1.5 9Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="9" r="2.25" stroke="currentColor" stroke-width="1.4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="form-options animate-delay-3">
                    <label class="checkbox-label" for="remember">
                        <input type="checkbox" id="remember" name="remember" class="checkbox-input" />
                        <span class="checkbox-custom" aria-hidden="true"></span>
                        <span class="checkbox-text">Ingat Saya</span>
                    </label>
                    <a href="#" class="form-link">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login animate-delay-4" id="btnLogin">
                    <span class="btn-login__text">Masuk Sekarang</span>
                    <span class="btn-login__loader" aria-hidden="true"></span>
                    <svg class="btn-login__arrow" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3.75 9H14.25" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M9.75 4.5L14.25 9L9.75 13.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

            </form>

            <!-- Demo Credentials Info -->
            <div class="demo-credentials">
                <button class="demo-credentials__toggle" id="demoToggle" aria-expanded="false" aria-controls="demoPanel">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="7" cy="7" r="6.25" stroke="currentColor" stroke-width="1.2"/>
                        <path d="M7 6.25V10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                        <circle cx="7" cy="4.5" r="0.7" fill="currentColor"/>
                    </svg>
                    Lihat Akun Demo
                    <svg class="demo-credentials__chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="demo-credentials__panel" id="demoPanel" aria-hidden="true">
                    <div class="demo-table">
                        <div class="demo-table__header">
                            <span>Role</span>
                            <span>Email</span>
                            <span>Password</span>
                        </div>
                        <div class="demo-table__row demo-table__row--karyawan" onclick="fillDemo('karyawan@burnoutxpert.com','password')">
                            <span class="role-badge role-badge--karyawan">Karyawan</span>
                            <span>karyawan@burnoutxpert.com</span>
                            <span>password</span>
                        </div>
                        <div class="demo-table__row demo-table__row--hrd" onclick="fillDemo('hrd@burnoutxpert.com','password')">
                            <span class="role-badge role-badge--hrd">HRD</span>
                            <span>hrd@burnoutxpert.com</span>
                            <span>password</span>
                        </div>
                        <div class="demo-table__row demo-table__row--admin" onclick="fillDemo('admin@burnoutxpert.com','password')">
                            <span class="role-badge role-badge--admin">Admin</span>
                            <span>admin@burnoutxpert.com</span>
                            <span>password</span>
                        </div>
                    </div>
                    <p class="demo-hint">Klik baris untuk mengisi form otomatis</p>
                </div>
            </div>

        </div><!-- /.login-card -->

        <!-- Footer -->
        <footer class="login-footer" role="contentinfo">
            <p>&copy; {{ date('Y') }} BurnoutXpert &mdash; Sistem Pakar Deteksi Burnout Karyawan</p>
            <p>Dibuat untuk keperluan akademik &mdash; Kecerdasan Buatan D4 TI</p>
        </footer>

    </main><!-- /.login-wrapper -->
@endsection

@push('scripts')
<script>
    // ── Client-side Form Validation ──
    (function() {
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const btnLogin = document.getElementById('btnLogin');
        const emailGroup = document.getElementById('emailGroup');
        const passwordGroup = document.getElementById('passwordGroup');

        // Real-time validation
        emailInput.addEventListener('input', () => validateField(emailInput, emailGroup));
        emailInput.addEventListener('blur', () => validateField(emailInput, emailGroup));
        passwordInput.addEventListener('input', () => validateField(passwordInput, passwordGroup));
        passwordInput.addEventListener('blur', () => validateField(passwordInput, passwordGroup));

        function validateField(input, group) {
            clearError(group);
            
            if (input.value.trim() === '') return; // Don't validate empty on input, only on submit

            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    showError(group, 'Format email tidak valid');
                    return false;
                }
            }

            if (input.type === 'password' && input.value.length > 0 && input.value.length < 6) {
                showError(group, 'Password minimal 6 karakter');
                return false;
            }

            showSuccess(group);
            return true;
        }

        function showError(group, message) {
            group.classList.remove('form-group--success');
            group.classList.add('form-group--error');
            
            let existing = group.querySelector('.field-error');
            if (!existing) {
                existing = document.createElement('div');
                existing.className = 'field-error';
                existing.style.cssText = 'color: #ef4444; font-size: 0.75rem; margin-top: 0.35rem; font-weight: 600; display: flex; align-items: center; gap: 4px; animation: fadeIn 0.2s ease;';
                group.appendChild(existing);
            }
            existing.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' + message;
        }

        function showSuccess(group) {
            group.classList.remove('form-group--error');
            group.classList.add('form-group--success');
            let existing = group.querySelector('.field-error');
            if (existing) existing.remove();
        }

        function clearError(group) {
            group.classList.remove('form-group--error', 'form-group--success');
            let existing = group.querySelector('.field-error');
            if (existing) existing.remove();
        }

        // Form submit validation
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Check empty email
            if (emailInput.value.trim() === '') {
                showError(emailGroup, 'Email wajib diisi');
                isValid = false;
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    showError(emailGroup, 'Format email tidak valid');
                    isValid = false;
                }
            }

            // Check empty password
            if (passwordInput.value === '') {
                showError(passwordGroup, 'Password wajib diisi');
                isValid = false;
            } else if (passwordInput.value.length < 6) {
                showError(passwordGroup, 'Password minimal 6 karakter');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Shake animation on button
                btnLogin.style.animation = 'shake 0.4s ease';
                setTimeout(() => btnLogin.style.animation = '', 400);
                return;
            }

            // Loading state
            btnLogin.classList.add('btn-login--loading');
            btnLogin.disabled = true;
        });
    })();

    // ── Demo Credential Filler ──
    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
        
        const btn = document.getElementById('btnLogin');
        btn.classList.add('btn-login--loading');
        setTimeout(() => {
            document.getElementById('loginForm').submit();
        }, 300);
    }
</script>

<style>
    .form-group--error .form-input { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important; }
    .form-group--success .form-input { border-color: #10b981 !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important; }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush
