<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business->id;

        return [
            // 'sometimes' berarti hanya validasi jika field itu dikirim
            'category_id' => [
                'sometimes', // Opsional
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($businessId) {
                    $query->where('business_id', $businessId);
                }),
            ],
            'jumlah' => ['sometimes', 'required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'tanggal_transaksi' => ['sometimes', 'required', 'date_format:Y-m-d'],
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
