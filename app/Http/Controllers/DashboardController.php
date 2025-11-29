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
                'laba'        => $labaPeriod
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
}