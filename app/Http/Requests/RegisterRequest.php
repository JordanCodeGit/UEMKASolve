<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password; // Import rule Password

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Kita izinkan siapa saja mencoba registrasi.
     */
    // Kode fungsi otorisasi request
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // Kode fungsi aturan validasi
    public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => [
            'required',
            // HAPUS 'confirmed', // Hapus aturan konfirmasi
            Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    // ->symbols() // Sesuaikan aturan kekuatan password jika perlu
        ],
        // HAPUS 'nama_usaha' => ['required', 'string', 'max:255'], // Hapus aturan nama usaha
    ];
}

    /**
     * (Opsional) Custom error messages in Indonesian.
     *
     * @return array
     */
    // Kode fungsi pesan validasi kustom
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'nama_usaha.required' => 'Nama usaha wajib diisi.',
        ];
    }
}
