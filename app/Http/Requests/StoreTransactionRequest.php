<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Import Rule facade

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * (Kita tidak perlu method authorize() karena validasi dilakukan di rules)
     */
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware & rules
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Ambil business_id dari user yang sedang login
        $idPerusahaan = Auth::user()->id_perusahaan;

        return [
            // Validasi Kritis: category_id harus ada DAN milik business_id user ini
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    $query->where('business_id', $idPerusahaan);
                }),
            ],
            // Sesuai Class Diagram & Aturan
            'jumlah' => ['required', 'numeric', 'min:0'], // 'decimal' divalidasi sebagai 'numeric'
            'catatan' => ['nullable', 'string', 'max:1000'],
            'tanggal_transaksi' => ['required', 'date'], // Format YYYY-MM-DD
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik Anda.',
            'jumlah.required' => 'Jumlah nominal wajib diisi.',
            'jumlah.numeric' => 'Jumlah nominal harus berupa angka.',
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date' => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
