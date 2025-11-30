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
            \Log::info('PrintLaporanController generatePdf called');
            
            $user = Auth::user();
            $idPerusahaan = $user->id_perusahaan;

            if (!$idPerusahaan) {
                \Log::error('No company ID for user');
                return response()->json(['error' => 'Company not set'], 400);
            }

            $sections = $request->get('sections', []);

            \Log::info('Sections:', $sections);

            // Validate that at least one section is selected
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

            \Log::info('Total transactions found: ' . count($allTransactions));

            // Calculate summary by iterating through transactions
            $pemasukanPeriod = 0;
            $pengeluaranPeriod = 0;
            
            foreach ($allTransactions as $tx) {
                if ($tx->category && $tx->category->tipe === 'pemasukan') {
                    $pemasukanPeriod += (float)$tx->jumlah;
                } elseif ($tx->category && $tx->category->tipe === 'pengeluaran') {
                    $pengeluaranPeriod += (float)$tx->jumlah;
                }
            }
            
            // Calculate total balance (all time, not just this month)
            $allTimePemasukan = Transaction::where('business_id', $idPerusahaan)
                ->with('category')
                ->get()
                ->filter(function($tx) {
                    return $tx->category && $tx->category->tipe === 'pemasukan';
                })
                ->sum('jumlah');

            $allTimePengeluaran = Transaction::where('business_id', $idPerusahaan)
                ->with('category')
                ->get()
                ->filter(function($tx) {
                    return $tx->category && $tx->category->tipe === 'pengeluaran';
                })
                ->sum('jumlah');

            $saldoTotal = $allTimePemasukan - $allTimePengeluaran;

            // Get recent transactions (limit to 10)
            $transactions = $allTransactions->take(10);

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
                'company' => [
                    'name' => $user->perusahaan?->nama_perusahaan ?? 'Usaha Saya',
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

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            \Log::error('Stack: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }
}
