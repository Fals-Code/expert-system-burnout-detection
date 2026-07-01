<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDiagnosaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:10|unique:diagnosa,kode,'.($this->diagnosa?->id ?? 'NULL'),
            'nama' => 'required|string|max:255',
            'tingkat' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'saran' => 'required|string',
            'color' => 'nullable|string|max:20',
            'bg_light' => 'nullable|string|max:20',
        ];
    }
}
