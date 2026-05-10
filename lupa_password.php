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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (empty($email)) {
        $error = 'Alamat email wajib diisi.';
    } else {
        // Cari user di store
        $found_user = find_user_by_email($email);

        // Juga cek fallback akun demo
        if (!$found_user) {
            $demo_emails = [
                'karyawan@burnoutxpert.com',
                'hrd@burnoutxpert.com',
                'admin@burnoutxpert.com',
            ];
            if (in_array($email, $demo_emails)) {
                $found_user = ['email' => $email, 'nama' => 'Demo User'];
            }
        }

        if ($found_user) {
            // Buat token reset — 6 karakter alphanumeric uppercase
            $token   = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
            $expires = time() + (15 * 60); // 15 menit

            $_SESSION['bx_store']['reset_tokens'][$email] = [
                'token'   => $token,
                'expires' => $expires,
            ];

            // Dalam demo: tampilkan token langsung di halaman (pengganti email)
            $success = "Token reset password untuk <strong>{$email}</strong>:<br>
                        <div style='font-size:2rem; font-weight:900; letter-spacing:0.4rem; color:#1E3A5F; font-family:monospace; margin:0.75rem 0;'>{$token}</div>
                        Token berlaku selama <strong>15 menit</strong>.<br>
                        <small style='color:#6B7280;'>(Dalam produksi, token dikirim via email. Untuk demo, token ditampilkan di sini.)</small>";

            append_log('System', 'RESET_TOKEN', $email, "Token reset password dibuat untuk: {$email}");
        } else {
            // Jangan bocorkan apakah email terdaftar atau tidak (keamanan)
            $success = "Jika email <strong>{$email}</strong> terdaftar di sistem, instruksi reset akan terkirim.
                        <br><small style='color:#6B7280;'>(Demo: email tidak ditemukan di sistem)</small>";
        }
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

    <div class="bg-decoration">
        <div class="bg-circle bg-circle--1"></div>
        <div class="bg-circle bg-circle--2"></div>
        <div class="bg-circle bg-circle--3"></div>
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
                        <circle cx="20" cy="20" r="14" fill="#F4845F" opacity="0.2"/>
                        <path d="M20 10V14M16 14H24M16 14V11C16 8.79086 17.7909 7 20 7C22.2091 7 24 8.79086 24 11V14M13 14H27C28.1046 14 29 14.8954 29 16V27C29 28.1046 28.1046 29 27 29H13C11.8954 29 11 28.1046 11 27V16C11 14.8954 11.8954 14 13 14Z" stroke="#F4845F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Lupa Password?</h1>
                <p class="login-card__subtitle">Masukkan alamat email Anda untuk mendapatkan token reset password.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert--error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert--success" style="line-height:1.8;">
                <?= $success ?>
            </div>
            <div style="text-align:center; margin-top:1rem;">
                <a href="reset_password.php" class="btn-login" style="display:inline-flex; text-decoration:none; padding:0.75rem 1.5rem;">
                    <span class="btn-login__text">Lanjut ke Reset Password</span>
                </a>
            </div>
            <?php else: ?>

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
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        />
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span class="btn-login__text">Kirim Token Reset</span>
                </button>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" class="form-link" style="font-weight: 600;">
                        ← Kembali ke Halaman Login
                    </a>
                </div>
            </form>

            <?php endif; ?>
        </div>
    </main>

</body>
</html>
