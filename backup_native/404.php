<?php
session_start();
$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

$dashboard_url = 'index.php';
if ($role === 'admin') $dashboard_url = 'admin/dashboard.php';
elseif ($role === 'hrd') $dashboard_url = 'hrd/dashboard.php';
elseif ($role === 'karyawan') $dashboard_url = 'karyawan/dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | BurnoutXpert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-align: center;
        }

        .error-container {
            max-width: 600px;
            padding: 2rem;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── SVG Illustration ── */
        .illustration {
            width: 320px;
            margin: 0 auto 2rem;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* ── Text Content ── */
        .error-code {
            font-size: 6rem;
            font-weight: 900;
            color: var(--color-accent);
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .error-desc {
            font-size: 1rem;
            color: var(--color-gray-500);
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        /* ── Buttons ── */
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.9rem 1.8rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--color-primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.2);
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 95, 0.3);
        }

        .btn-outline {
            background: #fff;
            color: var(--color-gray-600);
            border: 2px solid var(--color-gray-200);
        }

        .btn-outline:hover {
            border-color: var(--color-gray-400);
            background: var(--color-gray-50);
            color: var(--color-primary);
        }

        @media (max-width: 480px) {
            .illustration { width: 240px; }
            .error-code { font-size: 4rem; }
            .btn-group { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="error-container">
        <!-- Exhausted Person Illustration -->
        <div class="illustration">
            <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Desk -->
                <rect x="50" y="220" width="300" height="10" rx="5" fill="#E2E8EF"/>
                <!-- Computer Monitor -->
                <rect x="130" y="100" width="140" height="100" rx="8" fill="#1E3A5F"/>
                <rect x="140" y="110" width="120" height="70" rx="4" fill="#F4F7FA"/>
                <rect x="180" y="200" width="40" height="20" fill="#1E3A5F"/>
                <!-- Character Head -->
                <circle cx="200" cy="220" r="30" fill="#F4845F"/> <!-- Slumped Head on desk -->
                <!-- Hair/Head Detail -->
                <path d="M175 200C175 185 225 185 225 200" stroke="#1E3A5F" stroke-width="4" stroke-linecap="round"/>
                <!-- Exhaustion lines -->
                <path d="M160 80L170 90M230 80L220 90M200 60V75" stroke="#F4845F" stroke-width="3" stroke-linecap="round" opacity="0.6"/>
                <!-- Screen Content (404) -->
                <text x="160" y="155" fill="#F4845F" font-family="Arial" font-weight="bold" font-size="24">404</text>
                <rect x="160" y="165" width="80" height="4" rx="2" fill="#1E3A5F" opacity="0.2"/>
            </svg>
        </div>

        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-desc">
            Sepertinya halaman ini sedang <strong>burnout</strong> dan membutuhkan istirahat.<br>
            Yuk kembali ke halaman utama untuk melanjutkan aktivitas!
        </p>

        <div class="btn-group">
            <a href="<?= $dashboard_url ?>" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 10 4 15 9 20"></polyline>
                    <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                </svg>
                Kembali ke Dashboard
            </a>
            <a href="#" class="btn btn-outline">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                Laporkan Masalah
            </a>
        </div>
    </div>

</body>
</html>
