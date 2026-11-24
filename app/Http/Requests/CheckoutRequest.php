<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_promo' => strtoupper(trim((string) $this->input('kode_promo'))),
            'nama_pelanggan' => trim((string) $this->input('nama_pelanggan')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => ['required', 'string', 'max:100'],
            'table_id' => ['nullable', 'integer', Rule::exists('tables', 'id')],
            'metode_bayar' => ['required', Rule::in(Order::METHODS)],
            'kode_promo' => ['nullable', 'string', 'max:40'],
            'catatan' => ['nullable', 'string', 'max:800'],
        ];
    }

    public function attributes(): array
    {
        return ['nama_pelanggan' => 'nama pelanggan', 'table_id' => 'nomor meja', 'metode_bayar' => 'metode pembayaran', 'kode_promo' => 'kode promo'];
    }
}
