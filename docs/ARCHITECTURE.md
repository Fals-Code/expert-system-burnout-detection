# Arsitektur

SanctuaryHub tetap memakai Laravel sebagai arsitektur utama.

## Lapisan

- Routes dan middleware: `routes/web.php`, `RoleMiddleware`
- Controller: orkestrasi request, session, redirect, authorization
- Service domain AI: `BackwardChainingEngine`, `CertaintyFactorCalculator`, `ExpertSystemService`
- Model: `User`, `Diagnosa`, `Gejala`, `Aturan`, `Konsultasi`
- View: Blade untuk Karyawan, HRD, Admin
- Data awal: seeder Laravel

PDF akademik menyebut PHP Native pada beberapa bagian. Implementasi final mempertahankan Laravel karena repository saat ini adalah Laravel dan user melarang rollback ke PHP Native.

## Data Flow Deteksi

Karyawan membuka wizard, engine memilih gejala dari goal aktif, jawaban disimpan di session, engine mengevaluasi rule, hasil disimpan ke `konsultasi`, lalu trace ditampilkan dan dapat dibuka pada halaman hasil.
