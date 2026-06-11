# MBI-GS Setup

Implementasi ini tidak menyimpan teks 16 item MBI-GS di repositori publik karena instrumen tersebut berlisensi.

## Instalasi database

```bash
php artisan migrate
php artisan db:seed --class=MbiGsInstrumentSeeder
```

Seeder membuat tepat 16 slot aktif:

- EX: 5 item
- CY: 5 item
- PE: 6 item

Seeder juga menonaktifkan seluruh rule custom lama agar engine CF tidak lagi digunakan untuk skrining baru.

## Impor konten berlisensi

Simpan data resmi yang diperoleh organisasi pada:

```text
storage/app/private/mbi-gs.json
```

Format JSON:

```json
{
  "items": [
    {
      "code": "MBIGS-EX-01",
      "dimension": "EX",
      "position": 1,
      "prompt_text": "[TEKS ITEM RESMI BERLISENSI]",
      "source_item_reference": "[REFERENSI ITEM PADA PAKET LISENSI]"
    }
  ]
}
```

Berkas harus berisi tepat 16 item dan kode harus cocok dengan slot yang dibuat seeder. Jalankan:

```bash
php artisan mbi:import-licensed storage/app/private/mbi-gs.json
```

`prompt_text` disimpan menggunakan encrypted cast Laravel.

## Profil kategoris

Secara default aplikasi hanya menampilkan tiga skor kontinu. Profil kategoris tetap nonaktif sampai ambang dari manual berlisensi atau norma organisasi tervalidasi tersedia.

```env
MBI_PROFILE_CLASSIFICATION_ENABLED=false
MBI_EX_HIGH_THRESHOLD=
MBI_CY_HIGH_THRESHOLD=
MBI_PE_LOW_THRESHOLD=
```

Jangan mengisi threshold dengan tebakan atau cut-off internet.

## Pengujian

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

## Disclaimer

Hasil MBI-GS digunakan untuk riset dan pengembangan organisasi. Hasil bukan diagnosis klinis atau medis dan tidak boleh menjadi satu-satunya dasar keputusan ketenagakerjaan.
