# Testing

Jalankan:

```powershell
php artisan test
```

Cakupan utama:

- Mapping jawaban CF
- Rumus rata-rata premis x CF pakar
- Goal priority
- Fallback Tidak Terindikasi
- Alur hasil deteksi
- Route authorization
- Backup/restore knowledge base
- Dashboard dan rekomendasi aman

CI menjalankan Composer install, migration/test SQLite, `npm ci`, dan `npm run build`.
