<?php

namespace App\Http\Requests;

use App\Models\CbiItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCbiAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKaryawan() ?? false;
    }

    public function rules(): array
    {
        return [
            'responses' => ['required', 'array', 'size:19'],
            'responses.*' => [
                'required',
                'string',
                Rule::in(array_keys(config('cbi.response_options', []))),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $activeCodes = CbiItem::query()
                ->where('is_active', true)
                ->pluck('code')
                ->sort()
                ->values();

            $expectedCount = (int) config('cbi.instrument.expected_item_count', 19);

            if ($activeCodes->count() !== $expectedCount) {
                $validator->errors()->add(
                    'responses',
                    "Instrumen CBI belum dikonfigurasi dengan tepat: diperlukan {$expectedCount} item aktif."
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
                    'Data tidak mencukupi. Item yang belum dijawab: '
                        .$missing->implode(', ')
                        .'.'
                );
            }

            if ($unknown->isNotEmpty()) {
                $validator->errors()->add(
                    'responses',
                    'Terdapat kode item yang tidak dikenali: '
                        .$unknown->implode(', ')
                        .'.'
                );
            }
        });
    }

    public function validatedResponses(): array
    {
        return collect($this->validated('responses', []))
            ->map(fn (mixed $answer): string => strtoupper(trim((string) $answer)))
            ->all();
    }

    public function messages(): array
    {
        return [
            'responses.required' => 'Seluruh 19 item CBI wajib dijawab.',
            'responses.size' => 'Data tidak mencukupi: CBI memerlukan tepat 19 respons.',
            'responses.*.required' => 'Setiap item CBI wajib dijawab.',
            'responses.*.in' => 'Pilihan jawaban CBI tidak valid.',
        ];
    }
}
