<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    // Kode fungsi otorisasi request
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware
    }

    // Kode fungsi aturan validasi
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'nama_usaha' => ['sometimes', 'required', 'string', 'max:255'],

            // [TAMBAHKAN INI] Validasi untuk logo
            'logo' => [
                'nullable', // Boleh kosong
                'image',    // Harus file gambar
                'mimes:png,jpg,jpeg', // Format file
                'max:2048'  // Ukuran maks 2MB (2048 KB)
            ],
        ];
    }
}
