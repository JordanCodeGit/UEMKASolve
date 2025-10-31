<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DashboardService; // Import service
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Import Carbon

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
        // 1. Validasi input tanggal (opsional, bisa dibuat Form Request)
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        // 2. Ambil user yang sedang login
        $user = Auth::user();

        // 3. Ambil bisnis milik user (Otorisasi Sesuai Aturan)
        $business = $user->business;

        if (!$business) {
            return response()->json(['message' => 'Data bisnis tidak ditemukan.'], 404);
        }

        // 4. Tentukan rentang tanggal
        // Jika tidak ada input, default ke bulan ini
        $dateRange = [
            'startDate' => $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString(),
            'endDate' => $validated['end_date'] ?? Carbon::now()->endOfMonth()->toDateString(),
        ];

        // 5. Panggil service untuk menghitung data
        try {
            $summaryData = $this->dashboardService->getDashboardSummary($business, $dateRange);

            return response()->json($summaryData, 200);

        } catch (\Exception $e) {
            // Log::error('Gagal mengambil data dashboard: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data dashboard.'], 500);
        }
    }
}
