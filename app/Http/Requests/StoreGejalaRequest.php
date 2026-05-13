<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreGejalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:10|unique:gejala,kode,' . ($this->gejala?->id ?? 'NULL'),
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:fisik,emosional,perilaku,kognitif',
            'bobot' => 'required|numeric|min:0|max:1',
        ];
    }
}
