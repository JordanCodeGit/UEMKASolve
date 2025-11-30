<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Perusahaan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $needsCompanySetup = is_null($user->id_perusahaan);
        return view('dashboard', compact('needsCompanySetup'));
    }

    public function storeCompanySetup(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required|string|max:32',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        if ($user->id_perusahaan) return redirect()->back();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $perusahaan = Perusahaan::create([
            'nama_perusahaan' => strip_tags($request->nama_perusahaan),
            'logo'            => $logoPath,
        ]);

        $user->update(['id_perusahaan' => $perusahaan->id]);
        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat!');
    }

    public function getSummary(Request $request)
    {
        $user = Auth::user();
        $idPerusahaan = $user->id_perusahaan;

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
        // Query ini untuk Saldo (selalu all time)
        $querySaldo = Transaction::where('business_id', $idPerusahaan);
        $pemasukanSaldo = (clone $querySaldo)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranSaldo = (clone $querySaldo)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $saldoTotal = $pemasukanSaldo - $pengeluaranSaldo;


        // =========================================================
        // 2. QUERY CHART & LIST & SUMMARY CARDS (KENA FILTER)
        // =========================================================
        // Query ini yang akan diobok-obok oleh filter user
        $queryFiltered = Transaction::where('business_id', $idPerusahaan);

        // A. Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            $queryFiltered->where(function($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                  ->orWhereHas('category', function($cat) use ($search) {
                      $cat->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        // B. Filter Tanggal (Mempengaruhi Grafik, List, & Summary Cards)
        $isFilterActive = $request->filled('start_date') && $request->filled('end_date');
        
        if ($isFilterActive) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate   = $request->end_date . ' 23:59:59';
            
            $queryFiltered->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            
            // Mode Harian
            $groupByFormat = "DATE(tanggal_transaksi)";
            $dateFormatPHP = 'd M'; 
        } else {
            // Mode Bulanan (Semua)
            $groupByFormat = "DATE_FORMAT(tanggal_transaksi, '%Y-%m')";
            $dateFormatPHP = 'M Y'; 
        }

        // --- SUMMARY CARDS (Pemasukan, Pengeluaran, Laba) ---
        // Menggunakan queryFiltered agar terkena filter tanggal
        $pemasukanPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPeriod = (clone $queryFiltered)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $labaPeriod = $pemasukanPeriod - $pengeluaranPeriod;

        // Hitung persentase perubahan (jika ada data bulan/periode sebelumnya)
        $pemasukanPercentChange = 0;
        $pengeluaranPercentChange = 0;
        $labaPercentChange = 0;

        // Jika filter aktif (berdasarkan range tanggal), hitung perubahan vs periode sebelumnya
        if ($isFilterActive) {
            $startDate_obj = Carbon::parse($request->start_date);
            $endDate_obj = Carbon::parse($request->end_date);
            $periodLength = $startDate_obj->diffInDays($endDate_obj);

            $prevStartDate = $startDate_obj->clone()->subDays($periodLength + 1);
            $prevEndDate = $startDate_obj->clone()->subDay();

            $queryPrevious = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$prevStartDate->format('Y-m-d') . ' 00:00:00', $prevEndDate->format('Y-m-d') . ' 23:59:59']);

            $pemasukanPrevious = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
            $pengeluaranPrevious = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
            $labaPrevious = $pemasukanPrevious - $pengeluaranPrevious;

            // Hitung persentase - gunakan nilai absolute untuk handling nilai 0/negatif
            // Jika periode sebelumnya 0, tentukan tanda berdasarkan periode saat ini
            if ($pemasukanPrevious != 0) {
                $pemasukanPercentChange = (($pemasukanPeriod - $pemasukanPrevious) / abs($pemasukanPrevious)) * 100;
            } else {
                $pemasukanPercentChange = ($pemasukanPeriod > 0 ? 100 : ($pemasukanPeriod < 0 ? -100 : 0));
            }

            if ($pengeluaranPrevious != 0) {
                $pengeluaranPercentChange = (($pengeluaranPeriod - $pengeluaranPrevious) / abs($pengeluaranPrevious)) * 100;
            } else {
                $pengeluaranPercentChange = ($pengeluaranPeriod > 0 ? 100 : ($pengeluaranPeriod < 0 ? -100 : 0));
            }

            if ($labaPrevious != 0) {
                $labaPercentChange = (($labaPeriod - $labaPrevious) / abs($labaPrevious)) * 100;
            } else {
                $labaPercentChange = ($labaPeriod > 0 ? 100 : ($labaPeriod < 0 ? -100 : 0));
            }
        } else {
            // Mode bulanan - bandingkan dengan bulan sebelumnya
            $currentMonth = Carbon::now();
            $prevMonth = $currentMonth->clone()->subMonth();

            $queryCurrent = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$currentMonth->startOfMonth()->format('Y-m-d H:i:s'), $currentMonth->endOfMonth()->format('Y-m-d H:i:s')]);

            $queryPrevMonth = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$prevMonth->startOfMonth()->format('Y-m-d H:i:s'), $prevMonth->endOfMonth()->format('Y-m-d H:i:s')]);

            $pemasukanCurrent = (clone $queryCurrent)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
            $pemasukanPrevMonth = (clone $queryPrevMonth)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');

            $pengeluaranCurrent = (clone $queryCurrent)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
            $pengeluaranPrevMonth = (clone $queryPrevMonth)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

            $labaCurrent = $pemasukanCurrent - $pengeluaranCurrent;
            $labaPrevMonth = $pemasukanPrevMonth - $pengeluaranPrevMonth;

            // Hitung persentase - gunakan nilai absolute untuk handling nilai 0/negatif
            // Jika periode sebelumnya 0, tentukan tanda berdasarkan periode saat ini
            if ($pemasukanPrevMonth != 0) {
                $pemasukanPercentChange = (($pemasukanCurrent - $pemasukanPrevMonth) / abs($pemasukanPrevMonth)) * 100;
            } else {
                $pemasukanPercentChange = ($pemasukanCurrent > 0 ? 100 : ($pemasukanCurrent < 0 ? -100 : 0));
            }

            if ($pengeluaranPrevMonth != 0) {
                $pengeluaranPercentChange = (($pengeluaranCurrent - $pengeluaranPrevMonth) / abs($pengeluaranPrevMonth)) * 100;
            } else {
                $pengeluaranPercentChange = ($pengeluaranCurrent > 0 ? 100 : ($pengeluaranCurrent < 0 ? -100 : 0));
            }

            if ($labaPrevMonth != 0) {
                $labaPercentChange = (($labaCurrent - $labaPrevMonth) / abs($labaPrevMonth)) * 100;
            } else {
                $labaPercentChange = ($labaCurrent > 0 ? 100 : ($labaCurrent < 0 ? -100 : 0));
            }
        }

        // --- LINE CHART (Cashflow) ---
        $incomeDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

        $expenseDataRaw = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->selectRaw("$groupByFormat as date, SUM(jumlah) as total")
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

        $allKeys = $incomeDataRaw->keys()->merge($expenseDataRaw->keys())->unique()->sort()->values();
        $chartLabels = []; $chartIncome = []; $chartExpense = [];

        foreach ($allKeys as $key) {
            $chartLabels[] = Carbon::parse($key)->format($dateFormatPHP);
            $chartIncome[] = $incomeDataRaw[$key] ?? 0;
            $chartExpense[] = $expenseDataRaw[$key] ?? 0;
        }

        // --- DOUGHNUT CHART (Kategori) ---
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');
        $topCategories = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', $doughnutMode))
            ->with('category')
            ->selectRaw('category_id, SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $doughnutLabels = $topCategories->map(fn($item) => optional($item->category)->nama_kategori ?? 'Tanpa Kategori');
        $doughnutData = $topCategories->pluck('total');

        // --- LIST TRANSAKSI (5 Terakhir) ---
        $recentTransactions = (clone $queryFiltered)
            ->with('category')
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();


        // =========================================================
        // RESPONSE JSON
        // =========================================================
        return response()->json([
            'summary' => [
                // Saldo: All Time (tidak berubah dengan filter)
                'saldo'       => $saldoTotal,
                // Pemasukan, Pengeluaran, Laba: Bergantung pada filter tanggal
                'pemasukan'   => $pemasukanPeriod,
                'pengeluaran' => $pengeluaranPeriod,
                'laba'        => $labaPeriod,
                // Persentase perubahan
                'pemasukan_percent_change' => round($pemasukanPercentChange, 2),
                'pengeluaran_percent_change' => round($pengeluaranPercentChange, 2),
                'laba_percent_change' => round($labaPercentChange, 2)
            ],
            'recent_transactions' => $recentTransactions, // Kena Filter
            'line_chart' => [                             // Kena Filter
                'labels' => $chartLabels,
                'datasets' => [
                    ['label' => 'Pemasukan', 'data' => $chartIncome],
                    ['label' => 'Pengeluaran', 'data' => $chartExpense]
                ]
            ],
            'doughnut_chart' => [                         // Kena Filter
                'labels' => $doughnutLabels,
                'data' => $doughnutData
            ]
        ]);
    }

    // --- API Endpoint untuk Print Laporan ---
    public function getData(Request $request)
    {
        $user = Auth::user();
        $idPerusahaan = $user->id_perusahaan;

        if (!$idPerusahaan) {
            return response()->json(['error' => 'Company not set'], 400);
        }

        // Get current month summary
        $now = Carbon::now();
        $startDate = $now->clone()->startOfMonth();
        $endDate = $now->clone()->endOfMonth();

        $query = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        // Calculate summary - filter by category tipe
        $pemasukanPeriod = $query->clone()->whereHas('category', function ($q) {
            $q->where('tipe', 'pemasukan');
        })->sum('jumlah');
        
        $pengeluaranPeriod = $query->clone()->whereHas('category', function ($q) {
            $q->where('tipe', 'pengeluaran');
        })->sum('jumlah');
        
        $saldoTotal = Transaction::where('business_id', $idPerusahaan)
            ->whereHas('category', function ($q) {
                $q->where('tipe', 'pemasukan');
            })->sum('jumlah') - 
            Transaction::where('business_id', $idPerusahaan)
            ->whereHas('category', function ($q) {
                $q->where('tipe', 'pengeluaran');
            })->sum('jumlah');

        // Get recent transactions (last 10)
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