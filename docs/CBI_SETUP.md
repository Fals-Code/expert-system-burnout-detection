# CBI Backward Chaining

## Metodologi

Aplikasi memakai 19 indikator CBI sebagai fakta untuk rule base kustom. Aplikasi tidak menghitung rata-rata dimensi CBI resmi.

Keluaran harus disebut **inferensi rule-based berbasis indikator CBI**, bukan skor CBI resmi atau diagnosis klinis.

## Instalasi

```bash
php artisan migrate
php artisan db:seed --class=CbiInstrumentSeeder
php artisan db:seed --class=CbiBackwardChainingRuleSeeder
php artisan optimize:clear
```

## Konversi jawaban

| Jawaban | Nilai | Boolean |
|---|---:|---|
| Selalu | 100 | TRUE |
| Sering | 75 | TRUE |
| Kadang-kadang | 50 | FALSE |
| Jarang | 25 | FALSE |
| Tidak pernah | 0 | FALSE |

## Rule utama

```text
BURNOUT_PERSONAL
K_OF_N: minimal 4 dari 6 indikator PB = TRUE

BURNOUT_KERJA
K_OF_N: minimal 5 dari 7 premis WB terpenuhi

BURNOUT_CLIENT
K_OF_N: minimal 4 dari 6 indikator CB = TRUE

BURNOUT_KERJA_KRONIS
ALL: BURNOUT_PERSONAL dan BURNOUT_KERJA terbukti

KONDISI_STABIL
ALL: BURNOUT_PERSONAL, BURNOUT_KERJA, dan BURNOUT_CLIENT tidak terbukti
```

`CBI-WB-07` merupakan item positif. Rule mengharapkan boolean FALSE pada item tersebut.

Semua ambang adalah keputusan knowledge engineering proyek, bukan cut-off resmi CBI.

## Alur engine

1. Engine memulai dari goal aktif.
2. Rule yang menghasilkan goal dicari berdasarkan prioritas.
3. Premis goal diperiksa secara rekursif.
4. Premis fakta dibaca dari `inference_answers`.
5. Fakta yang belum tersedia dikembalikan sebagai satu pertanyaan.
6. K-of-N berhenti jika ambang tercapai atau tidak mungkin lagi tercapai.
7. `visitedGoals` mencegah recursive loop.
8. Jejak proses disimpan pada `inference_traces`.

## Pengujian

```bash
php artisan test --filter=RecursiveBackwardChainingTest
```

Teks Bahasa Indonesia merupakan terjemahan operasional. Untuk penelitian formal, tetap diperlukan validasi bahasa dan lintas budaya.
