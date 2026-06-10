# 🔥 BurnoutXpert

> **Sistem Pakar berbasis web untuk mendeteksi tingkat burnout karyawan secara dini** dengan metode **Backward Chaining**, **Certainty Factor**, directional evidence, dan pengamanan data hasil diagnosis.

<p align="left">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Framework">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/MySQL%2FMariaDB-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL MariaDB">
  <img src="https://img.shields.io/badge/PHPUnit-Feature_Testing-6C3?style=for-the-badge&logo=php&logoColor=white" alt="PHPUnit">
</p>

---

## 📌 Table of Contents

- [Project Overview](#-project-overview)
- [Alur Logika Sistem Pakar](#-alur-logika-sistem-pakar)
- [Core Features](#-core-features)
- [Security & Reliability Engineering](#-security--reliability-engineering)
- [Directory Structure](#-directory-structure)
- [Installation Guide](#-installation-guide)
- [Cache Management](#-cache-management)
- [Automated Testing Protocol](#-automated-testing-protocol)
- [Contributor](#-contributor)

---

## 🧠 Project Overview

**BurnoutXpert** adalah aplikasi web berbasis **Laravel + Blade** yang dirancang untuk membantu perusahaan melakukan deteksi dini terhadap kondisi burnout karyawan. Sistem ini tidak hanya menyimpan hasil survei, tetapi juga menjalankan proses inferensi berbasis **Sistem Pakar** untuk menghasilkan diagnosis yang dapat ditelusuri kembali melalui rule, bobot pakar, dan nilai Certainty Factor.

Aplikasi ini menggunakan pendekatan kuesioner yang dibuat lebih netral secara psikologis melalui judul:

> **“Survei Evaluasi Kenyamanan dan Kebugaran Lingkungan Kerja Karyawan”**

Tujuannya adalah mengurangi **response bias**, yaitu kecenderungan pengguna menjawab seolah-olah sedang diarahkan menuju diagnosis tertentu. Karena ya, bahkan manusia bisa bias hanya karena judul halaman. Teknologi hebat, psikologi tetap licik.

### Hasil Diagnosis

| Kode | Diagnosis | Makna |
|---|---|---|
| **D01** | **Tidak Burnout / Sehat** | Tidak ditemukan pola gejala burnout yang signifikan. |
| **D02** | **Burnout Tinggi** | Gejala burnout kuat dan memerlukan tindak lanjut cepat. |
| **D03** | **Burnout Sedang** | Gejala mulai terlihat dan perlu dikendalikan. |
| **D04** | **Burnout Rendah** | Indikasi ringan, tetap perlu pemantauan. |

---

## 🔎 Alur Logika Sistem Pakar

BurnoutXpert menggunakan metode **Backward Chaining**, yaitu proses inferensi yang dimulai dari hipotesis diagnosis, lalu menelusuri gejala yang mendukung atau melemahkan hipotesis tersebut.

Engine utama berada pada:

```text
app/Services/ExpertSystemService.php
```

### 1. Backward Chaining

Sistem mengevaluasi rule aktif dari basis pengetahuan, lalu mencocokkan jawaban karyawan dengan gejala pada setiap aturan.

```text
Hipotesis Diagnosis → Evaluasi Rule → Cocokkan Gejala → Hitung CF → Pilih Diagnosis Terkuat
```

### 2. Certainty Factor Combine

Setiap jawaban pengguna dikonversi menjadi nilai **CF User**, lalu dikalikan dengan bobot pakar pada rule.

```text
CF Sub = CF User × Bobot Pakar
CF Final = CF Combine Gejala × CF Pakar Rule
```

### 3. Directional Evidence Matrix

BurnoutXpert memakai penalaran dua arah agar rule sehat tidak keliru naik saat user menjawab “Ya” pada gejala burnout.

| Evidence Direction | Logika | Contoh |
|---|---|---|
| **PRESENT_SUPPORTS** | Gejala yang dialami mendukung diagnosis. | User menjawab “Ya” pada gejala kelelahan → mendukung Burnout Tinggi. |
| **ABSENT_SUPPORTS** | Gejala yang tidak dialami mendukung diagnosis. | User menjawab “Tidak” pada gejala kelelahan → mendukung Tidak Burnout. |

Dengan model ini, **D01 / Tidak Burnout** tidak dihitung dari banyaknya gejala buruk yang dialami, tetapi dari **absennya gejala burnout utama**. Akhirnya sistem pakar tidak bersikap seperti manusia denial.

### 4. Dynamic Early Stop

Sistem memiliki mekanisme **Dynamic Early Stop** untuk menghentikan proses inferensi lebih cepat jika diagnosis sudah cukup kuat.

Prinsipnya:

- Rule harus valid.
- Nilai CF harus melewati threshold dinamis.
- Jumlah jawaban minimum harus terpenuhi.
- Early stop tidak boleh terlalu agresif agar hasil tidak prematur.

---

## ✨ Core Features

### 👤 Karyawan

| Fitur | Deskripsi |
|---|---|
| **Survei evaluasi netral** | Judul dan wording form dibuat tidak terlalu eksplisit menyebut burnout agar mengurangi bias jawaban. |
| **Deteksi otomatis** | Jawaban diproses oleh engine Backward Chaining + Certainty Factor. |
| **Hasil diagnosis historis** | Halaman hasil membaca record `Konsultasi` yang sudah fixed, bukan menghitung ulang saat refresh. |
| **Anti-IDOR** | Karyawan hanya dapat melihat hasil konsultasinya sendiri. |
| **Laporan PDF** | Hasil dapat dicetak/diunduh sebagai laporan. |
| **Lakukan Deteksi Ulang** | Tombol reset membersihkan session lama dan mengarahkan user ke survei baru. |

### 🛠️ Admin

| Fitur | Deskripsi |
|---|---|
| **Dashboard statistik** | Menampilkan ringkasan user, gejala, aturan, dan log aktivitas. |
| **Manajemen basis pengetahuan** | Admin dapat mengelola data gejala, diagnosis, aturan, bobot pakar, dan threshold. |
| **Refresh Basis Pengetahuan** | Tombol sekali klik untuk membersihkan cache knowledge base yang disimpan 24 jam. |
| **Audit Log** | Aktivitas admin seperti refresh cache dicatat agar proses demo dan operasional dapat dilacak. |

### 🧱 Arsitektur Keamanan & Reliability

| Area | Implementasi |
|---|---|
| **Validasi input** | `StoreDeteksiRequest` memvalidasi `gejala_id[]` dengan rule `exists:gejala,id`. |
| **Atomic persistence** | Penyimpanan hasil diagnosis memakai `DB::transaction()` agar data konsultasi, tracing, dan pivot gejala tidak setengah tersimpan. |
| **Anti-IDOR** | Controller memakai `abort_if($konsultasi->user_id !== Auth::id(), 403)`. |
| **Eager loading** | Relasi seperti `diagnosa` dan `gejala` di-load dengan `with()` untuk menghindari N+1 query. |
| **Cache invalidation** | Admin dapat membersihkan cache rule, diagnosa, dan aturan per diagnosis melalui dashboard. |
| **Tracing** | Setiap hasil menyimpan detail rule, CF pakar, CF gejala, dan kontribusi gejala. |

---

## 🧩 Directory Structure

Struktur berikut menyoroti folder dan file yang paling penting untuk memahami proyek.

```text
BurnoutXpert/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php
│   │   │   ├── KaryawanController.php
│   │   │   └── DeteksiController.php
│   │   └── Requests/
│   │       └── StoreDeteksiRequest.php
│   ├── Models/
│   │   ├── Aturan.php
│   │   ├── Diagnosa.php
│   │   ├── Gejala.php
│   │   └── Konsultasi.php
│   └── Services/
│       └── ExpertSystemService.php
│
├── database/
│   ├── migrations/
│   │   ├── *_create_burnout_xpert_tables.php
│   │   ├── *_enhance_expert_system_tables.php
│   │   └── *_add_evidence_direction_to_aturan_gejala_table.php
│   └── seeders/
│
├── resources/
│   └── views/
│       ├── admin/
│       │   └── dashboard.blade.php
│       └── karyawan/
│           └── deteksi/
│               ├── form.blade.php
│               ├── hasil.blade.php
│               └── report.blade.php
│
├── routes/
│   └── web.php
│
├── tests/
│   └── Feature/
│       └── DiagnosisTest.php
│
├── composer.json
├── package.json
├── phpunit.xml
└── README.md
```

### File Kunci

| File | Fungsi |
|---|---|
| `ExpertSystemService.php` | Engine inferensi Backward Chaining, Certainty Factor, directional evidence, explanation facility. |
| `DeteksiController.php` | Mengatur flow survei, proses diagnosis, transaction, hasil, reset deteksi ulang. |
| `StoreDeteksiRequest.php` | Validasi input gejala agar ID dan kode gejala valid. |
| `DiagnosisTest.php` | Feature test untuk menguji diagnosis sehat dan burnout tinggi. |
| `admin/dashboard.blade.php` | Dashboard admin dan tombol refresh basis pengetahuan. |
| `karyawan/deteksi/form.blade.php` | Form survei evaluasi kerja dengan wording netral. |
| `karyawan/deteksi/hasil.blade.php` | Halaman hasil diagnosis berbasis record historis `Konsultasi`. |

---

## ⚙️ Installation Guide

### Prasyarat

| Komponen | Versi yang Disarankan |
|---|---|
| PHP | 8.3+ |
| Composer | Versi terbaru stabil |
| Node.js | 18+ atau 20+ |
| Database | MySQL / MariaDB |
| Package manager frontend | npm |

### 1. Clone Repository

```bash
git clone https://github.com/Fals-Code/expert-system-burnout-detection.git
cd expert-system-burnout-detection
```

### 2. Install Dependency Backend

```bash
composer install
```

### 3. Install Dependency Frontend

```bash
npm install
```

### 4. Siapkan Environment

```bash
cp .env.example .env
php artisan key:generate
```

Konfigurasi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=burnoutxpert
DB_USERNAME=root
DB_PASSWORD=
```

> Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan konfigurasi lokal. Komputer dosen tidak akan menebak kredensial database kamu, meski kadang manusia berharap begitu.

### 5. Migrasi dan Seeder

```bash
php artisan migrate --seed
```

Jika ingin menjalankan migrasi tanpa seed:

```bash
php artisan migrate
```

### 6. Build Asset Frontend

Untuk produksi atau demo stabil:

```bash
npm run build
```

Untuk development:

```bash
npm run dev
```

### 7. Jalankan Server Lokal

```bash
php artisan serve
```

Akses aplikasi melalui:

```text
http://127.0.0.1:8000
```

---

## ♻️ Cache Management

BurnoutXpert menyimpan beberapa bagian basis pengetahuan selama 24 jam agar proses inferensi tidak melakukan query berulang.

Cache utama yang digunakan:

| Cache Key | Kegunaan |
|---|---|
| `aturan_active_rules_base64` | Menyimpan rule aktif beserta relasi diagnosis dan gejala. |
| `diagnosa_ordered_base64` | Menyimpan urutan diagnosis untuk inferensi. |
| `diagnosa_default_tidak_burnout_base64` | Menyimpan fallback diagnosis sehat. |
| `aturan_by_diagnosa_{id}_base64` | Menyimpan rule aktif per diagnosis. |

### Refresh dari Dashboard Admin

Admin dapat membuka dashboard lalu menekan tombol:

```text
Refresh Basis Pengetahuan
```

Tombol ini memanggil logic `Cache::forget()` untuk membersihkan cache basis pengetahuan tanpa perlu membuka terminal.

### Refresh Manual dari Terminal

```bash
php artisan optimize:clear
```

Gunakan ini setelah perubahan konfigurasi besar, perubahan environment, atau saat Laravel mulai bertingkah seperti menyimpan dendam di cache.

---

## 🧪 Automated Testing Protocol

BurnoutXpert memiliki pengujian otomatis untuk memastikan perubahan knowledge base tidak merusak hasil inferensi.

Test utama:

```text
tests/Feature/DiagnosisTest.php
```

### Tujuan Test

| Test Case | Ekspektasi |
|---|---|
| Karyawan sehat | Sistem menampilkan **Tidak Burnout** dan rule **R01**. |
| Gejala burnout tinggi | Sistem menampilkan **Burnout Tinggi** dan tidak jatuh ke D01. |

### Teknik Runtime Route Injection

`DiagnosisTest` menggunakan teknik **Runtime Route Injection** pada `setUp()` untuk membuat endpoint testing `POST /diagnosis` tanpa harus mengubah route produksi.

Keuntungan pendekatan ini:

- Test fokus pada integritas engine diagnosis.
- Route produksi tetap bersih.
- Payload `gejala_id[]` dapat disimulasikan langsung.
- Feature test bisa memverifikasi hasil Blade dengan `assertSeeText()`.

### Jalankan Test Spesifik

```bash
php artisan test --filter=DiagnosisTest
```

### Jalankan Semua Test

```bash
php artisan test
```

### Jika Cache Mengganggu Test

```bash
php artisan optimize:clear
php artisan test --filter=DiagnosisTest
```

---

## 🔐 Security Notes

### Anti-IDOR Protection

Halaman hasil diagnosis dilindungi agar satu karyawan tidak dapat membaca hasil milik karyawan lain hanya dengan mengganti ID URL.

```php
abort_if((int) $konsultasi->user_id !== (int) Auth::id(), 403);
```

### Database Transaction

Penyimpanan hasil diagnosis memakai transaction:

```php
$konsultasi = DB::transaction(function () use ($result, $answers) {
    return $this->expertSystem->saveResult(Auth::id(), $result, $answers);
});
```

Jika proses attach pivot gejala gagal, seluruh penyimpanan akan rollback.

### Validasi Bertingkat

`StoreDeteksiRequest` memastikan:

```php
'gejala_id' => ['sometimes', 'array'],
'gejala_id.*' => ['integer', 'exists:gejala,id'],
```

Dengan ini, payload gejala palsu seperti `99999` tidak akan masuk ke engine.

---

## 🧭 Recommended Demo Flow

Gunakan alur berikut saat presentasi:

| Langkah | Aksi |
|---|---|
| 1 | Login sebagai Admin. |
| 2 | Buka dashboard admin dan tunjukkan statistik sistem. |
| 3 | Ubah bobot pakar atau threshold pada basis pengetahuan. |
| 4 | Klik **Refresh Basis Pengetahuan**. |
| 5 | Login sebagai Karyawan. |
| 6 | Isi survei evaluasi kerja dengan skenario sehat atau burnout tinggi. |
| 7 | Tunjukkan hasil diagnosis, rule dominan, CF, tracing, dan rekomendasi. |
| 8 | Klik **Lakukan Deteksi Ulang** untuk membuktikan state lama dibersihkan. |
| 9 | Jalankan `php artisan test --filter=DiagnosisTest` untuk menunjukkan validasi otomatis. |

---

## 👥 Contributor

**Kelompok 8 Universitas Airlangga**  
Program Studi D4 Teknik Informatika  
Proyek Sistem Pakar Deteksi Burnout Karyawan

---

## 📄 License

Proyek ini dikembangkan untuk keperluan akademik dan demonstrasi rekayasa perangkat lunak berbasis sistem pakar.

---

<p align="center">
  <strong>BurnoutXpert</strong><br>
  Laravel · Blade · Tailwind CSS · MySQL/MariaDB · Backward Chaining · Certainty Factor
</p>
