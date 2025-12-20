<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    // Kode fungsi otorisasi request
    public function authorize(): bool
    {
        return true;
    }

    // Kode fungsi aturan validasi
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'], // Pastikan email ada di tabel users
        ];
    }

    // Kode fungsi pesan validasi kustom
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar.',
        ];
    }
}
