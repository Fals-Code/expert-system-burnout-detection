<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/data_store.php';
bx_init_store();

$success = '';
$error   = '';
$token_valid = false;
$validated_email = '';

// ── Validasi token dari form ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_token = strtoupper(trim($_POST['token'] ?? ''));
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $password    = $_POST['password']  ?? '';
    $confirm     = $_POST['confirm']   ?? '';

    if ($_POST['step'] === 'verify_token') {
        // Step 1: verifikasi token
        $stored = $_SESSION['bx_store']['reset_tokens'][$email] ?? null;
        if (!$stored) {
            $error = 'Email tidak ditemukan atau token belum dibuat. Coba minta token baru.';
        } elseif (time() > $stored['expires']) {
            unset($_SESSION['bx_store']['reset_tokens'][$email]);
            $error = 'Token sudah kadaluarsa (lebih dari 15 menit). Silakan minta token baru.';
        } elseif ($input_token !== $stored['token']) {
            $error = 'Token tidak valid. Periksa kembali token yang Anda masukkan.';
        } else {
            // Token valid → simpan email tervalidasi di session untuk step 2
            $_SESSION['reset_verified_email'] = $email;
            $token_valid       = true;
            $validated_email   = $email;
        }

    } elseif ($_POST['step'] === 'reset_password') {
        // Step 2: reset password
        $verified_email = $_SESSION['reset_verified_email'] ?? '';
        if (empty($verified_email)) {
            $error = 'Sesi verifikasi tidak valid. Mulai ulang proses reset.';
        } elseif (empty($password) || empty($confirm)) {
            $error = 'Semua field wajib diisi.';
            $token_valid = true; $validated_email = $verified_email;
        } elseif ($password !== $confirm) {
            $error = 'Konfirmasi password tidak cocok.';
            $token_valid = true; $validated_email = $verified_email;
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
            $token_valid = true; $validated_email = $verified_email;
        } else {
            // Update password di store
            $updated = update_user_password($verified_email, $password);
            if ($updated) {
                unset($_SESSION['bx_store']['reset_tokens'][$verified_email]);
                unset($_SESSION['reset_verified_email']);
                append_log('System', 'RESET_PASSWORD', $verified_email, "Password berhasil direset untuk: {$verified_email}");
                $success = 'Password berhasil diubah! Anda akan diarahkan ke halaman login...';
            } else {
                $error = 'Gagal memperbarui password. Akun tidak ditemukan di sistem.';
            }
        }
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
                        <path d="M20 12V18M20 18L23 15M20 18L17 15M11 20C11 24.9706 15.0294 29 20 29C24.9706 29 29 24.9706 29 20C29 15.0294 24.9706 11 20 11C15.0294 11 11 15.0294 11 20Z" stroke="#F4845F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Reset Password</h1>
                <p class="login-card__subtitle">
                    <?= $token_valid ? 'Masukkan password baru Anda.' : 'Masukkan email dan token yang Anda terima.' ?>
                </p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert--error"><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert--success"><span><?= htmlspecialchars($success) ?></span></div>
            <script>setTimeout(function() { window.location.href = 'index.php'; }, 3000);</script>

            <?php elseif ($token_valid): ?>
            <!-- Step 2: Buat password baru -->
            <form class="login-form" method="POST" action="reset_password.php">
                <input type="hidden" name="step" value="reset_password">
                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required/>
                    </div>
                    <div class="strength-meter"><div class="strength-bar" id="strengthBar"></div></div>
                    <div class="strength-text" id="strengthText" style="font-size:0.8rem; margin-top:0.25rem;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm" name="confirm" class="form-input" placeholder="Ulangi password baru" required/>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <span class="btn-login__text">Simpan Password Baru</span>
                </button>
            </form>

            <?php else: ?>
            <!-- Step 1: Verifikasi token -->
            <form class="login-form" method="POST" action="reset_password.php">
                <input type="hidden" name="step" value="verify_token">
                <div class="form-group">
                    <label class="form-label" for="email">Email Terdaftar</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-input"
                            placeholder="Email yang Anda gunakan di lupa_password.php"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="token">Token Reset (6 Karakter)</label>
                    <div class="input-wrapper">
                        <input type="text" id="token" name="token" class="form-input"
                            placeholder="Contoh: A1B2C3"
                            style="text-transform:uppercase; letter-spacing:0.3rem; font-size:1.1rem; font-weight:700;"
                            maxlength="6" required/>
                    </div>
                    <small style="color:var(--color-gray-400);">Token didapat dari halaman Lupa Password. Berlaku 15 menit.</small>
                </div>
                <button type="submit" class="btn-login">
                    <span class="btn-login__text">Verifikasi Token</span>
                </button>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="lupa_password.php" class="form-link">← Kembali untuk meminta token baru</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pwInput = document.getElementById('password');
            const strBar  = document.getElementById('strengthBar');
            const strText = document.getElementById('strengthText');
            if (!pwInput) return;

            pwInput.addEventListener('input', function() {
                const val = pwInput.value;
                let strength = 0;
                if (val.length >= 6)  strength += 25;
                if (val.length >= 10) strength += 15;
                if (val.match(/[A-Z]/)) strength += 20;
                if (val.match(/[0-9]/)) strength += 20;
                if (val.match(/[^a-zA-Z0-9]/)) strength += 20;
                strength = Math.min(strength, 100);

                strBar.style.width = strength + '%';
                if (val.length === 0) { strBar.style.width = '0%'; strText.innerText = ''; }
                else if (strength < 40) { strBar.style.background = '#DC3545'; strText.innerText = 'Lemah'; strText.style.color = '#DC3545'; }
                else if (strength < 75) { strBar.style.background = '#FFC107'; strText.innerText = 'Cukup Kuat'; strText.style.color = '#D97706'; }
                else { strBar.style.background = '#10B981'; strText.innerText = 'Sangat Kuat ✓'; strText.style.color = '#10B981'; }
            });

            // Auto-uppercase token field
            const tokenInput = document.getElementById('token');
            if (tokenInput) {
                tokenInput.addEventListener('input', () => {
                    tokenInput.value = tokenInput.value.toUpperCase();
                });
            }
        });
    </script>
</body>
</html>
