<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware role:owner
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'nama' => ['required', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options_text' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return ['category_id' => 'kategori', 'nama' => 'nama menu', 'harga' => 'harga', 'stok' => 'stok', 'foto' => 'foto menu'];
    }
}
