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
    private function getBusinessId()
    {
        $user = Auth::user();
        if ($user && $user->business) {
            return $user->business->id;
        }
        // Cek relasi 'perusahaan' jika 'business' null (backup)
        if ($user && $user->perusahaan) {
            return $user->perusahaan->id;
        }
        return null;
    }

    public function index()
    {
        return view('dashboard');
    }

    /**
     * Helper: Hitung Persentase Perubahan
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            // Jika sebelumnya 0 dan sekarang > 0, anggap naik 100%
            // Jika sama-sama 0, berarti 0%
            return $current > 0 ? 100 : 0;
        }

        // Rumus: ((Sekarang - Lalu) / Lalu) * 100
        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function getSummary(Request $request)
    {
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
        // 1. TENTUKAN RENTANG WAKTU (CURRENT vs PREVIOUS)
        // =========================================================

        // Default: Bulan Ini
        $currStart = Carbon::now()->startOfMonth();
        $currEnd   = Carbon::now()->endOfMonth();
        $groupByFormat = "DATE_FORMAT(tanggal_transaksi, '%Y-%m')";

        // Jika Filter Aktif
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $currStart = Carbon::parse($request->start_date)->startOfDay();
            $currEnd   = Carbon::parse($request->end_date)->endOfDay();
            $groupByFormat = "DATE(tanggal_transaksi)"; // Harian
        }

        // Hitung Periode Sebelumnya (Durasi yang sama mundur ke belakang)
        // Contoh: Jika filter 1-30 Nov (30 hari), Previous adalah 1-31 Okt (Mundur 1 bulan/durasi sama)
        $diffInDays = $currStart->diffInDays($currEnd) + 1;
        $prevEnd    = $currStart->copy()->subSecond(); // Detik sebelum start
        $prevStart  = $prevEnd->copy()->subDays($diffInDays)->addSecond(); // Mundur sebanyak durasi

        // =========================================================
        // 2. QUERY DATA SAAT INI (CURRENT)
        // =========================================================
        $queryCurrent = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$currStart, $currEnd]);

        // Filter Search (Opsional, hanya mempengaruhi data current)
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
        // 3. QUERY DATA SEBELUMNYA (PREVIOUS) - UNTUK PERSENTASE
        // =========================================================
        $queryPrevious = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$prevStart, $prevEnd]);

        $pemasukanPrev   = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPrev = (clone $queryPrevious)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');
        $labaPrev        = $pemasukanPrev - $pengeluaranPrev;

        // Hitung Persentase
        $pctPemasukan   = $this->calculatePercentageChange($pemasukanCurrent, $pemasukanPrev);
        $pctPengeluaran = $this->calculatePercentageChange($pengeluaranCurrent, $pengeluaranPrev);
        $pctLaba        = $this->calculatePercentageChange($labaCurrent, $labaPrev);

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
        if ($request->filled('start_date')) {
            // Loop Harian (Agar grafik tidak bolong)
            $period = CarbonPeriod::create($currStart, $currEnd);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $chartLabels[] = $date->format('d M');
                $chartIncome[] = $incomeDataRaw[$key] ?? 0;
                $chartExpense[] = $expenseDataRaw[$key] ?? 0;
            }
        } else {
            // Default (Bulanan) - ambil semua tanggal yg ada transaksi di bulan ini
            // Atau bisa di-force daily 1-30
            $period = CarbonPeriod::create($currStart, $currEnd);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d'); // Default query kita pakai format Y-m-d kalau default

                // Cek format key dari database (kadang YYYY-MM jika grouping bulanan)
                // Disini kita paksa loop harian bulan ini agar grafik smooth
                $todayIncome = Transaction::where('business_id', $idPerusahaan)
                                ->whereDate('tanggal_transaksi', $key)
                                ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
                                ->sum('jumlah');

                $todayExpense = Transaction::where('business_id', $idPerusahaan)
                                ->whereDate('tanggal_transaksi', $key)
                                ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
                                ->sum('jumlah');

                $chartLabels[] = $date->format('d'); // Tgl 1, 2, 3...
                $chartIncome[] = $todayIncome;
                $chartExpense[] = $todayExpense;
            }
        }

        // =========================================================
        // 6. DOUGHNUT CHART
        // =========================================================
        $doughnutMode = $request->input('doughnut_mode', 'pengeluaran');

        $topCategories = (clone $queryCurrent) // Pakai queryCurrent agar ikut filter tanggal
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
                // Data Persentase (Sudah Dihitung)
                'pemasukan_percent_change'   => $pctPemasukan,
                'pengeluaran_percent_change' => $pctPengeluaran,
                'laba_percent_change'        => $pctLaba
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

    // Fungsi storeCompanySetup tetap, Fungsi getData (API print) tetap
    public function storeCompanySetup(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required|string|max:32',
            'logo'            => 'nullable|image|max:2048',
        ]);

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

    public function getData(Request $request)
    {
        // Fungsi ini opsional/legacy untuk print laporan
        $idPerusahaan = $this->getBusinessId();
        if (!$idPerusahaan) return response()->json(['error' => 'Company not set'], 400);

        $now = Carbon::now();
        $startDate = $now->clone()->startOfMonth();
        $endDate = $now->clone()->endOfMonth();

        $query = Transaction::where('business_id', $idPerusahaan)
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        $pemasukanPeriod = (clone $query)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
        $pengeluaranPeriod = (clone $query)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

        $saldoTotal = Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q)=>$q->where('tipe','pemasukan'))->sum('jumlah') -
                      Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q)=>$q->where('tipe','pengeluaran'))->sum('jumlah');

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
