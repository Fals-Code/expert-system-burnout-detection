# 🔥 BurnoutXpert – Sistem Pakar Deteksi Burnout Karyawan

> Aplikasi berbasis web untuk mendeteksi tingkat burnout karyawan menggunakan metode **Backward Chaining** dan **Certainty Factor (CF)**, dibangun dengan framework **Laravel 11**.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 Deskripsi

BurnoutXpert adalah sistem pakar yang dirancang untuk membantu perusahaan mendeteksi tingkat burnout karyawan secara dini. Sistem ini menggunakan:

- **Backward Chaining** – Metode inferensi goal-driven yang menguji hipotesis dari tingkat burnout tertinggi ke terendah
- **Certainty Factor (CF)** – Mengukur tingkat keyakinan diagnosis berdasarkan jawaban pengguna dan bobot pakar
- **Knowledge Base berbasis MBI** – Gejala-gejala disusun berdasarkan Maslach Burnout Inventory (Maslach, Jackson & Leiter, 1996)

### Referensi Ilmiah

| Sumber                                                                                                                       | Keterangan                           |
| ---------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ |
| Maslach, C., Jackson, S.E. & Leiter, M.P. (1996). _Maslach Burnout Inventory Manual_ (3rd ed.). CPP.                         | Instrumen standar pengukuran burnout |
| Maslach, C. & Leiter, M.P. (2016). _Understanding the burnout experience_. World Psychiatry, 15(2), 103-111.                 | Teori 3 dimensi burnout              |
| Shortliffe, E.H. & Buchanan, B.G. (1975). _A model of inexact reasoning in medicine_. Mathematical Biosciences, 23, 351-379. | Certainty Factor model               |

---

## ✨ Fitur Utama

### 🧠 Sistem Pakar

- Backward Chaining + Certainty Factor (CF Combine)
- 12 gejala berbasis MBI (4 kategori: Emosional, Perilaku, Kognitif, Fisik)
- 8 rules dengan 4 tingkat diagnosis
- Explanation Facility (penjelasan bahasa natural)
- Tracing kalkulasi transparan

### 👥 Multi-Role System

| Role         | Akses                                                      |
| ------------ | ---------------------------------------------------------- |
| **Admin**    | CRUD Knowledge Base, manajemen user, audit log             |
| **HRD**      | Dashboard monitoring, laporan per divisi, riwayat karyawan |
| **Karyawan** | Deteksi burnout (wizard), riwayat + grafik tren, unduh PDF |

### 🎨 UI/UX

- Dark mode toggle
- Responsive design
- ApexCharts analytics
- SweetAlert2 confirmations
- Progressive Web App (PWA)
- Animated page transitions

### 🔒 Keamanan

- CSRF protection
- Password hashing (bcrypt)
- Role-based access control (middleware)
- Login rate limiting (5x per 2 menit)
- Audit logging

### 🛠️ Perubahan Terbaru

- Memperbaiki duplikasi method dan syntax error di `app/Services/ExpertSystemService.php`
- Menghilangkan warning properti dinamis Eloquent dengan menambah docblock model
- Memperbaiki konstruktor model default menggunakan `Diagnosa::make()` untuk fallback hasil diagnosis
- Menjaga stabilitas notifikasi dan controller pada `NotificationService` dan `NotificationController`

---

## 🚀 Instalasi

### Prasyarat

- PHP ≥ 8.2
- Composer
- MySQL / MariaDB
- Node.js ≥ 18 (opsional, untuk asset compilation)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/Fals-Code/expert-system-burnout-detection.git
cd expert-system-burnout-detection

# 2. Install dependensi PHP
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi database di .env
#    Ubah sesuai kredensial MySQL Anda:
#    DB_DATABASE=burnoutxpert
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Jalankan migrasi database
php artisan migrate

# 7. Seed data Knowledge Base + User demo
php artisan db:seed

# 8. Jalankan server development
php artisan serve
```

### Akun Demo

| Role     | Email                     | Password |
| -------- | ------------------------- | -------- |
| Admin    | admin@burnoutxpert.com    | password |
| HRD      | hrd@burnoutxpert.com      | password |
| Karyawan | karyawan@burnoutxpert.com | password |

> ⚠️ Ganti password default setelah login pertama kali!

---

## 📁 Struktur Proyek

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/LoginController.php     # Autentikasi + rate limiting
│   │   ├── Admin/                       # CRUD Knowledge Base + Users
│   │   ├── Hrd/ReportController.php     # Laporan & monitoring
│   │   ├── DeteksiController.php        # Wizard deteksi burnout
│   │   ├── KaryawanController.php       # Dashboard + riwayat
│   │   └── ProfileController.php        # Profil + ubah sandi
│   ├── Models/
│   │   ├── Gejala.php                   # 12 gejala MBI-based
│   │   ├── Diagnosa.php                 # 4 level burnout
│   │   ├── Aturan.php                   # 8 rules inferensi
│   │   └── Konsultasi.php               # Hasil deteksi
│   └── Services/
│       ├── ExpertSystemService.php      # Engine BC + CF + Explanation
│       └── NotificationService.php      # Notifikasi sistem
├── database/
│   ├── migrations/                      # Schema database
│   └── seeders/
│       └── BurnoutKnowledgeBaseSeeder.php  # Knowledge base MBI
├── resources/views/
│   ├── admin/                           # Views admin
│   ├── hrd/                             # Views HRD + reports
│   ├── karyawan/                        # Views karyawan + deteksi
│   ├── errors/                          # Custom error pages (403/404/500)
│   └── layouts/                         # Template layouts
├── tests/Unit/
│   └── ExpertSystemServiceTest.php      # 19 unit tests (58 assertions)
└── public/
    ├── manifest.json                    # PWA manifest
    ├── sw.js                            # Service Worker
    └── assets/css/                      # Stylesheets
```

---

## 🧪 Testing

```bash
# Jalankan semua tests
php artisan test

# Jalankan hanya unit test expert system
php artisan test --filter=ExpertSystemServiceTest

# Expected output: 19 tests, 58 assertions — PASSED
```

---

## 📊 Knowledge Base

### Dimensi Gejala (MBI-Based)

| Dimensi                         | Kode    | Gejala                                                  | Bobot     |
| ------------------------------- | ------- | ------------------------------------------------------- | --------- |
| Emotional Exhaustion            | G01-G04 | Terkuras emosional, kelelahan, beban berat, mudah marah | 0.60-0.80 |
| Depersonalization               | G05-G07 | Sinisme, tidak peduli, isolasi diri                     | 0.70-0.90 |
| Reduced Personal Accomplishment | G08-G10 | Tidak berdampak, sulit konsentrasi, tidak puas          | 0.60-0.70 |
| Fisik                           | G11-G12 | Sakit kepala/pencernaan, gangguan tidur                 | 0.55-0.60 |

### Rules Inferensi

| Rule | Diagnosa      | CF Pakar | Gejala             |
| ---- | ------------- | -------- | ------------------ |
| R01  | Sangat Tinggi | 0.95     | G01, G02, G05, G06 |
| R02  | Sangat Tinggi | 0.90     | G01, G04, G07, G12 |
| R03  | Tinggi        | 0.85     | G01, G03, G08, G09 |
| R04  | Tinggi        | 0.80     | G05, G06, G07, G11 |
| R05  | Sedang        | 0.75     | G01, G11, G12      |
| R06  | Sedang        | 0.70     | G08, G09, G10, G04 |
| R07  | Rendah        | 0.60     | G04, G10           |
| R08  | Rendah        | 0.55     | G11, G12, G02      |

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan akademis – UAS Mata Kuliah **Kecerdasan Buatan**, Program Studi D4 Teknik Informatika.

---

<p align="center">
  <strong>BurnoutXpert</strong> · Dibuat menggunakan Laravel 11
</p>
