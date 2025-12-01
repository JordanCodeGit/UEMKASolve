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
        $user = Auth::user();
        assert($user !== null);
        $idPerusahaan = $user->id_perusahaan;

        return [
            // 'sometimes' berarti hanya validasi jika field itu dikirim
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    $query->where('business_id', $idPerusahaan);
                }),
            ],
            'jumlah'            => ['required', 'numeric', 'min:0', 'max:999999999999999'],
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
            'jumlah.required' => 'Nominal wajib diisi.',
            'jumlah.numeric' => 'Nominal harus berupa angka.',
            'jumlah.min' => 'Nominal tidak boleh kurang dari 0.',
            'jumlah.max' => 'Nominal transaksi tidak boleh lebih dari 15 digit.',
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
