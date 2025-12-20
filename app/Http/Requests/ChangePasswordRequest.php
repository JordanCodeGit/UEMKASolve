<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
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
            // Validasi password saat ini
            'current_password' => [
                'required',
                // Cek apakah password lama cocok dengan yang di database
                function ($attribute, $value, $fail) {
                    $user = $this->user();
                    assert($user !== null);
                    if (!Hash::check($value, (string)$user->password)) {
                        $fail('Password Anda saat ini salah.');
                    }
                },
            ],
            // Validasi password baru
            'password' => [
                'required',
                'confirmed', // Butuh password_confirmation
                Password::min(8)->mixedCase()->numbers()
            ],
        ];
    }

    // Kode fungsi pesan validasi kustom
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
