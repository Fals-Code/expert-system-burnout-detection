# 🧠 BurnoutXpert: Enterprise Expert System

[![Laravel](https://img.shields.io/badge/Framework-Laravel%2011-red.svg)](https://laravel.com)
[![Expert System](https://img.shields.io/badge/Method-Backward%20Chaining-blue.svg)]()
[![Certainty Factor](https://img.shields.io/badge/Logic-Certainty%20Factor-green.svg)]()
[![License](https://img.shields.io/badge/License-MIT-brightgreen.svg)](LICENSE)

**BurnoutXpert** adalah sistem pakar berbasis web profesional yang dirancang untuk mendeteksi tingkat burnout pada karyawan menggunakan metode **Backward Chaining** dan logika **Certainty Factor**. Aplikasi ini menggunakan standar **Maslach Burnout Inventory (MBI)** untuk memberikan diagnosis yang akurat dan berbasis data medis psikologi.

---

## 🚀 Fitur Utama

### 1. Core Expert System Engine
- **Backward Chaining**: Mesin inferensi goal-driven yang efisien untuk membuktikan tingkat burnout.
- **Certainty Factor (CF)**: Menangani ketidakpastian jawaban pengguna dengan algoritma CF standar.
- **Explanation Facility**: Transparansi penuh. Pengguna dapat melihat *tracing* atau langkah-langkah bagaimana sistem mencapai kesimpulan tersebut.

### 2. User & HRD Experience
- **Interactive Wizard**: Proses deteksi step-by-step yang mulus menggunakan Alpine.js.
- **Enterprise Dashboard**: Visualisasi data analitik (ApexCharts) untuk tren burnout per divisi.
- **Automated Reporting**: Ekspor hasil deteksi ke format PDF dan Excel (SheetJS).
- **Notification System**: Peringatan otomatis ke tim HRD jika terdeteksi karyawan dengan burnout tingkat tinggi.

### 3. Management Portal
- **Knowledge Base Management**: CRUD Gejala, Aturan, dan Diagnosa dengan validasi ketat.
- **Audit Logs**: Mencatat setiap aktivitas krusial pada sistem untuk keamanan data.
- **User Management**: Pengaturan hak akses (Admin, HRD, Karyawan).

---

## 🛠️ Tech Stack
- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL / PostgreSQL
- **Frontend**: Blade, CSS Vanilla (Premium Design), Alpine.js
- **Charts**: ApexCharts
- **Export**: SheetJS (XLSX), HTML2PDF

---

## 📦 Cara Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/Fals-Code/expert-system-burnout-detection.git
   cd expert-system-burnout-detection
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run dev
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   Sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.

5. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```

---

## 📋 Basis Pengetahuan (MBI Standard)
Sistem ini memetakan gejala ke dalam 3 dimensi utama:
1. **Emotional Exhaustion** (Kelelahan Emosional)
2. **Depersonalization** (Sinisme/Depersonalisasi)
3. **Reduced Personal Accomplishment** (Penurunan Pencapaian Diri)

---

## 👨‍💻 Kontributor
- **Fals-Code** - *Project Lead & Architect*

## 📄 Lisensi
Proyek ini dilisensikan di bawah [MIT License](LICENSE).
