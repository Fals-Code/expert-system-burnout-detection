<?php

namespace App\Http\Requests;

use App\Models\MbiItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMbiAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKaryawan() ?? false;
    }

    public function rules(): array
    {
        return [
            'responses' => ['required', 'array', 'size:16'],
            'responses.*' => ['required', 'integer', 'between:0,6'],
            'safety' => ['sometimes', 'array'],
            'safety.G14' => ['nullable', 'integer', 'between:0,6'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $activeCodes = MbiItem::query()
                ->where('is_active', true)
                ->pluck('code')
                ->sort()
                ->values();

            if ($activeCodes->count() !== (int) config('mbi.instrument.expected_item_count', 16)) {
                $validator->errors()->add(
                    'responses',
                    'Instrumen belum dikonfigurasi dengan tepat: diperlukan tepat 16 item aktif.'
                );

                return;
            }

            $submittedCodes = collect(array_keys($this->input('responses', [])))
                ->sort()
                ->values();

            $missing = $activeCodes->diff($submittedCodes)->values();
            $unknown = $submittedCodes->diff($activeCodes)->values();

            if ($missing->isNotEmpty()) {
                $validator->errors()->add(
                    'responses',
                    'Data tidak mencukupi. Item yang belum dijawab: '.$missing->implode(', ').'.'
                );
            }

            if ($unknown->isNotEmpty()) {
                $validator->errors()->add(
                    'responses',
                    'Terdapat kode item yang tidak dikenali: '.$unknown->implode(', ').'.'
                );
            }
        });
    }

    public function validatedResponses(): array
    {
        return collect($this->validated('responses', []))
            ->map(fn (mixed $score): int => (int) $score)
            ->all();
    }

    public function redFlagResponse(): ?int
    {
        $value = $this->input('safety.G14');

        return $value === null || $value === '' ? null : (int) $value;
    }

    public function messages(): array
    {
        return [
            'responses.required' => 'Semua item MBI-GS wajib dijawab.',
            'responses.size' => 'Data tidak mencukupi: MBI-GS memerlukan tepat 16 respons.',
            'responses.*.integer' => 'Setiap respons harus berupa angka 0 sampai 6.',
            'responses.*.between' => 'Setiap respons harus berada pada rentang 0 sampai 6.',
            'safety.G14.between' => 'Respons pemeriksaan keselamatan harus berada pada rentang 0 sampai 6.',
        ];
    }
}
