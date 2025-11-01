<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Import CarbonPeriod

class DashboardService
{
    /**
     * Mengambil data ringkasan untuk dashboard.
     *
     * @param Business $business
     * @param array $dateRange
     * @return array
     */
    public function getDashboardSummary(Business $business, array $dateRange): array
    {
        // 1. Ambil data Pemasukan, Pengeluaran, dan Laba (Periode)
        $periodPemasukan = $this->calculateSum($business, 'pemasukan', $dateRange);
        $periodPengeluaran = $this->calculateSum($business, 'pengeluaran', $dateRange);
        $periodLaba = $periodPemasukan - $periodPengeluaran;

        // 2. Ambil Saldo Keseluruhan (Sepanjang Masa)
        $totalPemasukan = $this->calculateSum($business, 'pemasukan');
        $totalPengeluaran = $this->calculateSum($business, 'pengeluaran');
        $totalSaldo = $totalPemasukan - $totalPengeluaran;

        // 3. Ambil Transaksi Terakhir (5 Transaksi)
        $transaksiTerakhir = $business->transactions()
                                 ->with('category:id,nama_kategori,tipe,ikon') // Ambil ikon juga
                                 ->latest('tanggal_transaksi')
                                 ->limit(5)
                                 ->get();

        // 4. [BARU] Ambil Data Grafik Kas (Line Chart)
        $lineChartData = $this->getLineChartData($business, $dateRange);

        // 5. [BARU] Ambil Data Persentase Kas (Doughnut Chart)
        $doughnutChartData = $this->getDoughnutChartData($business, $dateRange, $periodPengeluaran);

        return [
            'summary' => [
                'saldo' => $totalSaldo,
                'pemasukan' => $periodPemasukan,
                'pengeluaran' => $periodPengeluaran,
                'laba' => $periodLaba,
            ],
            'line_chart' => $lineChartData,
            'doughnut_chart' => $doughnutChartData,
            'recent_transactions' => $transaksiTerakhir,
        ];
    }

    /**
     * Helper function untuk menghitung SUM transaksi.
     * (Tidak berubah)
     */
    private function calculateSum(Business $business, string $tipe, array $dateRange = null): float
    {
        $query = $business->transactions()
                         ->whereHas('category', function ($q) use ($tipe) {
                             $q->where('tipe', $tipe);
                         });

        if ($dateRange && isset($dateRange['startDate']) && isset($dateRange['endDate'])) {
            $query->whereBetween('tanggal_transaksi', [$dateRange['startDate'], $dateRange['endDate']]);
        }

        return (float) $query->sum('jumlah');
    }

    /**
     * [BARU] Helper untuk mengambil data Line Chart (Pemasukan vs Pengeluaran Harian)
     */
    private function getLineChartData(Business $business, array $dateRange): array
    {
        $startDate = Carbon::parse($dateRange['startDate']);
        $endDate = Carbon::parse($dateRange['endDate']);

        // Buat daftar semua tanggal dalam rentang
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        $labels = [];
        $pemasukanData = [];
        $pengeluaranData = [];

        // Inisialisasi semua tanggal dengan nilai 0
        foreach ($period as $date) {
            $day = $date->format('d'); // Label (misal: '01', '02', ..., '31')
            $labels[$day] = $day;
            $pemasukanData[$day] = 0;
            $pengeluaranData[$day] = 0;
        }

        // Ambil data Pemasukan harian
        $pemasukanDb = $business->transactions()
            ->whereHas('category', fn($q) => $q->where('tipe', 'pemasukan'))
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->select(
                DB::raw('DAY(tanggal_transaksi) as hari'),
                DB::raw('SUM(jumlah) as total')
            )
            ->groupBy('hari')
            ->pluck('total', 'hari'); // Hasil: [ '1' => 150000, '5' => 50000, ... ]

        // Ambil data Pengeluaran harian
        $pengeluaranDb = $business->transactions()
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->select(
                DB::raw('DAY(tanggal_transaksi) as hari'),
                DB::raw('SUM(jumlah) as total')
            )
            ->groupBy('hari')
            ->pluck('total', 'hari');

        // Isi array data dengan data dari DB
        foreach ($pemasukanDb as $hari => $total) {
            $pemasukanData[str_pad($hari, 2, '0', STR_PAD_LEFT)] = $total;
        }
        foreach ($pengeluaranDb as $hari => $total) {
            $pengeluaranData[str_pad($hari, 2, '0', STR_PAD_LEFT)] = $total;
        }

        return [
            // Kirim array yang index-nya sudah di-reset (agar jadi array JSON, bukan object)
            'labels' => array_values($labels),
            'datasets' => [
                ['label' => 'Pemasukan', 'data' => array_values($pemasukanData)],
                ['label' => 'Pengeluaran', 'data' => array_values($pengeluaranData)],
            ]
        ];
    }

    /**
     * [BARU] Helper untuk mengambil data Doughnut Chart (Persentase Pengeluaran per Kategori)
     */
    private function getDoughnutChartData(Business $business, array $dateRange, float $totalPengeluaran): array
    {
        if ($totalPengeluaran == 0) {
            return [
                'labels' => ['Belum ada data'],
                'data' => [100]
            ];
        }

        $data = $business->transactions()
            ->whereHas('category', fn($q) => $q->where('tipe', 'pengeluaran'))
            ->whereBetween('tanggal_transaksi', [$dateRange['startDate'], $dateRange['endDate']])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                'categories.nama_kategori as label',
                DB::raw('SUM(transactions.jumlah) as total')
            )
            ->groupBy('label')
            ->orderBy('total', 'desc') // Urutkan dari terbesar
            ->limit(5) // Ambil 5 kategori teratas
            ->get(); // Hasil: Collection [{label: 'Gaji', total: 5000}, ...]

        // Pisahkan label dan data
        $labels = $data->pluck('label');
        $totals = $data->pluck('total');

        // (Opsional) Hitung 'Lainnya' jika ada lebih dari 5 kategori
        // ...

        return [
            'labels' => $labels,
            'data' => $totals,
        ];
    }
}
