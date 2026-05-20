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

class TransactionController extends Controller
{
    /**
     * Helper untuk mendapatkan Business ID User
     */
    private function getPerusahaanId()
    {
        $user = Auth::user();
        if (!$user) return null;

        $business = $user->activeBusiness();
        if ($business) {
            return $business->id;
        }

        return null;
    }

    /**
     * Mengambil daftar transaksi dengan perhitungan Saldo & Laba yang Akurat
     */
    public function index(Request $request): JsonResponse
    {
        $idPerusahaan = $this->getPerusahaanId();

        if (!$idPerusahaan) {
            return response()->json([
                'pagination' => [
                    'data' => [],
                    'links' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => (int) $request->input('per_page', 10),
                    'total' => 0,
                ],
                'summary' => [
                    'total_pemasukan' => 0,
                    'total_pengeluaran' => 0,
                    'laba' => 0,
                    'saldo_real' => 0,
                ],
            ], 200);
        }

        // 1. Base Query (Filter Dasar)
        $queryFiltered = Transaction::where('business_id', $idPerusahaan)
            ->with('category:id,nama_kategori,tipe,ikon');

        // Filter: Search (Catatan / Nama Kategori)
        if ($request->filled('search')) {
            $search = $request->search;
            $queryFiltered->where(function ($q) use ($search) {
                $q->where('catatan', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('nama_kategori', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter: Tanggal (Untuk Summary Laba/Rugi & Tabel)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $queryFiltered->whereBetween('tanggal_transaksi', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter: Tipe (Pemasukan/Pengeluaran)
        if ($request->filled('tipe')) {
            $tipe = $request->tipe;
            $queryFiltered->whereHas('category', fn($q) => $q->where('tipe', $tipe));
        }

        if ($request->filled('category_id')) {
            $queryFiltered->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $queryFiltered->where('status', $request->status);
        }

        // Filter: Nominal
        if ($request->filled('min_nominal')) {
            $queryFiltered->where('jumlah', '>=', $request->min_nominal);
        }
        if ($request->filled('max_nominal')) {
            $queryFiltered->where('jumlah', '<=', $request->max_nominal);
        }

        // 2. Hitung Summary (Berdasarkan Filter di atas)
        // Kita clone query agar tidak mengganggu pagination
        $summaryPemasukan = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $summaryPengeluaran = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        // 3. Hitung Saldo Real (ALL TIME / Dompet)
        // Saldo dompet TIDAK boleh terpengaruh filter tanggal/search, harus selalu total uang saat ini.
        $allTimeQuery = Transaction::where('business_id', $idPerusahaan);

        $saldoMasuk = (clone $allTimeQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $saldoKeluar = (clone $allTimeQuery)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        // 4. Ambil Data Transaksi (Pagination)
        $transactions = $queryFiltered->latest('tanggal_transaksi')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return response()->json([
            'pagination' => $transactions,
            'summary' => [
                'total_pemasukan' => $summaryPemasukan,      // Sesuai Filter (misal: Bulan Ini)
                'total_pengeluaran' => $summaryPengeluaran,  // Sesuai Filter (misal: Bulan Ini)
                'laba' => $summaryPemasukan - $summaryPengeluaran, // Laba Periode Ini
                'saldo_real' => $saldoMasuk - $saldoKeluar   // Saldo Dompet (Total Uang Fisik)
            ]
        ], 200);
    }

    /**
     * Simpan transaksi baru
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        if (Auth::user()?->role === 'owner') {
            return response()->json(['message' => 'Owner hanya dapat memantau buku kas.'], 403);
        }

        $idPerusahaan = $this->getPerusahaanId();
        if (!$idPerusahaan) return response()->json(['message' => 'Profil usaha belum diset.'], 400);

        // Validasi tambahan: Pastikan category_id milik perusahaan ini
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
            'catatan'           => strip_tags($request->catatan ?? ''),
        ]);

        return response()->json($transaction->load('category'), 201);
    }

    /**
     * Detail transaksi
     */
    public function show($id): JsonResponse
    {
        $transaction = Transaction::find($id);
        $myBusinessId = $this->getPerusahaanId();

        if (!$transaction || $transaction->business_id != $myBusinessId) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }
        return response()->json($transaction, 200);
    }

    /**
     * Update transaksi
     */
    public function update(UpdateTransactionRequest $request, $id): JsonResponse
    {
        if (Auth::user()?->role === 'owner') {
            return response()->json(['message' => 'Owner hanya dapat memantau buku kas.'], 403);
        }

        $transaction = Transaction::withTrashed()->find($id);
        $myBusinessId = $this->getPerusahaanId();

        // Security Check
        if (!$transaction || $transaction->business_id != $myBusinessId) {
            return response()->json(['message' => 'Data tidak ditemukan atau bukan milik Anda.'], 404);
        }

        if ($transaction->trashed()) {
            return response()->json(['message' => 'Data ini sudah dihapus.'], 410);
        }

        // Validasi Kategori Milik Sendiri
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

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['verified', 'flagged', 'pending'])],
        ]);

        $transaction = Transaction::withTrashed()->find($id);
        $myBusinessId = $this->getPerusahaanId();

        if (!$transaction || $transaction->business_id != $myBusinessId) {
            return response()->json(['message' => 'Data tidak ditemukan atau bukan milik Anda.'], 404);
        }

        if ($transaction->trashed()) {
            return response()->json(['message' => 'Data ini sudah dihapus.'], 410);
        }

        $transaction->update([
            'status' => $validated['status'],
        ]);

        return response()->json($transaction->load('category'), 200);
    }

    /**
     * Hapus transaksi (Soft Delete)
     */
    public function destroy($id): JsonResponse
    {
        if (Auth::user()?->role === 'owner') {
            return response()->json(['message' => 'Owner hanya dapat memantau buku kas.'], 403);
        }

        $transaction = Transaction::withTrashed()->where('id', $id)->first();
        $myBusinessId = $this->getPerusahaanId();

        if (!$transaction || $transaction->business_id != $myBusinessId) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        if ($transaction->trashed()) {
            return response()->json(['message' => 'Data sudah terhapus.'], 200);
        }

        $transaction->delete();
        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }
}
