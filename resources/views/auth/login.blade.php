@extends('layouts.auth')

@section('title', 'Masuk – Sanctuary Hub')

@section('content')
    <div class="bg-decoration">
        <div class="shape shape--1"></div>
        <div class="shape shape--2"></div>
        <div class="shape shape--3"></div>
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
        <div class="bg-circle bg-circle--3"></div>
    </div>

    <a href="{{ url('/') }}" class="brand-logo" aria-label="Sanctuary Hub">
        <div class="brand-logo__icon" aria-hidden="true" style="background:transparent;border:none;backdrop-filter:none;overflow:hidden;">
            <img src="{{ asset('assets/img/sanctuary-hub-mark.svg') }}" alt="" style="width:40px;height:40px;display:block;">
        </div>
        <span class="brand-logo__text">Sanctuary<span class="brand-logo__accent">Hub</span></span>
    </a>

    <main class="login-wrapper" role="main">
        <div class="login-card" id="loginCard">
            <div class="login-card__header">
                <div class="login-card__icon" aria-hidden="true" style="background:transparent;display:flex;align-items:center;justify-content:center;border-radius:24px;box-shadow:none;">
                    <img src="{{ asset('assets/img/sanctuary-hub-mark.svg') }}" alt="" style="width:64px;height:64px;display:block;">
                </div>
                <h1 class="login-card__title">Masuk</h1>
                <p class="login-card__subtitle animate-delay-1">
                    Akses check-in kerja, riwayat pribadi, dan notifikasi dukungan.
                </p>
            </div>

            @if ($errors->any())
            <div class="alert alert--error" role="alert" id="alertBox">
                <span>{{ $errors->first() }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()" aria-label="Tutup pesan">&times;</button>
            </div>
            @endif

            <form class="login-form animate-delay-2" id="loginForm" method="POST" action="{{ url('/login') }}" novalidate>
                @csrf

                <div class="form-group animate-delay-3" id="emailGroup">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="nama@perusahaan.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            aria-required="true"
                        />
                    </div>
                </div>

                <div class="form-group" id="passwordGroup">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input form-input--password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                            aria-required="true"
                        />
                        <button
                            type="button"
                            class="input-toggle-pw"
                            id="togglePassword"
                            aria-label="Tampilkan/sembunyikan kata sandi"
                            title="Tampilkan/sembunyikan kata sandi"
                        >
                            <span id="eyeIcon" aria-hidden="true">👁</span>
                        </button>
                    </div>
                </div>

                <div class="form-options animate-delay-3">
                    <label class="checkbox-label" for="remember">
                        <input type="checkbox" id="remember" name="remember" class="checkbox-input" />
                        <span class="checkbox-custom" aria-hidden="true"></span>
                        <span class="checkbox-text">Ingat saya</span>
                    </label>
                    <a href="#" class="form-link">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="btn-login animate-delay-4" id="btnLogin">
                    <span class="btn-login__text">Masuk</span>
                    <span class="btn-login__loader" aria-hidden="true"></span>
                    <span class="btn-login__arrow" aria-hidden="true">→</span>
                </button>
            </form>

            <div class="demo-credentials">
                <button class="demo-credentials__toggle" id="demoToggle" aria-expanded="false" aria-controls="demoPanel">
                    Akun Demo
                    <span class="demo-credentials__chevron" aria-hidden="true">⌄</span>
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
        </div>

        <footer class="login-footer" role="contentinfo">
            <p>&copy; {{ date('Y') }} Sanctuary Hub &mdash; Sistem pakar berbasis Backward Chaining</p>
        </footer>
    </main>
@endsection

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const btnLogin = document.getElementById('btnLogin');
        const emailGroup = document.getElementById('emailGroup');
        const passwordGroup = document.getElementById('passwordGroup');
        const togglePassword = document.getElementById('togglePassword');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            });
        }

        emailInput.addEventListener('input', () => validateField(emailInput, emailGroup));
        emailInput.addEventListener('blur', () => validateField(emailInput, emailGroup));
        passwordInput.addEventListener('input', () => validateField(passwordInput, passwordGroup));
        passwordInput.addEventListener('blur', () => validateField(passwordInput, passwordGroup));

        function validateField(input, group) {
            clearError(group);
            if (input.value.trim() === '') return;

            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    showError(group, 'Format email tidak valid');
                    return false;
                }
            }

            if (input.type === 'password' && input.value.length > 0 && input.value.length < 6) {
                showError(group, 'Kata sandi minimal 6 karakter');
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
                existing.style.cssText = 'color:#ef4444;font-size:.75rem;margin-top:.35rem;font-weight:600;display:flex;align-items:center;gap:4px;animation:fadeIn .2s ease;';
                group.appendChild(existing);
            }
            existing.textContent = message;
        }

        function showSuccess(group) {
            group.classList.remove('form-group--error');
            group.classList.add('form-group--success');
            const existing = group.querySelector('.field-error');
            if (existing) existing.remove();
        }

        function clearError(group) {
            group.classList.remove('form-group--error', 'form-group--success');
            const existing = group.querySelector('.field-error');
            if (existing) existing.remove();
        }

        form.addEventListener('submit', function(e) {
            let isValid = true;
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

            if (passwordInput.value === '') {
                showError(passwordGroup, 'Kata sandi wajib diisi');
                isValid = false;
            } else if (passwordInput.value.length < 6) {
                showError(passwordGroup, 'Kata sandi minimal 6 karakter');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                btnLogin.style.animation = 'shake .4s ease';
                setTimeout(() => btnLogin.style.animation = '', 400);
                return;
            }

            btnLogin.classList.add('btn-login--loading');
            btnLogin.disabled = true;
        });
    })();

    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
        const btn = document.getElementById('btnLogin');
        btn.classList.add('btn-login--loading');
        setTimeout(() => document.getElementById('loginForm').submit(), 300);
    }
</script>

<style>
    .form-group--error .form-input { border-color:#ef4444 !important; box-shadow:0 0 0 3px rgba(239,68,68,.1) !important; }
    .form-group--success .form-input { border-color:#10b981 !important; box-shadow:0 0 0 3px rgba(16,185,129,.1) !important; }
    @keyframes shake { 0%, 100% { transform:translateX(0); } 25% { transform:translateX(-5px); } 75% { transform:translateX(5px); } }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush
