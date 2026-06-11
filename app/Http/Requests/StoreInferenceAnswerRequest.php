<?php

namespace App\Http\Requests;

use App\Models\InferenceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInferenceAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = InferenceSession::query()->find(
            $this->integer('session_id')
        );

        return $session !== null
            && (int) $session->user_id === (int) $this->user()?->id
            && ($this->user()?->isKaryawan() ?? false);
    }

    public function rules(): array
    {
        return [
            'session_id' => [
                'required',
                'integer',
                'exists:inference_sessions,id',
            ],
            'item_code' => [
                'required',
                'string',
                'exists:cbi_items,code',
            ],
            'answer_key' => [
                'required',
                'string',
                Rule::in(array_keys(config('cbi.response_options', []))),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $session = InferenceSession::query()->find(
                $this->integer('session_id')
            );

            if (! $session) {
                return;
            }

            if ($session->status !== InferenceSession::STATUS_IN_PROGRESS) {
                $validator->errors()->add(
                    'session_id',
                    'Sesi wawancara ini sudah selesai atau tidak aktif.'
                );
            }

            if ($session->current_question_code !== $this->string('item_code')->toString()) {
                $validator->errors()->add(
                    'item_code',
                    'Pertanyaan yang dikirim tidak sesuai dengan fakta yang sedang diminta engine.'
                );
            }
        });
    }

    public function answerKey(): string
    {
        return strtoupper(
            trim((string) $this->validated('answer_key'))
        );
    }
}
