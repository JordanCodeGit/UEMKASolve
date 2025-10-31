<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Untuk menangani tanggal

class DashboardService
{
    /**
     * Mengambil data ringkasan untuk dashboard.
     *
     * @param Business $business Bisnis milik user yang sedang login
     * @param array $dateRange Array berisi 'startDate' dan 'endDate'
     * @return array
     */
    public function getDashboardSummary(Business $business, array $dateRange): array
    {
        // 1. Ambil data Pemasukan, Pengeluaran, dan Laba berdasarkan rentang tanggal
        $periodPemasukan = $this->calculateSum($business, 'pemasukan', $dateRange);
        $periodPengeluaran = $this->calculateSum($business, 'pengeluaran', $dateRange);
        $periodLaba = $periodPemasukan - $periodPengeluaran;

        // 2. Ambil Saldo Keseluruhan (Total Pemasukan - Total Pengeluaran sepanjang masa)
        // Ini sesuai asumsi kita dari desain UI
        $totalPemasukan = $this->calculateSum($business, 'pemasukan');
        $totalPengeluaran = $this->calculateSum($business, 'pengeluaran');
        $totalSaldo = $totalPemasukan - $totalPengeluaran;

        // 3. Ambil Transaksi Terakhir (misal 5 transaksi)
        $transaksiTerakhir = $business->transactions()
                                 ->with('category') // Eager load relasi kategori
                                 ->latest('tanggal_transaksi') // Urutkan dari yg terbaru
                                 ->limit(5) // Ambil 5 data
                                 ->get();

        return [
            'saldo' => $totalSaldo,
            'pemasukan' => $periodPemasukan,
            'pengeluaran' => $periodPengeluaran,
            'laba' => $periodLaba,
            'transaksi_terakhir' => $transaksiTerakhir,
            // Anda bisa tambahkan data untuk chart di sini
        ];
    }

    /**
     * Helper function untuk menghitung SUM transaksi.
     *
     * @param Business $business
     * @param string $tipe ('pemasukan' atau 'pengeluaran')
     * @param array|null $dateRange
     * @return float
     */
    private function calculateSum(Business $business, string $tipe, array $dateRange = null): float
    {
        // Mulai query dari transaksi milik bisnis ini
        $query = $business->transactions()
                         ->whereHas('category', function ($q) use ($tipe) {
                             // Filter berdasarkan tipe kategori
                             $q->where('tipe', $tipe);
                         });

        // Jika ada rentang tanggal, terapkan filter
        if ($dateRange && isset($dateRange['startDate']) && isset($dateRange['endDate'])) {
            $query->whereBetween('tanggal_transaksi', [$dateRange['startDate'], $dateRange['endDate']]);
        }

        // Hitung jumlah
        return (float) $query->sum('jumlah');
    }
}
