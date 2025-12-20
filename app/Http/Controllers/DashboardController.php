<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Business;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    /**
     * Helper untuk mengambil ID Bisnis dengan aman
     */
    // Kode fungsi mengambil ID bisnis
    private function getBusinessId()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->business) {
            return $user->business->id;
        }
        return null;
    }

    // Kode fungsi menampilkan dashboard
    public function index()
    {
        return view('dashboard');
    }
// Kode fungsi mengambil ringkasan data dashboard
    
    public function getSummary(Request $request)
    {
        $idPerusahaan = $this->getBusinessId();

        if (!$idPerusahaan) {
            return response()->json([
                'summary' => [
                    'saldo' => 0,
                    'pemasukan' => 0,
                    'pengeluaran' => 0,
                    'laba' => 0,
                    'pemasukan_percentage' => 0,
                    'pengeluaran_percentage' => 0,
                    'profit_margin' => 0
                ],
                'recent_transactions' => [],
                'line_chart' => ['labels' => [], 'datasets' => []],
                'doughnut_chart' => ['labels' => [], 'data' => []]
            ]);
        }

        // =========================================================
        // 1. TENTUKAN RENTANG WAKTU
        // =========================================================

        $isAllTimeMonthly = $request->input('mode') === 'monthly' && $request->boolean('all_time');

        // Default: Bulan Ini (harian)
        $currStart = Carbon::now()->startOfMonth();
        $currEnd   = Carbon::now()->endOfMonth();
        $groupByFormat = "DATE(tanggal_transaksi)";

        if ($isAllTimeMonthly) {
            // Semua: rekap per bulan (all time)
            $groupByFormat = "DATE_FORMAT(tanggal_transaksi, '%Y-%m')";

            $minDate = Transaction::where('business_id', $idPerusahaan)->min('tanggal_transaksi');
            $maxDate = Transaction::where('business_id', $idPerusahaan)->max('tanggal_transaksi');

            if ($minDate && $maxDate) {
                $currStart = Carbon::parse($minDate)->startOfMonth();
                $currEnd = Carbon::parse($maxDate)->endOfMonth();
            }
        }

        // Jika Filter Aktif (harian berdasarkan rentang tanggal)
        if (!$isAllTimeMonthly && $request->filled('start_date') && $request->filled('end_date')) {
            $currStart = Carbon::parse($request->start_date)->startOfDay();
            $currEnd   = Carbon::parse($request->end_date)->endOfDay();
            $groupByFormat = "DATE(tanggal_transaksi)"; // Harian
        }

        // =========================================================
        // 2. QUERY DATA SAAT INI (CURRENT)
        // =========================================================
        $queryCurrent = Transaction::where('business_id', $idPerusahaan);
        if (!$isAllTimeMonthly) {
            $queryCurrent->whereBetween('tanggal_transaksi', [$currStart, $currEnd]);
        }

        // Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            $queryCurrent->where(function ($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cat) use ($search) {
                        $cat->where('nama_kategori', 'like', "%{$search}%");
                    });
            });
        }

        $pemasukanCurrent   = (clone $queryCurrent)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranCurrent = (clone $queryCurrent)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $labaCurrent        = $pemasukanCurrent - $pengeluaranCurrent;

        // =========================================================
        // 3. LOGIC BARU: PERSENTASE (Composition & Margin)
        // =========================================================
        // Sesuai rumus: incomePercentage = (income / total) * 100

        $totalArusKas = $pemasukanCurrent + $pengeluaranCurrent;

        // Hitung Persentase Pemasukan (Income / Total * 100)
        $pctPemasukan = $totalArusKas > 0
            ? round(($pemasukanCurrent / $totalArusKas) * 100, 2)
            : 0;

        // Hitung Persentase Pengeluaran (Expense / Total * 100)
        $pctPengeluaran = $totalArusKas > 0
            ? round(($pengeluaranCurrent / $totalArusKas) * 100, 2)
            : 0;

        // Hitung Profit Margin (Profit / Income * 100)
        // Hati-hati pembagian dengan 0 jika pemasukan 0
        $profitMargin = $pemasukanCurrent > 0
            ? round(($labaCurrent / $pemasukanCurrent) * 100, 2)
            : 0;

        // =========================================================
        // 4. SALDO TOTAL (REAL / ALL TIME)
        // =========================================================
        $queryAllTime = Transaction::where('business_id', $idPerusahaan);
        $totalMasuk   = (clone $queryAllTime)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $totalKeluar  = (clone $queryAllTime)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $saldoTotal   = $totalMasuk - $totalKeluar;

        // =========================================================
        // 5. LINE CHART DATA
        // =========================================================
        $chartLabels = [];
        $chartIncome = [];
        $chartExpense = [];

        $incomeDataRaw = (clone $queryCurrent)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->pluck('total', 'date');

        $expenseDataRaw = (clone $queryCurrent)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->pluck('total', 'date');

        // Generate Label yang Rapi
        if ($isAllTimeMonthly) {
            if ($currStart && $currEnd) {
                $period = CarbonPeriod::create($currStart->copy()->startOfMonth(), '1 month', $currEnd->copy()->startOfMonth());
                foreach ($period as $date) {
                    $key = $date->format('Y-m');
                    $chartLabels[] = $date->translatedFormat('M Y');
                    $chartIncome[] = $incomeDataRaw[$key] ?? 0;
                    $chartExpense[] = $expenseDataRaw[$key] ?? 0;
                }
            }
        } elseif ($request->filled('start_date')) {
            $period = CarbonPeriod::create($currStart, $currEnd);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $chartLabels[] = $date->format('d M');
                $chartIncome[] = $incomeDataRaw[$key] ?? 0;
                $chartExpense[] = $expenseDataRaw[$key] ?? 0;
            }
        } else {
            // Default (Harian di Bulan ini)
            $period = CarbonPeriod::create($currStart, $currEnd);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $chartLabels[] = $date->format('d');
                $chartIncome[] = $incomeDataRaw[$key] ?? 0;
                $chartExpense[] = $expenseDataRaw[$key] ?? 0;
            }
        }

        // =========================================================
        // 6. DOUGHNUT CHART
        // =========================================================
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');

        $topCategories = (clone $queryCurrent)
            ->whereHas('category', fn($q) => $q->where('tipe', $doughnutMode))
            ->with(['category' => fn($q) => $q->withTrashed()])
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
        // 7. RECENT TRANSACTIONS
        // =========================================================
        $recentTransactions = (clone $queryCurrent)
            ->with(['category' => fn($q) => $q->withTrashed()])
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                'saldo'       => $saldoTotal,
                'pemasukan'   => $pemasukanCurrent,
                'pengeluaran' => $pengeluaranCurrent,
                'laba'        => $labaCurrent,

                // DATA LOGIC BARU (Composition & Margin)
                // Pastikan Frontend menyesuaikan key ini
                'pemasukan_percent_change'   => $pctPemasukan,
                'pengeluaran_percent_change' => $pctPengeluaran,
                'laba_percent_change'        => $profitMargin
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
// Kode fungsi menyimpan pengaturan perusahaan
    
    public function storeCompanySetup(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required|string|max:32',
            'logo'            => 'nullable|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->business) return redirect()->back();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        Business::create([
            'user_id'    => $user->id,
            'nama_usaha' => strip_tags($request->nama_perusahaan),
            'logo_path'  => $logoPath,
            'saldo'      => 0
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat!');
    }
// Kode fungsi mengambil data dashboard untuk laporan
    
    public function getData(Request $request)
    {
        $idPerusahaan = $this->getBusinessId();
        if (!$idPerusahaan) return response()->json(['error' => 'Company not set'], 400);

        $now = Carbon::now();
        $startDate = $now->clone()->startOfMonth();
        $endDate = $now->clone()->endOfMonth();

        $query = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        $pemasukanPeriod = (clone $query)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPeriod = (clone $query)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        $saldoTotal = Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah') -
            Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        $recentTransactions = $query->clone()->with('category')->latest('tanggal_transaksi')->take(10)->get();

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
