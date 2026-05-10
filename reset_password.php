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
    <style>
        .strength-meter { height: 6px; background: var(--color-gray-100); border-radius: 4px; margin-top: 0.5rem; overflow: hidden; display: flex; }
        .strength-bar { height: 100%; width: 0; transition: 0.3s ease; }
        .strength-text { font-size: 0.75rem; font-weight: 700; color: var(--color-gray-500); margin-top: 0.25rem; text-align: right; }
    </style>
</head>
<body class="login-body">

    <div class="bg-decoration">
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
    </div>

    <main class="login-wrapper" role="main">
        <div class="login-card">
            <div class="login-card__header">
                <div class="login-card__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="M8 11l3 3 5-5"></path>
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
