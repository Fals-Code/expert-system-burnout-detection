<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:10|unique:aturan,kode,' . ($this->aturan?->id ?? 'NULL'),
            'diagnosa_id' => 'required|exists:diagnosa,id',
            'cf_pakar' => 'required|numeric|min:0|max:1',
            'gejala_ids' => 'required|array',
            'gejala_ids.*' => 'exists:gejala,id',
            'bobot_pakar' => 'required|array',
            'bobot_pakar.*' => 'numeric|min:0|max:1',
            'prioritas' => 'required|integer|min:1',
            'is_active' => 'nullable',
            'deskripsi' => 'nullable|string',
            'min_threshold' => 'required|numeric|min:0|max:1',
        ];
    }
}
