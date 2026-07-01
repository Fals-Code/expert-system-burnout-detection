# Algoritma Backward Chaining dan Certainty Factor

SanctuaryHub memakai pendekatan goal-driven. Engine menguji goal dalam urutan:

1. Risiko Burnout Tinggi
2. Risiko Burnout Sedang
3. Risiko Burnout Rendah
4. Tidak Terindikasi Burnout sebagai fallback

Untuk setiap goal, engine mengambil rule aktif yang mendukung goal tersebut. Premis rule yang belum dijawab menjadi pertanyaan berikutnya. Gejala yang sudah dijawab tidak ditanyakan ulang.

## Mapping Jawaban

- Sering = 1.0
- Kadang = 0.6
- Tidak Pernah = 0.0

## Rumus

```text
CF premis = CF user x bobot gejala
CF rule = rata-rata CF premis x CF pakar rule
```

Threshold default adalah 0.25. Nilai CF selalu dibatasi pada rentang 0 sampai 1.

## Conflict Strategy

Jika lebih dari satu rule pada goal yang sama lolos threshold, rule dengan CF tertinggi dipilih sebagai rule utama. Engine tidak menjumlahkan CF antar-rule.

## Explainability

Setiap hasil menyimpan trace: goal yang diuji, rule utama, jawaban, CF user, bobot gejala, CF premis, rata-rata premis, CF pakar, threshold, dan status rule.
