<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule facade

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:255'],
            // Validasi Enum (sesuai Aturan #3)
            'tipe' => ['required', Rule::in(['pemasukan', 'pengeluaran'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'tipe.required' => 'Tipe kategori (pemasukan/pengeluaran) wajib diisi.',
            'tipe.in' => 'Tipe kategori harus "pemasukan" atau "pengeluaran".',
        ];
    }
}
