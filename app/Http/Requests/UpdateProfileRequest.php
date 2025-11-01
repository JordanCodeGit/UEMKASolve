<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            // Validasi untuk tabel 'users'
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            // Validasi email: harus unik, TAPI abaikan ID user saat ini
            'email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($userId),
            ],

            // Validasi untuk tabel 'businesses'
            'nama_usaha' => ['sometimes', 'required', 'string', 'max:255'],
            // 'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Opsional: jika ada upload logo
        ];
    }
}
