<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password tidak cocok.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        $success = 'Password berhasil diubah! Anda akan diarahkan ke halaman login...';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Reset Password – BurnoutXpert</title>
    <?php include 'includes/head.php'; ?>
    <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body class="login-body">

    <div class="bg-decoration">
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
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

    <main class="login-wrapper" role="main">
        <div class="login-card">
            <div class="login-card__header">
                <div class="login-card__icon" aria-hidden="true">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="20" fill="#F4845F" opacity="0.15"/>
                        <circle cx="20" cy="20" r="14" fill="#F4845F" opacity="0.2"/>
                        <path d="M20 12V18M20 18L23 15M20 18L17 15M11 20C11 24.9706 15.0294 29 20 29C24.9706 29 29 24.9706 29 20C29 15.0294 24.9706 11 20 11C15.0294 11 11 15.0294 11 20Z" stroke="#F4845F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Buat Password Baru</h1>
                <p class="login-card__subtitle">Pastikan password baru Anda kuat dan mudah diingat.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert--error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert--success">
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 3000);
            </script>
            <?php else: ?>

            <form class="login-form" method="POST" action="reset_password.php">
                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Minimal 8 karakter"
                            required
                        />
                    </div>
                    <div class="strength-meter">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Sangat Lemah</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            class="form-input"
                            placeholder="Ulangi password"
                            required
                        />
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span class="btn-login__text">Simpan Password Baru</span>
                </button>
            </form>

            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pwInput = document.getElementById('password');
            const strBar = document.getElementById('strengthBar');
            const strText = document.getElementById('strengthText');

            if (pwInput) {
                pwInput.addEventListener('input', function() {
                    const val = pwInput.value;
                    let strength = 0;
                    
                    if (val.length >= 8) strength += 25;
                    if (val.match(/[A-Z]/)) strength += 25;
                    if (val.match(/[0-9]/)) strength += 25;
                    if (val.match(/[^a-zA-Z0-9]/)) strength += 25;

                    strBar.style.width = strength + '%';
                    
                    if (val.length === 0) {
                        strBar.style.width = '0%';
                        strText.innerText = 'Sangat Lemah';
                        strText.style.color = 'var(--color-gray-500)';
                    } else if (strength < 50) {
                        strBar.style.background = '#DC3545';
                        strText.innerText = 'Lemah';
                        strText.style.color = '#DC3545';
                    } else if (strength < 100) {
                        strBar.style.background = '#FFC107';
                        strText.innerText = 'Cukup Kuat';
                        strText.style.color = '#D97706';
                    } else {
                        strBar.style.background = '#28A745';
                        strText.innerText = 'Sangat Kuat';
                        strText.style.color = '#10B981';
                    }
                });
            }
        });
    </script>
</body>
</html>
