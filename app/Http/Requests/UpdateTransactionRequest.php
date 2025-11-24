<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $idPerusahaan = Auth::user()->id_perusahaan;

        return [
            // 'sometimes' berarti hanya validasi jika field itu dikirim
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    $query->where('business_id', $idPerusahaan);
                }),
            ],
            'jumlah'            => ['required', 'numeric', 'min:1'],
            'catatan'           => ['nullable', 'string', 'max:255'],
            'tanggal_transaksi' => ['required', 'date'], 
        ];
    }

    public function messages(): array
    {
        // Pesan error sama dengan StoreTransactionRequest
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik Anda.',
            'jumlah.required' => 'Jumlah nominal wajib diisi.',
            'jumlah.numeric' => 'Jumlah nominal harus berupa angka.',
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
