<?php
/**
 * BurnoutXpert – Konfigurasi Aplikasi
 * File ini akan digunakan saat koneksi database diaktifkan.
 */

// ── Mode Aplikasi ────────────────────────────────────────────
define('APP_NAME',    'BurnoutXpert');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // 'development' | 'production'
define('BASE_URL',    'http://localhost/UAS/');

// ── Konfigurasi Database (aktifkan saat siap) ────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'burnoutxpert_db');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');

// ── Konfigurasi Session ──────────────────────────────────────
define('SESSION_NAME',     'burnoutxpert_session');
define('SESSION_LIFETIME', 7200); // 2 jam (detik)

// ── Fungsi Koneksi PDO (siap pakai saat DB aktif) ────────────
function getDBConnection(): ?PDO {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (APP_ENV === 'development') {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
        error_log('DB Connection Error: ' . $e->getMessage());
        return null;
    }
}

// ── Autoload sederhana ───────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
