<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Business; // [FIX] Gunakan Model Business
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        // 1. Ambil ID Bisnis
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
        // A. FILTERING QUERY
        // =========================================================
        $queryFiltered = Transaction::where('business_id', $idPerusahaan);

        // Filter Search
        if ($request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $queryFiltered->where(function ($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cat) use ($search) {
                        $cat->where('nama_kategori', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Tanggal
        $isFilterActive = $request->filled('start_date') && $request->filled('end_date');
        $groupByFormat = "DATE_FORMAT(tanggal_transaksi, '%Y-%m')"; // Default: Bulanan

        if ($isFilterActive) {
            $startDateInput = $request->start_date;
            $endDateInput = $request->end_date;
            $startDate = (is_string($startDateInput) ? $startDateInput : '') . ' 00:00:00';
            $endDate   = (is_string($endDateInput) ? $endDateInput : '') . ' 23:59:59';

            $queryFiltered->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            $groupByFormat = "DATE(tanggal_transaksi)"; // Jika filter aktif: Harian
        }

        // =========================================================
        // B. HITUNG SUMMARY CARDS
        // =========================================================
        $pemasukanPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $labaPeriod = $pemasukanPeriod - $pengeluaranPeriod;

        // Hitung Saldo Real (All Time)
        $queryAllTime = Transaction::where('business_id', $idPerusahaan);
        $totalMasuk = (clone $queryAllTime)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $totalKeluar = (clone $queryAllTime)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $saldoTotal = $totalMasuk - $totalKeluar;

        // =========================================================
        // C. LINE CHART (LOGIKA BARU - FULL DATE RANGE)
        // =========================================================
        $chartLabels = [];
        $chartIncome = [];
        $chartExpense = [];

        // Ambil Data Mentah dari DB (Group by Date)
        $incomeDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->pluck('total', 'date');

        $expenseDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->pluck('total', 'date');

        if ($isFilterActive) {
            // --- LOGIKA 1: Jika Filter Tanggal Aktif (Harian) ---
            // Gunakan CarbonPeriod untuk membuat rentang tanggal lengkap (misal 1 Nov s.d 30 Nov)
            // Agar grafik tidak bolong-bolong jika tidak ada transaksi
            $period = CarbonPeriod::create($request->start_date, $request->end_date);

            foreach ($period as $date) {
                // Key format harus sama dengan output MySQL DATE() yaitu Y-m-d
                $key = $date->format('Y-m-d');

                $chartLabels[] = $date->format('d M'); // Label: 01 Nov
                $chartIncome[] = $incomeDataRaw[$key] ?? 0; // Isi 0 jika tidak ada data
                $chartExpense[] = $expenseDataRaw[$key] ?? 0;
            }
        } else {
            // --- LOGIKA 2: Default / Semua (Bulanan) ---
            // Ambil semua key yang ada di DB, urutkan, lalu loop
            $allKeys = $incomeDataRaw->keys()->merge($expenseDataRaw->keys())->unique()->sort()->values();

            foreach ($allKeys as $key) {
                $chartLabels[] = Carbon::parse($key)->format('M Y'); // Label: Nov 2025
                $chartIncome[] = $incomeDataRaw[$key] ?? 0;
                $chartExpense[] = $expenseDataRaw[$key] ?? 0;
            }
        }

        // =========================================================
        // D. DOUGHNUT CHART
        // =========================================================
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');

        $topCategories = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', $doughnutMode))
            ->with(['category' => fn($q) => $q->withTrashed()]) // Support Soft Delete
            ->selectRaw('category_id, SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $doughnutLabels = $topCategories->map(function ($item) {
            return $item->category ? $item->category->nama_kategori : 'Tanpa Kategori';
        });

        $doughnutData = $topCategories->pluck('total');

        // =========================================================
        // E. RECENT TRANSACTIONS
        // =========================================================
        $recentTransactions = (clone $queryFiltered)
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
                'pemasukan_percent_change' => 0,
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
