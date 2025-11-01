<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

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
     * [MODIFIKASI] Ditambahkan filter lengkap.
     */
    public function index(Request $request): JsonResponse
    {
        // Validasi input filter
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'search' => 'nullable|string|max:100',
            'tipe' => ['nullable', Rule::in(['pemasukan', 'pengeluaran'])],
            'category_id' => 'nullable|integer|exists:categories,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $businessId = $this->getBusinessId();
        $perPage = $request->input('per_page', 15); // Default 15 data per halaman

        $query = Transaction::where('business_id', $businessId)
                            ->with('category:id,nama_kategori,tipe') // Eager load
                            ->latest('tanggal_transaksi'); // Urutkan terbaru

        // --- Terapkan Filter ---

        // 1. Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
        }

        // 2. Filter Search (Deskripsi / Catatan)
        if ($request->filled('search')) {
             $query->where(function ($q) use ($request) {
                // Di Class Diagram, 'catatan' adalah nama kolom, 'deskripsi' tidak ada.
                // Jika FE Dev Anda mengirim 'deskripsi', Anda bisa ganti 'catatan' -> 'deskripsi'
                $q->where('catatan', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function ($catQuery) use ($request) {
                      $catQuery->where('nama_kategori', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // 3. Filter Tipe (Pemasukan / Pengeluaran)
        if ($request->filled('tipe')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        // 4. Filter Kategori ID (Spesifik)
        if ($request->filled('category_id')) {
            // Validasi tambahan untuk memastikan category_id milik user (Otorisasi)
            $categoryExists = Auth::user()->business->categories()
                                ->where('id', $request->category_id)
                                ->exists();

            if ($categoryExists) {
                $query->where('category_id', $request->category_id);
            } else {
                // Jika user mencoba filter kategori yg bukan miliknya, kembalikan data kosong
                $query->where('id', -1); // Query palsu
            }
        }

        // --- Akhir Filter ---

        $transactions = $query->paginate($perPage)->withQueryString(); // Bawa query param di link pagination

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
