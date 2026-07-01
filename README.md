# SanctuaryHub

SanctuaryHub adalah aplikasi Laravel untuk skrining awal risiko burnout karyawan menggunakan sistem pakar Backward Chaining dan Certainty Factor. Aplikasi ini bukan alat diagnosis medis dan tidak boleh dipakai sebagai penilaian performa individu.

## Teknologi

- Laravel 13, PHP 8.3+
- Blade, Tailwind CSS, Vite
- MySQL/MariaDB untuk local development Laragon
- SQLite in-memory untuk automated test
- PHPUnit dan Laravel Pint

## Instalasi Windows/Laragon

1. Clone repository ke folder Laragon, misalnya `D:\proyek\expert-system-burnout-detection`.
2. Buat database baru di MySQL/MariaDB: `sanctuaryhub`.
3. Jalankan:

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php artisan test
php artisan serve
```

URL default: `http://127.0.0.1:8000`.

## Akun Demo

Semua akun memakai password `password`.

- Karyawan: `karyawan@sanctuaryhub.test`
- HRD: `hrd@sanctuaryhub.test`
- Admin: `admin@sanctuaryhub.test`

Jangan gunakan akun demo di production.

## Metode AI

Engine dimulai dari goal, bukan dari semua rule sekaligus:

1. Risiko Burnout Tinggi
2. Risiko Burnout Sedang
3. Risiko Burnout Rendah
4. Fallback Tidak Terindikasi Burnout

Mapping jawaban:

- Sering = `1.0`
- Kadang = `0.6`
- Tidak Pernah = `0.0`

Rumus:

```text
CF premis = CF user x bobot gejala
CF rule = rata-rata CF premis x CF pakar rule
```

Rule lolos bila `CF rule >= threshold`, default `0.25`. Jika beberapa rule mendukung goal yang sama, rule dengan CF terkuat menjadi rule utama. Jika goal terkonfirmasi, engine berhenti sesuai urutan prioritas.

Contoh R001 dari PDF:

```text
G001=0.85, G006=0.92, G009=0.80, G014=0.88, G017=0.70
Semua dijawab Sering, CF pakar = 0.95
Rata-rata premis = (0.85+0.92+0.80+0.88+0.70)/5 = 0.83
CF rule = 0.83 x 0.95 = 0.7885
```

## Role

- Karyawan: mengisi check-in, melihat hasil dan riwayat milik sendiri.
- HRD: melihat dashboard dan laporan agregat empat kategori. Jawaban mentah dan identitas individu tidak ditampilkan di notifikasi.
- Admin: mengelola knowledge base, akun, dan audit log.

## Struktur Penting

- `app/Services/BackwardChainingEngine.php`
- `app/Services/CertaintyFactorCalculator.php`
- `app/Services/ExpertSystemService.php`
- `database/seeders/BurnoutKnowledgeBaseSeeder.php`
- `docs/ALGORITHM.md`
- `docs/TRACEABILITY.md`
- `backup_native/` adalah arsip legacy dan bukan runtime Laravel.

## Verifikasi

```powershell
composer install
php artisan optimize:clear
php artisan migrate:fresh --seed
npm install
npm run build
php artisan test
php artisan route:list
vendor\bin\pint --test
```

Jika `npm run build` gagal di PowerShell karena execution policy, gunakan:

```powershell
npm.cmd run build
```

## Troubleshooting

- `APP_KEY missing`: jalankan `php artisan key:generate`.
- MySQL gagal konek: pastikan Laragon MySQL aktif dan database `sanctuaryhub` sudah dibuat.
- Composer gagal extension: aktifkan `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `zip`, dan `intl` di PHP Laragon.
- Port 8000 bentrok: jalankan `php artisan serve --port=8001`.
- Vite gagal: hapus cache npm lokal bila perlu lalu jalankan `npm install`.

## Security dan Privasi

SanctuaryHub memakai auth Laravel, CSRF, session regeneration saat login, role middleware, validasi request, dan proteksi IDOR pada hasil karyawan. Service worker hanya cache aset statis, bukan halaman sensitif. HRD menerima agregat, bukan jawaban mentah individu.

## Disclaimer

Hasil SanctuaryHub adalah skrining awal berbasis rule pakar dan jawaban pengguna. Sistem ini tidak menggantikan konsultasi psikolog, dokter, atau tenaga profesional kesehatan lain.
