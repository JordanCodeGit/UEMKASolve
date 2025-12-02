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

        // Hitung persentase perubahan
        $pemasukanPercentChange = 0;
        $pengeluaranPercentChange = 0;
        $labaPercentChange = 0;

        if ($isFilterActive) {
            $startDateStr = is_string($request->start_date) ? $request->start_date : '';
            $endDateStr = is_string($request->end_date) ? $request->end_date : '';
            $startDate_obj = Carbon::parse($startDateStr);
            $endDate_obj = Carbon::parse($endDateStr);
            $periodLength = $startDate_obj->diffInDays($endDate_obj);

            $prevStartDate = $startDate_obj->clone()->subDays($periodLength + 1);
            $prevEndDate = $startDate_obj->clone()->subDay();

            $queryPrevious = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$prevStartDate->format('Y-m-d') . ' 00:00:00', $prevEndDate->format('Y-m-d') . ' 23:59:59']);

            $pemasukanPrevious = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
            $pengeluaranPrevious = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
            $labaPrevious = $pemasukanPrevious - $pengeluaranPrevious;

            if ($pemasukanPrevious != 0) {
                $pemasukanPercentChange = (($pemasukanPeriod - $pemasukanPrevious) / (float)abs((int)$pemasukanPrevious)) * 100;
            } else {
                $pemasukanPercentChange = ($pemasukanPeriod > 0 ? 100 : ($pemasukanPeriod < 0 ? -100 : 0));
            }

            if ($pengeluaranPrevious != 0) {
                $pengeluaranPercentChange = (($pengeluaranPeriod - $pengeluaranPrevious) / (float)abs((int)$pengeluaranPrevious)) * 100;
            } else {
                $pengeluaranPercentChange = ($pengeluaranPeriod > 0 ? 100 : ($pengeluaranPeriod < 0 ? -100 : 0));
            }

            if ($labaPrevious != 0) {
                $labaPercentChange = (($labaPeriod - $labaPrevious) / abs($labaPrevious)) * 100;
            } else {
                $labaPercentChange = ($labaPeriod > 0 ? 100 : ($labaPeriod < 0 ? -100 : 0));
            }
        } else {
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

            if ($pemasukanPrevMonth != 0) {
                $pemasukanPercentChange = (($pemasukanCurrent - $pemasukanPrevMonth) / (float)abs((int)$pemasukanPrevMonth)) * 100;
            } else {
                $pemasukanPercentChange = ($pemasukanCurrent > 0 ? 100 : ($pemasukanCurrent < 0 ? -100 : 0));
            }

            if ($pengeluaranPrevMonth != 0) {
                $pengeluaranPercentChange = (($pengeluaranCurrent - $pengeluaranPrevMonth) / (float)abs((int)$pengeluaranPrevMonth)) * 100;
            } else {
                $pengeluaranPercentChange = ($pengeluaranCurrent > 0 ? 100 : ($pengeluaranCurrent < 0 ? -100 : 0));
            }

            if ($labaPrevMonth != 0) {
                $labaPercentChange = (($labaCurrent - $labaPrevMonth) / (float)abs((int)$labaPrevMonth)) * 100;
            } else {
                $labaPercentChange = ($labaCurrent > 0 ? 100 : ($labaCurrent < 0 ? -100 : 0));
            }
        }

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

        // --- DOUGHNUT CHART ---
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');
        $topCategories = (clone $queryFiltered)
            ->whereHas('category', fn($q) => $q->where('tipe', $doughnutMode))
            ->with('category')
            ->selectRaw('category_id, SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $doughnutLabels = $topCategories->map(function ($item) {
            $cat = optional($item->category);
            return is_object($cat) && property_exists($cat, 'nama_kategori') ? $cat->nama_kategori : 'Tanpa Kategori';
        });
        $doughnutData = $topCategories->pluck('total');

        // --- LIST TRANSAKSI ---
        $recentTransactions = (clone $queryFiltered)
            ->with('category')
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
                'pemasukan_percent_change' => round($pemasukanPercentChange, 2),
                'pengeluaran_percent_change' => round($pengeluaranPercentChange, 2),
                'laba_percent_change' => round($labaPercentChange, 2)
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
