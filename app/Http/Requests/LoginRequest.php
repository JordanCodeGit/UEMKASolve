<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Izinkan siapa saja mencoba login
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],      // Email wajib diisi dan formatnya benar
            'password' => ['required', 'string'],            // Password wajib diisi
            'remember' => ['nullable', 'boolean'],           // Remember me checkbox (opsional)
            // 'device_name' => ['required', 'string'], // Opsional: nama perangkat untuk token Sanctum
        ];
    }

     /**
     * (Opsional) Custom error messages in Indonesian.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            // 'device_name.required' => 'Nama perangkat wajib diisi.',
        ];
    }
}
