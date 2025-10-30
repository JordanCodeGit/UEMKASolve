<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'], // Token dari link email
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'password' => [
                'required',
                'confirmed', // Butuh password_confirmation
                Password::min(8)->mixedCase()->numbers() // Aturan password kuat
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token reset password wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
        ];
    }
}
