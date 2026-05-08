<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header('Location: admin/dashboard.php');
    elseif ($role === 'hrd') header('Location: hrd/dashboard.php');
    else header('Location: karyawan/dashboard.php');
    exit();
}

// Mock user data (tanpa database)
$mock_users = [
    [
        'id'       => 1,
        'nama'     => 'Ahmad Fauzi',
        'email'    => 'karyawan@burnoutxpert.com',
        'password' => 'karyawan123',
        'role'     => 'karyawan',
        'divisi'   => 'Engineering',
    ],
    [
        'id'       => 2,
        'nama'     => 'Siti Rahayu',
        'email'    => 'hrd@burnoutxpert.com',
        'password' => 'hrd123',
        'role'     => 'hrd',
        'divisi'   => 'Human Resources',
    ],
    [
        'id'       => 3,
        'nama'     => 'Budi Santoso',
        'email'    => 'admin@burnoutxpert.com',
        'password' => 'admin123',
        'role'     => 'admin',
        'divisi'   => 'IT Administration',
    ],
];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan Password wajib diisi.';
    } else {
        $found = null;
        foreach ($mock_users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $found = $user;
                break;
            }
        }

        if ($found) {
            $_SESSION['user'] = $found;
            $role = $found['role'];
            if ($role === 'admin') header('Location: admin/dashboard.php');
            elseif ($role === 'hrd') header('Location: hrd/dashboard.php');
            else header('Location: karyawan/dashboard.php');
            exit();
        } else {
            $error = 'Email atau Password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="BurnoutXpert – Sistem Pakar Deteksi Burnout Karyawan berbasis Backward Chaining." />
    <title>Login – BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body class="login-body">

    <!-- ===== Background Decoration ===== -->
    <div class="bg-decoration">
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
        <div class="bg-circle bg-circle--3"></div>
    </div>

    <!-- ===== Top-left Brand Logo ===== -->
    <a href="index.php" class="brand-logo" aria-label="BurnoutXpert Home">
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
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="20" fill="#F4845F" opacity="0.15"/>
                        <circle cx="20" cy="20" r="14" fill="#F4845F" opacity="0.2"/>
                        <path d="M20 8C20 8 11 14.286 11 21.429C11 26.163 15.029 30 20 30C24.971 30 29 26.163 29 21.429C29 14.286 20 8 20 8Z" fill="#F4845F"/>
                        <path d="M20 16C20 16 16 19.571 16 22.857C16 25.143 17.791 27 20 27C22.209 27 24 25.143 24 22.857C24 19.571 20 16 20 16Z" fill="white"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Selamat Datang</h1>
                <p class="login-card__subtitle">Masuk ke <strong>BurnoutXpert</strong> untuk memulai deteksi burnout karyawan</p>
            </div>

            <!-- Error / Success Alert -->
            <?php if ($error): ?>
            <div class="alert alert--error" role="alert" id="alertBox">
                <svg class="alert__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="8" cy="8" r="7.5" stroke="currentColor"/>
                    <path d="M8 4.5V8.5" stroke="currentColor" stroke-linecap="round"/>
                    <circle cx="8" cy="11" r="0.75" fill="currentColor"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
                <button class="alert__close" onclick="this.parentElement.remove()" aria-label="Tutup pesan">&times;</button>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert--success" role="alert">
                <svg class="alert__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="8" cy="8" r="7.5" stroke="currentColor"/>
                    <path d="M5 8L7 10L11 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form class="login-form" method="POST" action="index.php" novalidate id="loginForm">

                <!-- Email Field -->
                <div class="form-group" id="emailGroup">
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
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                            aria-required="true"
                        />
                        <span class="input-focus-ring" aria-hidden="true"></span>
                    </div>
                    <span class="form-error" id="emailError" role="alert"></span>
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
                        <span class="input-focus-ring" aria-hidden="true"></span>
                    </div>
                    <span class="form-error" id="passwordError" role="alert"></span>
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="form-options">
                    <label class="checkbox-label" for="remember">
                        <input type="checkbox" id="remember" name="remember" class="checkbox-input" />
                        <span class="checkbox-custom" aria-hidden="true"></span>
                        <span class="checkbox-text">Ingat Saya</span>
                    </label>
                    <a href="#" class="form-link" onclick="alert('Fitur reset password belum tersedia.')">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="btnLogin">
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
                        <div class="demo-table__row demo-table__row--karyawan" onclick="fillDemo('karyawan@burnoutxpert.com','karyawan123')">
                            <span class="role-badge role-badge--karyawan">Karyawan</span>
                            <span>karyawan@burnoutxpert.com</span>
                            <span>karyawan123</span>
                        </div>
                        <div class="demo-table__row demo-table__row--hrd" onclick="fillDemo('hrd@burnoutxpert.com','hrd123')">
                            <span class="role-badge role-badge--hrd">HRD</span>
                            <span>hrd@burnoutxpert.com</span>
                            <span>hrd123</span>
                        </div>
                        <div class="demo-table__row demo-table__row--admin" onclick="fillDemo('admin@burnoutxpert.com','admin123')">
                            <span class="role-badge role-badge--admin">Admin</span>
                            <span>admin@burnoutxpert.com</span>
                            <span>admin123</span>
                        </div>
                    </div>
                    <p class="demo-hint">Klik baris untuk mengisi form otomatis</p>
                </div>
            </div>

        </div><!-- /.login-card -->

        <!-- Footer -->
        <footer class="login-footer" role="contentinfo">
            <p>&copy; <?= date('Y') ?> BurnoutXpert &mdash; Sistem Pakar Deteksi Burnout Karyawan</p>
            <p>Dibuat untuk keperluan akademik &mdash; Kecerdasan Buatan D4 TI</p>
        </footer>

    </main><!-- /.login-wrapper -->

    <script src="assets/js/login.js"></script>
</body>
</html>
