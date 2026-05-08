# 🔥 BurnoutXpert – Sistem Pakar Deteksi Burnout Karyawan

> **Proyek UAS Kecerdasan Buatan** | D4 Teknologi Informasi | Semester 4

Sistem pakar berbasis **Backward Chaining** untuk mendeteksi tingkat burnout karyawan secara dini dan memberikan rekomendasi penanganan yang tepat berdasarkan aturan gejala burnout (MBI).

---

## 🛠️ Tech Stack
- **Language** : PHP (Native/Custom)
- **Frontend** : CSS & JS Custom + Design System
- **Database** : MySQL
- **Server** : XAMPP / PHP Built-in Server

---

## 📁 Struktur Proyek

UAS/
├── index.php                 ← Halaman Login (entry point)
├── logout.php                ← Handler logout
├── config/                   ← Konfigurasi DB & konstanta app
├── database/                 ← Skema & seed data MySQL (burnoutxpert.sql)
├── assets/                   ← CSS, JS, & Media
├── karyawan/                 ← Dashboard role Karyawan (Konsultasi & Riwayat)
├── hrd/                      ← Dashboard role HRD (Monitoring Data)
└── admin/                    ← Dashboard role Admin (Kelola Knowledge Base)


---

## 🎨 Color Palette

| Token       | Hex       | Kegunaan               |
|-------------|-----------|---------------------- |
| Primary     | `#1E3A5F` | Navbar, teks heading  |
| Accent      | `#F4845F` | CTA, tombol, highlight|
| Background  | `#FFFFFF` | Latar halaman         |
| Gray-50     | `#F8FAFB` | Background card       |

---

## 🧠 Konsep Sistem Pakar

| Komponen | Implementasi |
|---|---|
| **Metode Inferensi** | **Backward Chaining** (Penalaran mundur dari hipotesis ke fakta) |
| **Knowledge Base** | Aturan gejala burnout berdasarkan Maslach Burnout Inventory (MBI) |
| **Working Memory** | Session Management & Temporary Input |
| **User Interface** | Web Responsive (Native PHP + Custom CSS) |

**Cara Kerja Backward Chaining:**
Sistem memulai penalaran dari **hipotesis tertinggi** (misal: Burnout Berat), lalu membuktikannya secara mundur berdasarkan gejala yang dijawab pengguna. Jika hipotesis tidak terbukti, sistem turun ke hipotesis berikutnya hingga ditemukan kesimpulan yang valid.

---

## 🚀 Cara Menjalankan

1. Copy folder `UAS/` ke direktori web server (contoh: `C:\xampp\htdocs\UAS`)
2. Import `database/burnoutxpert.sql` ke phpMyAdmin.
3. Buka browser: `http://localhost/UAS/`
4. Login menggunakan akun demo (lihat di file database atau dashboard admin).

---

## 📌 Roadmap Pengembangan

- [x] Halaman Login (3 Role) & Struktur direktori
- [x] Desain sistem & skema database
- [ ] Mesin inferensi Backward Chaining
- [ ] Fitur Konsultasi & Hasil Diagnosa (Karyawan)
- [ ] Dashboard monitoring & laporan (HRD)
- [ ] Manajemen Rule & Gejala (Admin)

---
*Dibuat untuk keperluan akademik – Mata Kuliah Kecerdasan Buatan*
