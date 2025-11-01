<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF facade
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Generate dan download laporan PDF.
     * Sesuai diagram "Mengunduh Laporan PDF".
     */
    public function downloadReport(Request $request)
    {
        // 1. Validasi input tanggal
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // 2. Ambil data bisnis (Otorisasi)
        $user = Auth::user();
        $business = $user->business;

        if (!$business) {
            return response()->json(['message' => 'Bisnis tidak ditemukan.'], 404);
        }

        // 3. Ambil data transaksi sesuai rentang tanggal & business_id
        $transactionsQuery = $business->transactions()
                                ->with('category')
                                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                                ->orderBy('tanggal_transaksi', 'asc');

        $transactions = $transactionsQuery->get();

        // 4. Hitung Total (Gunakan logic yang sama dari DashboardService)
        // (Anda bisa refactor ini ke Service jika mau)
        $totalPemasukan = (float) $transactionsQuery->clone() // Clone query agar tidak terpengaruh
                                    ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
                                    ->sum('jumlah');

        $totalPengeluaran = (float) $transactionsQuery->clone()
                                    ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
                                    ->sum('jumlah');

        $laba = $totalPemasukan - $totalPengeluaran;

        // 5. Siapkan data untuk dikirim ke Blade PDF
        $data = [
            'businessName' => $business->nama_usaha,
            'startDate' => Carbon::parse($startDate)->format('d F Y'),
            'endDate' => Carbon::parse($endDate)->format('d F Y'),
            'transactions' => $transactions,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'laba' => $laba,
        ];

        // 6. Load view Blade dan generate PDF
        $pdf = Pdf::loadView('reports.financial_report', $data);

        // 7. Buat nama file yang dinamis
        $filename = 'Laporan_Keuangan_' . $business->nama_usaha . '_' . $startDate . '_sd_' . $endDate . '.pdf';

        // 8. Kembalikan PDF sebagai download
        // 'download()' akan mengirimkan file ke browser
        return $pdf->download($filename);
    }
}
