<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TransactionController extends Controller
{
    /**
     * Helper: Ambil ID Bisnis (Support relasi 'business' atau 'perusahaan')
     */
    private function getPerusahaanId()
    {
        $user = Auth::user();
        if (!$user) return null;

        // Cek relasi 'business' (Standard)
        if ($user->relationLoaded('business') || $user->business) {
            return $user->business->id;
        }

        // Cek relasi 'perusahaan' (Sesuai AuthController Anda)
        if ($user->relationLoaded('perusahaan') || $user->perusahaan) {
            return $user->perusahaan->id;
        }

        return null;
    }

    /**
     * Menampilkan daftar transaksi
     */
    public function index(Request $request): JsonResponse
    {
        $idPerusahaan = $this->getPerusahaanId();

        if (!$idPerusahaan) {
            return response()->json([], 200);
        }

        $queryFiltered = Transaction::where('business_id', $idPerusahaan)
            ->with('category:id,nama_kategori,tipe,ikon');

        // Filter Search
        if ($request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $queryFiltered->where(function ($q) use ($search) {
                $q->where('catatan', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('nama_kategori', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $queryFiltered->whereBetween('tanggal_transaksi', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter Tipe
        if ($request->filled('tipe')) {
            $tipe = $request->tipe;
            $queryFiltered->whereHas('category', fn($q) => $q->where('tipe', $tipe));
        }

        // Filter Nominal
        if ($request->filled('min_nominal')) $queryFiltered->where('jumlah', '>=', $request->min_nominal);
        if ($request->filled('max_nominal')) $queryFiltered->where('jumlah', '<=', $request->max_nominal);

        // Hitung Summary
        $summaryQuery = clone $queryFiltered;
        $pemasukan = (clone $summaryQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaran = (clone $summaryQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        // Saldo Real (All Time)
        $allTimeQuery = Transaction::where('business_id', $idPerusahaan);
        $totalMasuk = (clone $allTimeQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $totalKeluar = (clone $allTimeQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        $transactions = $queryFiltered->latest('tanggal_transaksi')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return response()->json([
            'pagination' => $transactions,
            'summary' => [
                'total_pemasukan' => $pemasukan,
                'total_pengeluaran' => $pengeluaran,
                'laba' => $pemasukan - $pengeluaran,
                'saldo_real' => $totalMasuk - $totalKeluar
            ]
        ], 200);
    }

    /**
     * Simpan transaksi
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $idPerusahaan = $this->getPerusahaanId();
        if (!$idPerusahaan) return response()->json(['message' => 'Profil usaha belum diset.'], 400);

        // Validasi Kategori Milik Bisnis Ini
        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('business_id', $idPerusahaan)),
            ],
        ]);

        $transaction = Transaction::create([
            'business_id'       => $idPerusahaan,
            'category_id'       => $request->category_id,
            'jumlah'            => $request->jumlah,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'catatan'           => $request->catatan,
        ]);

        return response()->json($transaction->load('category'), 201);
    }

    /**
     * Show transaksi
     */
    public function show($id): JsonResponse
    {
        $transaction = Transaction::find($id);

        // [FIX] Gunakan != (Loose comparison)
        if (!$transaction || $transaction->business_id != $this->getPerusahaanId()) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }
        return response()->json($transaction, 200);
    }

    /**
     * Update transaksi (FIXED: Soft Delete & Type Mismatch)
     */
    public function update(UpdateTransactionRequest $request, $id): JsonResponse
    {
        $transaction = Transaction::withTrashed()->find($id);
        $myBusinessId = $this->getPerusahaanId();

        // 1. Cek Data & Kepemilikan (Gunakan != bukan !==)
        if (!$transaction || $transaction->business_id != $myBusinessId) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        // 2. Cek Soft Delete
        if ($transaction->trashed()) {
            return response()->json(['message' => 'Data ini sudah dihapus. Refresh halaman.'], 410);
        }

        // Validasi Kategori
        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('business_id', $myBusinessId)),
            ],
        ]);

        $transaction->update([
            'category_id'       => $request->category_id,
            'jumlah'            => $request->jumlah,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'catatan'           => strip_tags($request->catatan ?? ''),
        ]);

        return response()->json($transaction->load('category'), 200);
    }

    /**
     * Hapus transaksi (FIXED: Soft Delete & Idempotency)
     */
    public function destroy($id): JsonResponse
    {
        $transaction = Transaction::withTrashed()->where('id', $id)->first();

        // [FIX] Gunakan != (Loose comparison)
        if (!$transaction || $transaction->business_id != $this->getPerusahaanId()) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        if ($transaction->trashed()) {
            return response()->json(['message' => 'Data sudah terhapus.'], 200);
        }

        $transaction->delete();
        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }
}
