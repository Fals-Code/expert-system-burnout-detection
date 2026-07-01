# Security dan Privasi

## Proteksi

- Login memakai session regeneration dan rate limiting.
- Route dipisah berdasarkan role.
- Karyawan hanya dapat membaca hasil miliknya sendiri.
- HRD melihat data agregat dan tidak mendapat jawaban mentah.
- Admin mengelola knowledge base dengan validasi dan audit trail.
- Notifikasi dirender sebagai teks escaped, bukan HTML mentah.
- Service worker hanya cache aset statis.

## Production

Gunakan `APP_DEBUG=false`, HTTPS, cookie secure, database production terpisah, dan jangan memakai akun demo.

## Disclaimer

SanctuaryHub adalah alat skrining awal, bukan diagnosis medis.
