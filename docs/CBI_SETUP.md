# Copenhagen Burnout Inventory (CBI)

## Struktur instrumen

Implementasi menggunakan 19 item dalam tiga dimensi kontinu:

- Personal Burnout (`PB`): 6 item
- Work-related Burnout (`WB`): 7 item
- Client-related Burnout (`CB`): 6 item

Teks item Bahasa Indonesia disimpan langsung sebagai plain text di tabel `cbi_items`. Tidak ada encrypted cast, berkas lisensi privat, atau importer instrumen berbayar.

Teks Bahasa Indonesia pada repositori ini merupakan terjemahan operasional dari versi bahasa Inggris Kristensen dkk. (2005). Jangan menyebutnya sebagai versi Indonesia yang tervalidasi secara formal sebelum dilakukan proses penerjemahan balik dan validasi lintas budaya pada populasi sasaran.

## Instalasi

```bash
php artisan migrate
php artisan db:seed --class=CbiInstrumentSeeder
php artisan optimize:clear
```

## Skala dan perhitungan

| Pilihan | Nilai |
|---|---:|
| Selalu | 100 |
| Sering | 75 |
| Kadang-kadang | 50 |
| Jarang | 25 |
| Tidak pernah | 0 |

Nilai setiap dimensi adalah rata-rata aritmetika seluruh item dalam dimensi tersebut.

`CBI-WB-07` adalah item positif dan dihitung terbalik:

```text
skor normalisasi = 100 - skor mentah
```

Apabila satu item saja tidak terisi, dimensi terkait dan assessment keseluruhan berstatus `INSUFFICIENT_DATA`. Skor parsial tidak diterbitkan.

## Client-related Burnout

Istilah penerima layanan mencakup pelanggan, pasien, siswa, pengguna, warga, atau pihak internal yang menerima hasil pekerjaan. Jangan menggantinya secara otomatis menjadi rekan kerja tanpa validasi konstruk untuk konteks organisasi yang digunakan.

## Pengujian

```bash
php artisan test --filter=CbiAssessmentTest
```

## Batas penggunaan

Skor CBI merupakan keluaran skrining dan riset kontinu, bukan diagnosis klinis. Hasil tidak boleh menjadi satu-satunya dasar keputusan ketenagakerjaan.
