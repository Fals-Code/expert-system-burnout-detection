# Traceability

| Kebutuhan | Implementasi | File | Test | Status |
| --- | --- | --- | --- | --- |
| Nama canonical SanctuaryHub | Branding app, env, docs | `.env.example`, README, views | Feature smoke | Sesuai |
| Laravel tetap utama | Tidak rollback ke PHP Native | seluruh app Laravel | CI/test | Sesuai |
| Goal-driven Backward Chaining | Engine menguji Tinggi, Sedang, Rendah | `BackwardChainingEngine` | `ExpertSystemTest` | Sesuai |
| Mapping jawaban 3 opsi | Sering/Kadang/Tidak Pernah | `CertaintyFactorCalculator`, request, Blade | `ExpertSystemServiceTest` | Sesuai |
| Rumus CF PDF | Avg premis x CF pakar | `CertaintyFactorCalculator` | Unit/Feature | Sesuai |
| R001 PDF | G001,G006,G009,G014,G017, CF 0.95 | Seeder | `DiagnosisTest` | Sesuai |
| 20 gejala, 6 rule | Seeder final | `BurnoutKnowledgeBaseSeeder` | Migration/seed | Sesuai |
| HRD empat kategori | Tinggi/Sedang/Rendah/Tidak | HRD controller/views | Feature/manual | Sesuai |
| HRD privacy | Agregat, tanpa jawaban mentah | HRD report, notification | Feature/manual | Sesuai |
| backup_native legacy | Tidak runtime, excluded Pint | `pint.json`, README | Pint | Sesuai |

## Keputusan Perbedaan PDF

- PDF beberapa kali menulis `SancturyHub`; aplikasi memakai ejaan benar `SanctuaryHub`.
- PDF menyebut PHP Native; repository final tetap Laravel.
- PDF hanya menampilkan contoh lengkap R001. R002-R006 dilengkapi dari knowledge base Laravel yang ada, lalu disederhanakan menjadi 6 rule dan didokumentasikan.
- PDF menampilkan monitoring HRD yang dapat mengarah ke detail individu. Implementasi final membatasi HRD ke agregat untuk privasi.
