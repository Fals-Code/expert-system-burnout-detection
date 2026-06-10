<?php

namespace App\Http\Requests;

use App\Models\Gejala;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDeteksiRequest extends FormRequest
{
    /**
     * Hanya karyawan yang boleh mengirim jawaban deteksi.
     */
    public function authorize(): bool
    {
        return $this->user()?->isKaryawan() ?? false;
    }

    /**
     * Validasi input berbasis gejala_id untuk endpoint/testing yang memakai array ID.
     */
    public function rules(): array
    {
        return [
            'gejala_id' => ['sometimes', 'array'],
            'gejala_id.*' => ['integer', 'exists:gejala,id'],
        ];
    }

    /**
     * Validasi tambahan untuk form wizard produksi yang memakai kode gejala sebagai nama field.
     * Contoh: G01 => Ya, G02 => Tidak.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allowedAnswers = [
                'Sangat Sering',
                'Sering',
                'Kadang',
                'Jarang',
                'Sangat Jarang',
                'Tidak',
                'Ya',
                'Pasti Ya',
                'Hampir Pasti',
                'Mungkin',
                'Ragu-ragu',
                'Sedikit',
                'Tidak Pernah',
            ];

            $answerInputs = collect($this->except(['_token', 'gejala_id']))
                ->filter(fn ($value, $key) => str_starts_with((string) $key, 'G'));

            if ($answerInputs->isEmpty()) {
                return;
            }

            $existingCodes = Gejala::query()
                ->whereIn('kode', $answerInputs->keys()->all())
                ->pluck('kode')
                ->all();

            foreach ($answerInputs as $kode => $value) {
                if (!in_array($kode, $existingCodes, true)) {
                    $validator->errors()->add($kode, "Kode gejala {$kode} tidak ditemukan di basis pengetahuan.");
                    continue;
                }

                if (!in_array($value, $allowedAnswers, true)) {
                    $validator->errors()->add($kode, "Jawaban untuk {$kode} tidak valid.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'gejala_id.array' => 'Data gejala harus berbentuk array.',
            'gejala_id.*.integer' => 'ID gejala harus berupa angka.',
            'gejala_id.*.exists' => 'Terdapat ID gejala yang tidak ditemukan di basis pengetahuan.',
        ];
    }
}
