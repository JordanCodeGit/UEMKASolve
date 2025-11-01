<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kategori' => ['sometimes', 'required', 'string', 'max:255'],
            'tipe' => ['sometimes', 'required', Rule::in(['pemasukan', 'pengeluaran'])],
            'ikon' => ['sometimes', 'nullable', 'string', 'max:100'], // <-- TAMBAHKAN BARIS INI
        ];
    }

    public function messages(): array
    {
        // Pesan error sama dengan StoreCategoryRequest
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'tipe.required' => 'Tipe kategori (pemasukan/pengeluaran) wajib diisi.',
            'tipe.in' => 'Tipe kategori harus "pemasukan" atau "pengeluaran".',
            'ikon.string' => 'Ikon harus berupa teks.',
            'ikon.max' => 'Nama ikon tidak boleh lebih dari 100 karakter.',
        ];
    }
}
