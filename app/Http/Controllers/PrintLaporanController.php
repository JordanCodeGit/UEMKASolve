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
            // Check PHP GD extension
            if (!extension_loaded('gd')) {
                \Log::warning('PHP GD extension not loaded');
                return response()->json([
                    'error' => 'PDF generation requires PHP GD extension',
                    'message' => 'Server tidak memiliki PHP GD extension.',
                    'solution' => 'Hubungi hosting provider untuk mengaktifkan PHP GD extension.'
                ], 503);
            }

            $user = Auth::user();

            // [FIX] Ambil ID Bisnis dari relasi business (bukan id_perusahaan)
            // Pastikan user->business tidak null
            if (!$user || !$user->business) {
                \Log::error('No business found for user: ' . ($user->id ?? 'unknown'));
                return response()->json([
                    'error' => 'Business not found',
                    'message' => 'Anda belum memiliki profil bisnis. Silakan buat profil bisnis terlebih dahulu.'
                ], 400);
            }

            $idPerusahaan = $user->business->id;

            $sections = $request->get('sections', []);
            $sections = (array)$sections;

            if (!($sections['ringkasan'] ?? false) && !($sections['grafik'] ?? false) && !($sections['rincian'] ?? false)) {
                return response()->json(['error' => 'Pilih minimal satu bagian laporan.'], 400);
            }

            // Fetch fresh data
            $now = Carbon::now();
            $startDate = $now->clone()->startOfMonth();
            $endDate = $now->clone()->endOfMonth();

            // [FIX] Query menggunakan business_id yang benar
            $allTransactions = Transaction::where('business_id', $idPerusahaan)
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->with('category')
                ->latest('tanggal_transaksi')
                ->get();

            // Calculate Period Summary
            $pemasukanPeriod = 0;
            $pengeluaranPeriod = 0;

            foreach ($allTransactions as $tx) {
                $category = $tx->category;
                if ($category && $category->tipe === 'pemasukan') {
                    $pemasukanPeriod += (float)$tx->jumlah;
                } elseif ($category && $category->tipe === 'pengeluaran') {
                    $pengeluaranPeriod += (float)$tx->jumlah;
                }
            }

            // Calculate All Time Balance
            // [FIX] Query all time juga pakai business_id yang benar
            $allTimePemasukan = Transaction::where('business_id', $idPerusahaan)
                ->whereHas('category', function($q) { $q->where('tipe', 'pemasukan'); })
                ->sum('jumlah');

            $allTimePengeluaran = Transaction::where('business_id', $idPerusahaan)
                ->whereHas('category', function($q) { $q->where('tipe', 'pengeluaran'); })
                ->sum('jumlah');

            $saldoTotal = $allTimePemasukan - $allTimePengeluaran;

            // Recent Transactions (Limit 10)
            $transactions = $allTransactions->take(10);

            // Category Breakdown
            $categoryBreakdown = [];
            foreach ($allTransactions as $tx) {
                $category = $tx->category;
                if (!$category) continue;

                $name = $category->nama_kategori;
                if (!isset($categoryBreakdown[$name])) {
                    $categoryBreakdown[$name] = ['nama' => $name, 'tipe' => $category->tipe, 'total' => 0, 'count' => 0];
                }
                $categoryBreakdown[$name]['total'] += (float)$tx->jumlah;
                $categoryBreakdown[$name]['count'] += 1;
            }

            // Chart Data Preparation (Daily)
            $dailyData = [];
            foreach ($allTransactions as $tx) {
                $date = $tx->tanggal_transaksi->format('d');
                if (!isset($dailyData[$date])) $dailyData[$date] = ['pemasukan' => 0, 'pengeluaran' => 0];

                if ($tx->category->tipe === 'pemasukan') $dailyData[$date]['pemasukan'] += (float)$tx->jumlah;
                else $dailyData[$date]['pengeluaran'] += (float)$tx->jumlah;
            }
            ksort($dailyData);

            $chartLabels = array_keys($dailyData);
            $chartPemasukan = array_column($dailyData, 'pemasukan');
            $chartPengeluaran = array_column($dailyData, 'pengeluaran');

            // Generate Charts
            $lineChartBase64 = null;
            $doughnutChartBase64 = null;

            if ($sections['grafik'] ?? false) {
                $lineUrl = $this->generateLineChartUrl($chartLabels, $chartPemasukan, $chartPengeluaran);
                $doughUrl = $this->generateDoughnutChartUrl($categoryBreakdown);

                $lineChartBase64 = $this->getChartAsBase64($lineUrl);
                $doughnutChartBase64 = $this->getChartAsBase64($doughUrl);
            }

            $summary = [
                'saldo_real' => $saldoTotal,
                'total_pemasukan' => $pemasukanPeriod,
                'total_pengeluaran' => $pengeluaranPeriod,
                'laba' => $pemasukanPeriod - $pengeluaranPeriod
            ];

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
                    // [FIX] Ambil nama usaha dari relasi business->nama_usaha
                    'name' => $user->business->nama_usaha ?? 'Usaha Saya',
                ]
            ];

            $pdf = Pdf::loadView('pdf.laporan-keuangan', $pdfData)
                ->setPaper('a4')
                ->setOption(['isRemoteEnabled' => true]); // Penting untuk gambar

            return $pdf->download('Laporan_Keuangan_' . date('d-m-Y') . '.pdf');

        } catch (\Throwable $e) {
            \Log::error('PDF Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat PDF: ' . $e->getMessage()], 500);
        }
    }

    private function generateLineChartUrl($labels, $pemasukan, $pengeluaran)
    {
        $config = [
            'type' => 'bar', // Ubah ke Bar agar konsisten dengan dashboard
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Masuk', 'data' => $pemasukan, 'backgroundColor' => 'rgba(76, 175, 80, 0.7)'],
                    ['label' => 'Keluar', 'data' => $pengeluaran, 'backgroundColor' => 'rgba(244, 67, 54, 0.7)']
                ]
            ]
        ];
        return "https://quickchart.io/chart?c=" . urlencode(json_encode($config)) . "&w=600&h=300";
    }

    private function generateDoughnutChartUrl($breakdown)
    {
        $labels = [];
        $data = [];
        foreach ($breakdown as $cat) {
            $labels[] = $cat['nama'];
            $data[] = $cat['total'];
        }
        $config = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [['data' => $data]]
            ],
            'options' => ['plugins' => ['legend' => ['position' => 'right']]]
        ];
        return "https://quickchart.io/chart?c=" . urlencode(json_encode($config)) . "&w=500&h=300";
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
