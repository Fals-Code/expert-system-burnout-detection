# 🔥 BurnoutXpert – Sistem Pakar Deteksi Burnout Karyawan

> **Proyek UAS Kecerdasan Buatan** | D4 Teknologi Informasi | Semester 4

Sistem pakar berbasis **Backward Chaining** dan **Certainty Factor (CF)** untuk mendeteksi tingkat burnout karyawan secara dini dan memberikan rekomendasi penanganan yang tepat berdasarkan aturan gejala burnout (MBI).

---

## 🛠️ Tech Stack & Algorithm

- **Core Logic** : PHP Native (Modular Architecture)
- **Algorithms** : 
  - **Backward Chaining** (Goal-driven inference)
  - **Certainty Factor** (Handling uncertainty in diagnosis)
- **Frontend** : Vanilla CSS & JS (Custom Design System)
- **Charts** : ApexCharts.js (Data Analytics)
- **PDF Export** : html2pdf.js (Client-side PDF Generation)
- **State** : Session-based Persistent Data Store (`bx_store`)

---

## ✨ Fitur Utama

### 1. 🛡️ Keamanan (Security Hardening)
- **Rate Limiting**: Melindungi dari brute-force login (5 percobaan per menit).
- **Session Fixation Protection**: Regenerasi ID sesi otomatis setelah login.
- **CSRF Protection**: Token keamanan untuk semua operasi kritis (POST).
- **Password Hashing**: Menggunakan `password_hash()` (Argon2id/Bcrypt) untuk penyimpanan kredensial.
- **Directory Protection**: Access control via `.htaccess` untuk mengunci folder `/config` dan `/includes`.

### 2. 🧠 Mesin Diagnosis (Expert System)
- **Hybrid Inference**: Menggabungkan Backward Chaining untuk pencarian fakta dan Certainty Factor untuk akurasi persentase.
- **Wizard Deteksi**: UI interaktif yang hanya menanyakan gejala relevan berdasarkan hipotesis saat ini (efisien).
- **Analisis Tren**: Grafik fluktuasi tingkat burnout pribadi bagi karyawan.

### 3. 📊 Dashboard Khusus Role
- **Karyawan**: Deteksi mandiri, riwayat laporan, dan panduan bantuan.
- **HRD**: Monitoring kesehatan mental organisasi, statistik distribusi burnout, dan grafik tren bulanan.
- **Admin**: Manajemen Basis Pengetahuan (CRUD Gejala & Aturan) dengan validasi integritas data.

### 4. 📄 Reporting & Analytics
- **Export PDF**: Menghasilkan dokumen laporan resmi secara instan dengan format premium.
- **Notifikasi Persisten**: Sistem peringatan kritis bagi HRD jika ditemukan indikasi burnout tinggi.

---

## 📁 Struktur Proyek

```text
UAS/
├── index.php             ← Entry point & Sistem Auth
├── logout.php            ← Pembersih sesi
├── config/               ← Data Store, Mock DB, & Logic Konfigurasi
├── includes/             ← Komponen UI (Sidebar, Topbar, Security)
├── assets/               ← CSS, JS, Media, & Design Tokens
├── uploads/              ← Penyimpanan Foto Profil
├── karyawan/             ← Modul Diagnosa & Laporan
├── hrd/                  ← Modul Monitoring & Analytics
└── admin/                ← Modul Knowledge Base Management
```

---

## 🧠 Cara Kerja Backward Chaining & CF

Sistem memulai penalaran dari **Hipotesis Tertinggi (BURNOUT TINGGI)**.
1. **Goal Selection**: Sistem mencari aturan (rules) yang mengarah ke hipotesis tersebut.
2. **Fact Verification**: Sistem menanyakan gejala yang menjadi syarat aturan tersebut.
3. **Certainty Calculation**: Setiap jawaban (Sering, Kadang, Tidak Pernah) dikalikan dengan bobot pakar (Expert CF) menggunakan rumus:
   `CF[h,e] = CF[e] * CF[pakar]`
4. **CF Combination**: Jika beberapa gejala terdeteksi, nilai CF digabungkan:
   `CF_combine = CF1 + CF2 * (1 - CF1)`
5. **Decision**: Jika hipotesis tertinggi gagal/tidak cukup bukti, sistem otomatis beralih ke hipotesis berikutnya (Sedang -> Rendah).

---

## 🚀 Cara Menjalankan

1. Ekstrak folder `UAS/` ke direktori server lokal (misal: `C:\xampp\htdocs\UAS`).
2. Jalankan server (Apache atau PHP Built-in Server).
3. Buka browser: `http://localhost/UAS/`.
4. **Login Demo**:
   - **Karyawan**: `karyawan@burnoutxpert.com` / `karyawan`
   - **HRD**: `hrd@burnoutxpert.com` / `hrd`
   - **Admin**: `admin@burnoutxpert.com` / `admin`

---

## ✅ Status Proyek: FINISHED (Production Ready)

- [x] Autentikasi 3 Role (Secure)
- [x] Mesin Inferensi Backward Chaining + CF
- [x] Manajemen Basis Pengetahuan (CRUD Admin)
- [x] Export Laporan PDF
- [x] Dashboard Monitoring HRD (Analytics)
- [x] Upload Foto Profil & Keamanan Direktori

---

_Dibuat untuk keperluan akademik – Mata Kuliah Kecerdasan Buatan_
_© 2026 BurnoutXpert Team_
