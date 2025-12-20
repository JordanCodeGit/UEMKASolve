<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    // Kode fungsi otorisasi request
    public function authorize(): bool
    {
        return Auth::check();
    }

    // Kode fungsi aturan validasi
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // [FIX] Ambil ID dari relasi business (bukan kolom id_perusahaan)
        $idPerusahaan = $user->business ? $user->business->id : null;

        return [
            'category_id' => [
                'required',
                'integer',
                // Pastikan kategori milik bisnis user ini
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    return $query->where('business_id', $idPerusahaan);
                }),
            ],
            'jumlah'            => ['required', 'numeric', 'min:0', 'max:999999999999999'],
            'catatan'           => ['nullable', 'string', 'max:1000'],
            'tanggal_transaksi' => ['required', 'date'],
        ];
    }

    // Kode fungsi pesan validasi kustom
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Error: Kategori yang dipilih tidak valid atau bukan milik Anda.',
            'jumlah.required'      => 'Nominal wajib diisi.',
            'jumlah.numeric'       => 'Nominal harus berupa angka.',
            'jumlah.min'           => 'Nominal tidak boleh kurang dari 0.',
            'jumlah.max'           => 'Nominal transaksi terlalu besar.',
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date'     => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
