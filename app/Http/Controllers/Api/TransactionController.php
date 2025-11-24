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
     * [MODIFIKASI] Dapatkan ID Perusahaan langsung dari tabel users.
     * Menggantikan: Auth::user()->business->id
     */
    private function getPerusahaanId()
    {
        return Auth::user()->id_perusahaan;
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
        // JALUR 1: QUERY UNTUK TABEL (KENA FILTER)
        // =================================================================
        $queryList = Transaction::where('business_id', $idPerusahaan)
                                ->with('category:id,nama_kategori,tipe,ikon');

        // A. Filter Search
        if ($request->filled('search')) {
             $queryList->where(function ($q) use ($request) {
                $q->where('catatan', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function ($catQuery) use ($request) {
                      $catQuery->where('nama_kategori', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // B. Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate   = $request->end_date . ' 23:59:59';
            $queryList->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
        }

        // C. Filter Tipe
        if ($request->filled('tipe')) {
            $queryList->whereHas('category', function (Builder $q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }
        
        // D. Filter Nominal
        if ($request->filled('min_nominal')) {
            $queryList->where('jumlah', '>=', $request->min_nominal);
        }
        if ($request->filled('max_nominal')) {
            $queryList->where('jumlah', '<=', $request->max_nominal);
        }


        // =================================================================
        // JALUR 2: QUERY UNTUK SALDO/SUMMARY (KEBAL FILTER)
        // =================================================================
        // Kita buat query baru yang BERSIH, hanya filter by Perusahaan saja.
        // Ini akan menghitung total uang REAL yang ada di database (All Time).
        
        $querySummary = Transaction::where('business_id', $idPerusahaan);

        // Hitung Pemasukan (All Time)
        $totalPemasukan = (clone $querySummary)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pemasukan');
        })->sum('jumlah');

        // Hitung Pengeluaran (All Time)
        $totalPengeluaran = (clone $querySummary)->whereHas('category', function (Builder $q) {
            $q->where('tipe', 'pengeluaran');
        })->sum('jumlah');

        // Hitung Saldo Akhir (All Time)
        $laba = $totalPemasukan - $totalPengeluaran;


        // =================================================================
        // EKSEKUSI DATA
        // =================================================================

        // Ambil data tabel dari $queryList (yang sudah difilter)
        $transactions = $queryList->latest('tanggal_transaksi')
                                  ->paginate($request->input('per_page', 10))
                                  ->withQueryString();

        return response()->json([
            'pagination' => $transactions, // Data Tabel (Berubah sesuai filter)
            'summary' => [                 // Data Saldo (TETAP / All Time)
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'laba' => $laba
            ]
        ], 200);
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $validatedData = $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'tanggal_transaksi' => 'required|date',
            'catatan' => 'nullable|string|max:255',
            // 'tipe' tidak perlu divalidasi masuk DB, karena ikut kategori
        ]);

        $idPerusahaan = Auth::user()->id_perusahaan;

        if (!$idPerusahaan) {
            return response()->json(['message' => 'Anda belum memiliki profil usaha.'], 400);
        }

        $validatedData = $request->validated();

        // [PERBAIKAN] Mapping Sesuai Database (Gambar)
        $transaction = Transaction::create([
            'business_id'       => $idPerusahaan,
            'category_id'       => $validatedData['category_id'],
            
            // KIRI (Kolom DB)   => KANAN (Input Form)
            'jumlah'            => $validatedData['jumlah'], 
            'tanggal_transaksi' => $validatedData['tanggal_transaksi'], 
            'catatan'           => $validatedData['catatan'],
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
        // 1. Cari Transaksi & Cek Kepemilikan
        $transaction = Transaction::find($id);

        if (!$transaction || $transaction->business_id !== $this->getPerusahaanId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        // 2. Ambil Data Validasi
        $validatedData = $request->validated();

        // 3. [PERBAIKAN PENTING] Mapping Manual (Input Form -> Kolom DB)
        // Karena kita tidak bisa langsung $transaction->update($validatedData)
        
        $transaction->update([
            'category_id'       => $validatedData['category_id'],
            'jumlah'            => $validatedData['jumlah'],            // Mapping: jumlah -> amount
            'tanggal_transaksi'              => $validatedData['tanggal_transaksi'], // Mapping: tanggal -> date
            'catatan'       => strip_tags($validatedData['catatan'] ?? ''), // Mapping & Bersihkan XSS
            // 'type' tidak perlu diupdate karena ikut kategori, tapi jika mau disimpan:
            // 'type'           => $validatedData['tipe'], 
        ]);

        // 4. Reload relasi untuk respon
        $transaction->load('category');

        return response()->json($transaction, 200);
    }

    /**
     * Menghapus transaksi (Soft Delete).
     */
    public function destroy($id): JsonResponse
    {
        $transaction = Transaction::find($id);
        
        // Cek kepemilikan via business_id (id_perusahaan)
        if (!$transaction || $transaction->business_id !== $this->getPerusahaanId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        $transaction->delete();
        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }
}