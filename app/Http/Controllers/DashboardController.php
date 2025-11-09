<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DashboardService; // Import service
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Import Carbon
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $dashboardService;

    // Inject DashboardService ke controller
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handle request untuk mengambil data dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSummary(Request $request): JsonResponse
    {
        // 1. Validasi input (tambahkan 'doughnut_tipe')
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'search' => 'nullable|string|max:100',
            // [PERBAIKAN] Tambahkan validasi untuk filter doughnut
            'doughnut_tipe' => ['nullable', Rule::in(['pemasukan', 'pengeluaran'])],
        ]);

        // 2. Ambil user & bisnis (tetap sama)
        $user = Auth::user();
        $business = $user->business;
        if (!$business) {
            return response()->json(['message' => 'Data bisnis tidak ditemukan.'], 404);
        }

        // 3. Tentukan rentang tanggal (tetap sama)
        $dateRange = [
            'startDate' => $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString(),
            'endDate' => $validated['end_date'] ?? Carbon::now()->endOfMonth()->toDateString(),
        ];

        // 4. Ambil search query (tetap sama)
        $searchQuery = $validated['search'] ?? null;
        
        // 5. [PERBAIKAN] Ambil tipe doughnut
        $doughnutTipe = $validated['doughnut_tipe'] ?? 'pengeluaran'; // Default 'pengeluaran'
        Log::info('Dashboard request received with doughnut_tipe: ' . $doughnutTipe);

        // 6. Panggil service dengan parameter baru
        try {
            // [PERBAIKAN] Teruskan $doughnutTipe
            $summaryData = $this->dashboardService->getDashboardSummary(
                $business, 
                $dateRange, 
                $searchQuery,
                $doughnutTipe // <-- Parameter baru
            ); 
            
            return response()->json($summaryData, 200);

        } catch (\Exception $e) {
            // Log::error('Gagal mengambil data dashboard: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data dashboard.', 'error' => $e->getMessage()], 500);
        }
    }
}
