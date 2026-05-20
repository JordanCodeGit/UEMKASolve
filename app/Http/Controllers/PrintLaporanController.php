<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Business;
use App\Models\Transaction;

class PrintLaporanController extends Controller
{
    public function generatePdf(Request $request)
    {
        try {
            if (!extension_loaded('gd')) {
                return response()->json(['error' => 'PHP GD extension missing'], 503);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user && !$user->activeBusiness() && $user->role !== 'owner') {
                $user->setRelation('business', Business::create([
                    'user_id' => $user->id,
                    'nama_usaha' => 'Akun ' . ucfirst($user->role ?? 'User') . ' - ' . $user->name,
                ]));
            }

            $business = $user ? $user->activeBusiness() : null;
            if (!$user || !$business) {
                return response()->json(['error' => 'Business not found'], 400);
            }

            $idPerusahaan = $business->id;
            $sections = $request->get('sections', []);
            $sections = (array)$sections;

            if (empty($sections)) {
                return response()->json(['error' => 'Pilih minimal satu bagian.'], 400);
            }

            // Data Fetching
            $now = Carbon::now();
            $startDate = $now->clone()->startOfMonth();
            $endDate = $now->clone()->endOfMonth();

            $allTransactions = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->with('category')
                ->latest('tanggal_transaksi')
                ->get();

            // --- [MODIFIKASI: Hitung Total Pemasukan & Pengeluaran + Kategori] ---
            $pemasukanPeriod = 0;
            $pengeluaranPeriod = 0;
            $rawExpense = [];
            $rawIncome = [];

            foreach ($allTransactions as $tx) {
                $category = $tx->category;
                if (!$category) continue;

                $nominal = (float)$tx->jumlah;
                $name = $category->nama_kategori;

                if ($category->tipe === 'pemasukan') {
                    $pemasukanPeriod += $nominal;
                    if (!isset($rawIncome[$name])) $rawIncome[$name] = 0;
                    $rawIncome[$name] += $nominal;
                } elseif ($category->tipe === 'pengeluaran') {
                    $pengeluaranPeriod += $nominal;
                    if (!isset($rawExpense[$name])) $rawExpense[$name] = 0;
                    $rawExpense[$name] += $nominal;
                }
            }

            // Format Data Kategori (Warna & Struktur)
            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#64748b'];

            // 1. Kategori Pengeluaran
            $expenseCategories = [];
            $idx = 0;
            foreach ($rawExpense as $name => $total) {
                $expenseCategories[] = [
                    'nama_kategori' => $name,
                    'total' => $total,
                    'color' => $colors[$idx % count($colors)]
                ];
                $idx++;
            }

            // 2. Kategori Pemasukan [BARU]
            $incomeCategories = [];
            $idx = 0;
            foreach ($rawIncome as $name => $total) {
                $incomeCategories[] = [
                    'nama_kategori' => $name,
                    'total' => $total,
                    'color' => $colors[$idx % count($colors)]
                ];
                $idx++;
            }

            // --- [Chart Generation] ---
            $lineChartBase64 = null;
            $expenseChartBase64 = null;
            $incomeChartBase64 = null;

            if ($sections['grafik'] ?? false) {
                // A. Grafik Garis (Daily)
                $dailyData = [];
                foreach ($allTransactions as $tx) {
                    $date = $tx->tanggal_transaksi->format('d');
                    if (!isset($dailyData[$date])) $dailyData[$date] = ['pemasukan' => 0, 'pengeluaran' => 0];
                    if ($tx->category->tipe === 'pemasukan') $dailyData[$date]['pemasukan'] += (float)$tx->jumlah;
                    else $dailyData[$date]['pengeluaran'] += (float)$tx->jumlah;
                }
                ksort($dailyData);
                $lineUrl = $this->generateLineChartUrl(array_keys($dailyData), array_column($dailyData, 'pemasukan'), array_column($dailyData, 'pengeluaran'));
                $lineChartBase64 = $this->getChartAsBase64($lineUrl);

                // B. Grafik Donat Pengeluaran
                if (count($expenseCategories) > 0) {
                    $expenseChartBase64 = $this->getChartAsBase64($this->generateDoughnutChartUrl($expenseCategories));
                }

                // C. Grafik Donat Pemasukan [BARU]
                if (count($incomeCategories) > 0) {
                    $incomeChartBase64 = $this->getChartAsBase64($this->generateDoughnutChartUrl($incomeCategories));
                }
            }

            // Saldo Total (All Time)
            $allTimePemasukan = Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))->sum('jumlah');
            $allTimePengeluaran = Transaction::where('business_id', $idPerusahaan)->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))->sum('jumlah');

            $pdfData = [
                'title' => 'Laporan Keuangan',
                'date' => date('d-m-Y H:i'),
                'sections' => $sections,
                'summary' => [
                    'saldo_real' => $allTimePemasukan - $allTimePengeluaran,
                    'total_pemasukan' => $pemasukanPeriod,
                    'total_pengeluaran' => $pengeluaranPeriod,
                    'laba' => $pemasukanPeriod - $pengeluaranPeriod
                ],
                'transactions' => $allTransactions->take(10), // Limit 10
                'company' => ['name' => $business->nama_usaha ?? 'Usaha Saya'],

                // Variabel Grafik Baru
                'expense_categories' => $expenseCategories,
                'income_categories' => $incomeCategories,
                'lineChartBase64' => $lineChartBase64,
                'expenseChartBase64' => $expenseChartBase64,
                'incomeChartBase64' => $incomeChartBase64,
            ];

            $pdf = Pdf::loadView('pdf.laporan-keuangan', $pdfData)
                ->setPaper('a4')
                ->setOption(['isRemoteEnabled' => true]);

            return $pdf->download('Laporan_Keuangan_' . date('d-m-Y') . '.pdf');

        } catch (\Throwable $e) {
            Log::error('PDF Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // --- Helper Functions ---

    private function generateLineChartUrl($labels, $pemasukan, $pengeluaran)
    {
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Masuk', 'data' => $pemasukan, 'backgroundColor' => 'rgba(16, 185, 129, 0.7)'], // Hijau
                    ['label' => 'Keluar', 'data' => $pengeluaran, 'backgroundColor' => 'rgba(239, 68, 68, 0.7)'] // Merah
                ]
            ],
            'options' => ['legend' => ['position' => 'bottom']]
        ];
        return "https://quickchart.io/chart?c=" . urlencode(json_encode($config)) . "&w=700&h=250";
    }

    private function generateDoughnutChartUrl($categories)
    {
        $labels = array_column($categories, 'nama_kategori');
        $dataValues = array_column($categories, 'total');
        $colors = array_column($categories, 'color');

        $config = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $dataValues,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => ['display' => false]
                ],
                'cutoutPercentage' => 70,
            ]
        ];
        return "https://quickchart.io/chart?c=" . urlencode(json_encode($config)) . "&w=300&h=300";
    }

    private function getChartAsBase64($url)
    {
        try {
            $img = @file_get_contents($url);
            if ($img) return 'data:image/png;base64,' . base64_encode($img);
        } catch (\Exception $e) {}
        return null;
    }
}
