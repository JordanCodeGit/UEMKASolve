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
use Illuminate\Database\Eloquent\Builder;

class TransactionController extends Controller
{
    /**
     * [PERBAIKAN] Dapatkan ID Bisnis dari relasi User -> Business.
     */
    private function getPerusahaanId()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek relasi business (HasOne)
        if ($user && $user->business) {
            return $user->business->id;
        }

        return null;
    }

    /**
     * Menampilkan daftar transaksi milik user.
     */
    public function index(Request $request): JsonResponse
    {
        $idPerusahaan = $this->getPerusahaanId();

        if (!$idPerusahaan) {
            return response()->json([], 200);
        }

        // =================================================================
        // 1. QUERY UTAMA
        // =================================================================
        $queryFiltered = Transaction::where('business_id', $idPerusahaan)
            ->with('category:id,nama_kategori,tipe,ikon');

        // A. Filter Search
        if ($request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $queryFiltered->where(function ($q) use ($search) {
                $q->where('catatan', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('nama_kategori', 'like', '%' . $search . '%');
                    });
            });
        }

        // B. Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date') && is_string($request->start_date) && is_string($request->end_date)) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate   = $request->end_date . ' 23:59:59';
            $queryFiltered->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
        }

        // C. Filter Tipe
        if ($request->filled('tipe')) {
            $tipe = $request->tipe;
            $queryFiltered->whereHas('category', function (Builder $q) use ($tipe) {
                $q->where('tipe', $tipe);
            });
        }

        // D. Filter Nominal
        if ($request->filled('min_nominal')) {
            $queryFiltered->where('jumlah', '>=', $request->min_nominal);
        }
        if ($request->filled('max_nominal')) {
            $queryFiltered->where('jumlah', '<=', $request->max_nominal);
        }


        // =================================================================
        // 2. HITUNG TOTAL SESUAI FILTER (FOOTER)
        // =================================================================
        $pemasukanFiltered = (clone $queryFiltered)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pemasukan');
        })->sum('jumlah');

        $pengeluaranFiltered = (clone $queryFiltered)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pengeluaran');
        })->sum('jumlah');

        $labaFiltered = $pemasukanFiltered - $pengeluaranFiltered;


        // =================================================================
        // 3. HITUNG SALDO ASLI (ALL TIME)
        // =================================================================
        $queryAllTime = Transaction::where('business_id', $idPerusahaan);

        $totalMasukAll = (clone $queryAllTime)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pemasukan');
        })->sum('jumlah');

        $totalKeluarAll = (clone $queryAllTime)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pengeluaran');
        })->sum('jumlah');

        $saldoReal = $totalMasukAll - $totalKeluarAll;


        // =================================================================
        // 4. AMBIL DATA PAGINASI
        // =================================================================
        $perPageInput = $request->input('per_page') ?? 10;
        $perPage = is_numeric($perPageInput) ? (int)$perPageInput : 10;
        $transactions = $queryFiltered->latest('tanggal_transaksi')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'pagination' => $transactions,
            'summary' => [
                'total_pemasukan' => $pemasukanFiltered,
                'total_pengeluaran' => $pengeluaranFiltered,
                'laba' => $labaFiltered,
                'saldo_real' => $saldoReal
            ]
        ], 200);
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        // 1. Ambil ID Bisnis dengan Benar
        $idPerusahaan = $this->getPerusahaanId();

        if (!$idPerusahaan) {
            return response()->json(['message' => 'Anda belum memiliki profil usaha.'], 400);
        }

        // 2. Validasi Manual (Selain StoreTransactionRequest)
        // Kita perlu memastikan category_id yang dikirim BENAR MILIK bisnis ini
        $request->validate([
            'jumlah' => 'required|numeric|min:0',
            'tanggal_transaksi' => 'required|date',
            'catatan' => 'nullable|string|max:255',
            'category_id' => [
                'required',
                // Rule ini memastikan kategori milik bisnis yang sedang login
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    return $query->where('business_id', $idPerusahaan);
                }),
            ],
        ]);

        $validatedData = $request->validated();

        // 3. Simpan Transaksi
        $transaction = Transaction::create([
            'business_id'       => $idPerusahaan, // [FIX] ID Bisnis Valid
            'category_id'       => $request->category_id, // Ambil dari request langsung agar aman
            'jumlah'            => $request->jumlah,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'catatan'           => $request->catatan,
        ]);

        $transaction->load('category');

        return response()->json($transaction, 201);
    }

    /**
     * Menampilkan satu transaksi spesifik.
     */
    public function show($id): JsonResponse
    {
        $transaction = Transaction::find($id);

        // Cek kepemilikan
        if (!$transaction || $transaction->business_id !== $this->getPerusahaanId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }
        return response()->json($transaction, 200);
    }

    /**
     * Memperbarui transaksi.
     */
    public function update(UpdateTransactionRequest $request, $id): JsonResponse
    {
        $transaction = Transaction::find($id);

        if (!$transaction || $transaction->business_id !== $this->getPerusahaanId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        // Validasi kepemilikan kategori saat update juga penting
        $idPerusahaan = $this->getPerusahaanId();
        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($idPerusahaan) {
                    return $query->where('business_id', $idPerusahaan);
                }),
            ],
        ]);

        $validatedData = $request->validated();

        $transaction->update([
            'category_id'       => $request->category_id,
            'jumlah'            => $request->jumlah,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'catatan'           => strip_tags($request->catatan ?? ''),
        ]);

        $transaction->load('category');

        return response()->json($transaction, 200);
    }

    /**
     * Menghapus transaksi.
     */
    public function destroy($id): JsonResponse
    {
        $transaction = Transaction::find($id);

        if (!$transaction || $transaction->business_id !== $this->getPerusahaanId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        $transaction->delete();
        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }
}
