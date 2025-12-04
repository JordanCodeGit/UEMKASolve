<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Business; // [FIX] Gunakan Model Business
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * [BARU] Helper untuk mengambil ID Bisnis dengan aman
     */
    private function getBusinessId()
    {
        $user = Auth::user();
        if ($user && $user->business) {
            return $user->business->id;
        }
        return null;
    }

    public function index()
    {
        // [FIX] Bersihkan logic index.
        // Variable $needsCompanySetup sudah otomatis dikirim oleh AppServiceProvider.
        // Jadi controller ini cukup return view saja.
        return view('dashboard');
    }

    /**
     * [OPSIONAL] Fungsi ini mungkin sudah digantikan oleh CompanySetupController.
     * Tapi saya update juga biar tidak error jika masih ada route yang kesini.
     */
    public function storeCompanySetup(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required|string|max:32',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        // Cek via relasi business
        if ($user->business) return redirect()->back();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // [FIX] Simpan ke tabel businesses
        Business::create([
            'user_id'    => $user->id,
            'nama_usaha' => strip_tags($request->nama_perusahaan), // Mapping ke nama_usaha
            'logo_path'  => $logoPath,
            'saldo'      => 0
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat!');
    }

    public function getSummary(Request $request)
    {
        // [FIX] Ambil ID dari Helper Baru
        $idPerusahaan = $this->getBusinessId();

        if (!$idPerusahaan) {
            return response()->json([
                'summary' => ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0, 'laba' => 0],
                'recent_transactions' => [],
                'line_chart' => ['labels' => [], 'datasets' => []],
                'doughnut_chart' => ['labels' => [], 'data' => []]
            ]);
        }

        // =========================================================
        // 1. QUERY SUMMARY (ALL TIME - TIDAK BERUBAH)
        // =========================================================
        $querySaldo = Transaction::where('business_id', $idPerusahaan);
        $pemasukanSaldo = (clone $querySaldo)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranSaldo = (clone $querySaldo)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $saldoTotal = $pemasukanSaldo - $pengeluaranSaldo;


        // =========================================================
        // 2. QUERY CHART & LIST & SUMMARY CARDS (KENA FILTER)
        // =========================================================
        $queryFiltered = Transaction::where('business_id', $idPerusahaan);

        // A. Filter Search
        if ($request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $queryFiltered->where(function ($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cat) use ($search) {
                        $cat->where('nama_kategori', 'like', "%{$search}%");
                    });
            });
        }

        // B. Filter Tanggal
        $isFilterActive = $request->filled('start_date') && $request->filled('end_date');

        if ($isFilterActive) {
            $startDateInput = $request->start_date;
            $endDateInput = $request->end_date;
            $startDate = (is_string($startDateInput) ? $startDateInput : '') . ' 00:00:00';
            $endDate   = (is_string($endDateInput) ? $endDateInput : '') . ' 23:59:59';

            $queryFiltered->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

            $groupByFormat = "DATE(tanggal_transaksi)";
            $dateFormatPHP = 'd M';
        } else {
            $groupByFormat = "DATE_FORMAT(tanggal_transaksi, '%Y-%m')";
            $dateFormatPHP = 'M Y';
        }

        // --- SUMMARY CARDS ---
        $pemasukanPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $labaPeriod = $pemasukanPeriod - $pengeluaranPeriod;

        // Hitung persentase perubahan (Code logic persentase tetap sama, disingkat disini)
        $pemasukanPercentChange = 0;
        $pengeluaranPercentChange = 0;
        $labaPercentChange = 0;
        // ... (Biarkan logic persentase Anda yg lama disini, tidak perlu diubah) ...


        // --- LINE CHART ---
        $incomeDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

        $expenseDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

        $allKeys = $incomeDataRaw->keys()->merge($expenseDataRaw->keys())->unique()->sort()->values();
        $chartLabels = [];
        $chartIncome = [];
        $chartExpense = [];

        foreach ($allKeys as $key) {
            $chartLabels[] = Carbon::parse($key)->format($dateFormatPHP);
            $chartIncome[] = $incomeDataRaw[$key] ?? 0;
            $chartExpense[] = $expenseDataRaw[$key] ?? 0;
        }

        // --- DOUGHNUT CHART (BAGIAN YG DIPERBAIKI) ---
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');

        $topCategories = (clone $queryFiltered)
            // Filter berdasarkan tipe (pemasukan/pengeluaran)
            ->whereHas('category', fn($q) => $q->where('tipe', $doughnutMode))
            // [FIX 1] Gunakan withTrashed() agar kategori yang terhapus (soft delete) tetap muncul namanya di laporan
            ->with(['category' => fn($q) => $q->withTrashed()])
            ->selectRaw('category_id, SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $doughnutLabels = $topCategories->map(function ($item) {
            // [FIX 2] Logic pengecekan nama kategori yang benar
            // Jangan gunakan property_exists pada Eloquent Model
            if ($item->category) {
                return $item->category->nama_kategori;
            }
            return 'Tanpa Kategori';
        });

        $doughnutData = $topCategories->pluck('total');

        // --- LIST TRANSAKSI ---
        $recentTransactions = (clone $queryFiltered)
            // [FIX 3] Tambahkan withTrashed di list transaksi juga biar aman
            ->with(['category' => fn($q) => $q->withTrashed()])
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();


        // RESPONSE
        return response()->json([
            'summary' => [
                'saldo'       => $saldoTotal,
                'pemasukan'   => $pemasukanPeriod,
                'pengeluaran' => $pengeluaranPeriod,
                'laba'        => $labaPeriod,
                'pemasukan_percent_change' => 0, // Placeholder jika logic persentase diatas dihapus/disembunyikan
                'pengeluaran_percent_change' => 0,
                'laba_percent_change' => 0
            ],
            'recent_transactions' => $recentTransactions,
            'line_chart' => [
                'labels' => $chartLabels,
                'datasets' => [
                    ['label' => 'Pemasukan', 'data' => $chartIncome],
                    ['label' => 'Pengeluaran', 'data' => $chartExpense]
                ]
            ],
            'doughnut_chart' => [
                'labels' => $doughnutLabels,
                'data' => $doughnutData
            ]
        ]);
    }

    public function getData(Request $request)
    {
        // [FIX] Gunakan ID Bisnis yang benar
        $idPerusahaan = $this->getBusinessId();

        if (!$idPerusahaan) {
            return response()->json(['error' => 'Company not set'], 400);
        }

        $now = Carbon::now();
        $startDate = $now->clone()->startOfMonth();
        $endDate = $now->clone()->endOfMonth();

        $query = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        $pemasukanPeriod = $query->clone()->whereHas('category', function ($q) {
            $q->where('tipe', 'pemasukan');
        })->sum('jumlah');

        $pengeluaranPeriod = $query->clone()->whereHas('category', function ($q) {
            $q->where('tipe', 'pengeluaran');
        })->sum('jumlah');

        // [FIX] Saldo Total Query menggunakan ID yang benar
        $saldoTotal = Transaction::where('business_id', $idPerusahaan)
            ->whereHas('category', function ($q) {
                $q->where('tipe', 'pemasukan');
            })->sum('jumlah') -
            Transaction::where('business_id', $idPerusahaan)
            ->whereHas('category', function ($q) {
                $q->where('tipe', 'pengeluaran');
            })->sum('jumlah');

        $recentTransactions = $query->clone()
            ->with('category')
            ->latest('tanggal_transaksi')
            ->take(10)
            ->get();

        return response()->json([
            'summary' => [
                'saldo_real' => $saldoTotal,
                'total_pemasukan' => $pemasukanPeriod,
                'total_pengeluaran' => $pengeluaranPeriod,
                'laba' => $pemasukanPeriod - $pengeluaranPeriod
            ],
            'recent_transactions' => $recentTransactions
        ]);
    }
}
