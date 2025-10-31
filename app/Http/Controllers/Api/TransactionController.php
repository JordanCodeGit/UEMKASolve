<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response; // Untuk respons 204

class TransactionController extends Controller
{
    /**
     * Dapatkan business_id milik user yang terotentikasi.
     * Ini adalah inti dari Aturan Otorisasi Data Anda.
     */
    private function getBusinessId(): int
    {
        return Auth::user()->business->id;
    }

    /**
     * Menampilkan daftar transaksi milik user.
     * Sesuai Aturan Otorisasi: difilter berdasarkan business_id.
     */
    public function index(Request $request): JsonResponse
    {
        $businessId = $this->getBusinessId();

        $query = Transaction::where('business_id', $businessId)
                            ->with('category:id,nama_kategori,tipe') // Hanya ambil kolom yg perlu dari relasi
                            ->latest('tanggal_transaksi'); // Urutkan

        // (Opsional) Tambahkan filter tanggal dari request (seperti di Dashboard)
        if ($request->has('start_date') && $request->has('end_date')) {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);
            $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
        }

        // (Opsional) Tambahkan filter search query
        if ($request->has('search')) {
             $query->where(function ($q) use ($request) {
                $q->where('catatan', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function ($catQuery) use ($request) {
                      $catQuery->where('nama_kategori', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $transactions = $query->paginate(15); // Ambil 15 data per halaman

        return response()->json($transactions, 200);
    }

    /**
     * Menyimpan transaksi baru.
     * Validasi & Otorisasi ditangani oleh StoreTransactionRequest.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        // Data sudah divalidasi (termasuk 'category_id' adalah milik user)
        $validatedData = $request->validated();

        // Tambahkan business_id milik user ke data
        $validatedData['business_id'] = $this->getBusinessId();

        // Buat transaksi
        $transaction = Transaction::create($validatedData);

        // Muat relasi kategori untuk respons
        $transaction->load('category:id,nama_kategori,tipe');

        return response()->json($transaction, 201); // 201 Created
    }

    /**
     * Menampilkan satu transaksi spesifik.
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        // Otorisasi: Pastikan transaksi ini milik user yang login
        if ($transaction->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        $transaction->load('category:id,nama_kategori,tipe');
        return response()->json($transaction, 200);
    }

    /**
     * Memperbarui transaksi.
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        // Otorisasi: Pastikan transaksi ini milik user yang login
        if ($transaction->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        // Data sudah divalidasi (termasuk 'category_id' baru jika ada)
        $validatedData = $request->validated();

        $transaction->update($validatedData);

        $transaction->load('category:id,nama_kategori,tipe');
        return response()->json($transaction, 200);
    }

    /**
     * Menghapus transaksi (Soft Delete).
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function destroy(Transaction $transaction): Response
    {
        // Otorisasi: Pastikan transaksi ini milik user yang login
        if ($transaction->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        // Lakukan Soft Delete (sesuai Aturan #2)
        $transaction->delete();

        return response()->noContent(); // 204 No Content
    }
}
