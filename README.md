# 🧠 Expert System — Burnout Detection
> Sistem Pakar Deteksi Tingkat Burnout pada Karyawan

Implementasi **Sistem Pakar (Expert System)** untuk mendeteksi tingkat 
burnout karyawan menggunakan metode inferensi **Backward Chaining** 
berbasis Web. Proyek UAS Mata Kuliah Kecerdasan Buatan.

## 🛠️ Tech Stack
- **Backend** : CodeIgniter 4 (PHP)
- **Frontend** : Bootstrap 5 + Bootstrap Icons
- **Database** : MySQL
- **Server**   : XAMPP

## 🧩 Komponen Expert System
| Komponen | Implementasi |
|---|---|
| Knowledge Base | Aturan gejala burnout (MBI) |
| Inference Engine | Backward Chaining |
| Working Memory | Session CI4 |
| User Interface | Web (PHP + Bootstrap 5) |

## 👤 Role Pengguna
| Role | Akses |
|---|---|
| Karyawan | Konsultasi deteksi burnout, riwayat hasil |
| HRD | Monitoring data burnout karyawan |
| Admin | Kelola knowledge base & rule |

## ⚙️ Cara Kerja Backward Chaining
Sistem memulai penalaran dari **hipotesis tertinggi** 
(Burnout Sangat Tinggi), lalu membuktikannya secara mundur 
berdasarkan gejala yang dijawab pengguna. Jika hipotesis 
tidak terbukti, sistem turun ke hipotesis berikutnya hingga 
ditemukan kesimpulan yang valid.
