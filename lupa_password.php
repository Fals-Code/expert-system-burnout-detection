<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Alamat email wajib diisi.';
    } else {
        // Simulasi pengiriman email
        $success = 'Tautan pemulihan telah dikirim ke email Anda.';
        // Untuk demo, kita arahkan pengguna ke reset_password.php dalam beberapa detik menggunakan javascript
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Lupa Password – BurnoutXpert</title>
    <?php include 'includes/head.php'; ?>
    <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body class="login-body">

    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
        <div class="bg-circle bg-circle--3"></div>
    </div>

    <main class="login-wrapper" role="main">
        <div class="login-card">
            <div class="login-card__header">
                <div class="login-card__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1 class="login-card__title">Lupa Password?</h1>
                <p class="login-card__subtitle">Jangan khawatir! Masukkan alamat email Anda dan kami akan mengirimkan instruksi untuk memulihkan akses Anda.</p>
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
                    window.location.href = 'reset_password.php?token=demo123';
                }, 2500);
            </script>
            <?php endif; ?>

            <form class="login-form" method="POST" action="lupa_password.php">
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="contoh@burnoutxpert.com"
                            required
                        />
                    </div>
                </div>

                <button type="submit" class="btn-login" <?= $success ? 'disabled' : '' ?>>
                    <span class="btn-login__text">Kirim Tautan Pemulihan</span>
                </button>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" class="form-link" style="font-weight: 600;">
                        ← Kembali ke Halaman Login
                    </a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
