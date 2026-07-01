# AGENTS.md

## Struktur

- Laravel app: `app/`, `routes/`, `resources/views/`
- Knowledge base: `database/seeders/BurnoutKnowledgeBaseSeeder.php`
- AI domain: `app/Services/BackwardChainingEngine.php`, `CertaintyFactorCalculator.php`
- Docs: `docs/`
- Legacy archive: `backup_native/`

## Setup

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php artisan test
```

## Konvensi

- Jangan memindahkan logika inferensi ke controller atau Blade.
- Jangan mengubah `backup_native` kecuali dokumentasi/proteksi tooling.
- Jangan commit `.env`, `vendor`, `node_modules`, `public/build`, atau secret.
- Gunakan tiga jawaban canonical: Sering, Kadang, Tidak Pernah.

## Definition of Done

- Migration, seeder, build, test, route list, dan Pint lulus.
- Algoritma goal-driven dan traceable.
- HRD dashboard empat kategori.
- Karyawan tidak dapat membaca hasil user lain.
- README/docs sesuai implementasi akhir.
