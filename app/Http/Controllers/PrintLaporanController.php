<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Transaction;

class PrintLaporanController extends Controller
{
    public function generatePdf(Request $request)
    {
        try {
            // Check if PHP GD extension is installed
            if (!extension_loaded('gd')) {
                \Log::warning('PHP GD extension not loaded');
                // Return JSON response dengan instruksi untuk server
                return response()->json([
                    'error' => 'PDF generation requires PHP GD extension',
                    'message' => 'Server tidak memiliki PHP GD extension yang diperlukan untuk generate PDF dengan grafik.',
                    'solution' => 'Hubungi hosting provider untuk mengaktifkan PHP GD extension, atau coba gunakan server production yang sudah dikonfigurasi.'
                ], 503);
            }

            \Log::info('PrintLaporanController generatePdf called');

            $user = Auth::user();
            assert($user !== null);
            $idPerusahaan = $user->id_perusahaan;

            if (!$idPerusahaan) {
                \Log::error('No company ID for user');
                return response()->json(['error' => 'Company not set'], 400);
            }

            $sections = $request->get('sections', []);

            \Log::info('Sections:', (array)$sections);

            // Validate that at least one section is selected
            $sections = (array)$sections;
            if (!($sections['ringkasan'] ?? false) && !($sections['grafik'] ?? false) && !($sections['rincian'] ?? false)) {
                return response()->json(['error' => 'No sections selected'], 400);
            }

            // Fetch fresh data from database
            $now = Carbon::now();
            $startDate = $now->clone()->startOfMonth();
            $endDate = $now->clone()->endOfMonth();

            \Log::info('Date range: ' . $startDate . ' to ' . $endDate);

            // Get all transactions for this business in the current month
            $allTransactions = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->with('category')
                ->latest('tanggal_transaksi')
                ->get();

            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $allTransactions */

            \Log::info('Total transactions found: ' . count($allTransactions));

            // Calculate summary by iterating through transactions
            $pemasukanPeriod = 0;
            $pengeluaranPeriod = 0;

            foreach ($allTransactions as $tx) {
                /** @var \App\Models\Category|null $category */
                $category = $tx->category;
                if ($category && $category->tipe === 'pemasukan') {
                    $pemasukanPeriod += (float)$tx->jumlah;
                } elseif ($category && $category->tipe === 'pengeluaran') {
                    $pengeluaranPeriod += (float)$tx->jumlah;
                }
            }

            // Calculate total balance (all time, not just this month)
            $allTimePemasukan = Transaction::where('business_id', $idPerusahaan)
                ->with('category')
                ->get()
                ->filter(function ($tx) {
                    /** @var \App\Models\Transaction $tx */
                    $category = $tx->category;
                    /** @phpstan-ignore-next-line */
                    return $category && $category->tipe === 'pemasukan';
                })
                ->sum('jumlah');

            $allTimePengeluaran = Transaction::where('business_id', $idPerusahaan)
                ->with('category')
                ->get()
                ->filter(function ($tx) {
                    /** @var \App\Models\Transaction $tx */
                    $category = $tx->category;
                    /** @phpstan-ignore-next-line */
                    return $category && $category->tipe === 'pengeluaran';
                })
                ->sum('jumlah');

            $saldoTotal = (is_numeric($allTimePemasukan) ? $allTimePemasukan : 0) - (is_numeric($allTimePengeluaran) ? $allTimePengeluaran : 0);

            // Get recent transactions (limit to 10)
            $transactions = $allTransactions->take(10);

            // Calculate breakdown by category for Grafik Kas
            $categoryBreakdown = [];
            $categoryLabels = [];
            $categoryPemasukanData = [];
            $categoryPengeluaranData = [];

            foreach ($allTransactions as $tx) {
                /** @var \App\Models\Category|null $category */
                $category = $tx->category;
                if (!$category) continue;

                $categoryName = $category->nama_kategori;
                $categoryType = $category->tipe;

                if (!isset($categoryBreakdown[$categoryName])) {
                    $categoryBreakdown[$categoryName] = [
                        'nama' => $categoryName,
                        'tipe' => $categoryType,
                        'total' => 0,
                        'count' => 0
                    ];
                }

                $categoryBreakdown[$categoryName]['total'] += (float)$tx->jumlah;
                $categoryBreakdown[$categoryName]['count'] += 1;
            }

            // Prepare chart data (daily breakdown)
            $dailyData = [];
            foreach ($allTransactions as $tx) {
                /** @var \App\Models\Transaction $tx */
                $tanggal = $tx->tanggal_transaksi;
                $date = (new \DateTime((string)$tanggal))->format('d');

                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [
                        'pemasukan' => 0,
                        'pengeluaran' => 0
                    ];
                }

                /** @var \App\Models\Category|null $category */
                $category = $tx->category;
                if ($category && $category->tipe === 'pemasukan') {
                    $dailyData[$date]['pemasukan'] += (float)$tx->jumlah;
                } else {
                    $dailyData[$date]['pengeluaran'] += (float)$tx->jumlah;
                }
            }

            // Sort by date
            ksort($dailyData);

            $chartLabels = array_keys($dailyData);
            $chartPemasukanValues = [];
            $chartPengeluaranValues = [];

            foreach ($dailyData as $data) {
                $chartPemasukanValues[] = $data['pemasukan'];
                $chartPengeluaranValues[] = $data['pengeluaran'];
            }

            // Generate chart URLs using QuickChart API
            $lineChartUrl = $this->generateLineChartUrl($chartLabels, $chartPemasukanValues, $chartPengeluaranValues);
            $doughnutChartUrl = $this->generateDoughnutChartUrl($categoryBreakdown);

            // Download dan convert chart images to base64
            $lineChartBase64 = $this->getChartAsBase64($lineChartUrl);
            $doughnutChartBase64 = $this->getChartAsBase64($doughnutChartUrl);

            $summary = [
                'saldo_real' => $saldoTotal,
                'total_pemasukan' => $pemasukanPeriod,
                'total_pengeluaran' => $pengeluaranPeriod,
                'laba' => $pemasukanPeriod - $pengeluaranPeriod
            ];

            // Prepare data for PDF
            $pdfData = [
                'title' => 'Laporan Keuangan',
                'date' => date('d-m-Y H:i'),
                'sections' => $sections,
                'summary' => $summary,
                'transactions' => $transactions,
                'categoryBreakdown' => $categoryBreakdown,
                'lineChartBase64' => $lineChartBase64,
                'doughnutChartBase64' => $doughnutChartBase64,
                'company' => [
                    'name' => $user->perusahaan->nama_perusahaan ?? 'Usaha Saya',
                ]
            ];

            \Log::info('PDF Data prepared, transactions count: ' . count($pdfData['transactions']));

            // Generate PDF
            $pdf = Pdf::loadView('pdf.laporan-keuangan', $pdfData)
                ->setPaper('a4')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10)
                ->setOption('enable-local-file-access', true);

            \Log::info('PDF loaded and configured');

            $filename = 'Laporan_Keuangan_' . date('d-m-Y') . '.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            \Log::error('Stack: ' . $e->getTraceAsString());

            // Check if error is related to GD extension
            if (
                strpos($e->getMessage(), 'gd') !== false ||
                strpos($e->getMessage(), 'GD') !== false ||
                strpos($e->getMessage(), 'imagecreatetruecolor') !== false
            ) {
                return response()->json([
                    'error' => 'PHP GD extension is required, but is not installed',
                    'message' => 'Server tidak memiliki PHP GD extension. Hubungi hosting provider untuk mengaktifkannya.',
                    'solution' => 'Minta tim hosting untuk enable php_gd extension atau gunakan server production yang sudah dikonfigurasi.'
                ], 503);
            }

            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Line Chart URL using QuickChart API
     */
    private function generateLineChartUrl($labels, $pemasukanData, $pengeluaranData)
    {
        // Validate and convert to arrays
        if (!is_array($labels)) {
            $labels = [];
        }
        if (!is_array($pemasukanData)) {
            $pemasukanData = [];
        }
        if (!is_array($pengeluaranData)) {
            $pengeluaranData = [];
        }

        // Build QuickChart Chart.js config
        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Pemasukan',
                        'data' => $pemasukanData,
                        'borderColor' => '#4caf50',
                        'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                        'borderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true
                    ],
                    [
                        'label' => 'Pengeluaran',
                        'data' => $pengeluaranData,
                        'borderColor' => '#f44336',
                        'backgroundColor' => 'rgba(244, 67, 54, 0.1)',
                        'borderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'top'
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        $chartJson = json_encode($chartConfig);
        $encodedConfig = urlencode($chartJson !== false ? $chartJson : '');
        return "https://quickchart.io/chart?c={$encodedConfig}&w=800&h=400";
    }

    /**
     * Generate Doughnut Chart URL using QuickChart API
     */
    private function generateDoughnutChartUrl($categoryBreakdown)
    {
        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
        $backgroundColors = [];

        $colorIndex = 0;
        foreach ($categoryBreakdown as $category) {
            $labels[] = $category['nama'];
            $data[] = $category['total'];
            $backgroundColors[] = $colors[$colorIndex % count($colors)];
            $colorIndex++;
        }

        // Build QuickChart Chart.js config
        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $backgroundColors,
                        'borderColor' => '#fff',
                        'borderWidth' => 2
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];

        $chartJson = json_encode($chartConfig);
        $encodedConfig = urlencode($chartJson !== false ? $chartJson : '');
        return "https://quickchart.io/chart?c={$encodedConfig}&w=600&h=400";
    }

    /**
     * Download chart image dari URL dan convert ke base64
     */
    private function getChartAsBase64($chartUrl)
    {
        try {
            $imageContent = @file_get_contents($chartUrl);
            if ($imageContent === false) {
                \Log::warning('Failed to download chart from: ' . $chartUrl);
                return null;
            }

            return 'data:image/png;base64,' . base64_encode($imageContent);
        } catch (\Exception $e) {
            \Log::error('Error converting chart to base64: ' . $e->getMessage());
            return null;
        }
    }
}
