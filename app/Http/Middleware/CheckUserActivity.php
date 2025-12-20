<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivity
{
    /**
     * Auto Logout setelah 20 jam IDLE (tidak ada aktivitas)
     *
     * Remember Me: 12 jam (dengan activity tracking)
     * Auto Logout: 20 jam (idle/tidak ada aktivitas)
     */
    // Kode fungsi mengecek aktivitas user (auto logout)
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            assert($user !== null);
            $userId = $user->id;

            // Cache key untuk track last activity
            $activityKey = "user_activity_{$userId}";
            $maxIdleTime = 20 * 60 * 60; // 20 jam dalam detik

            // Ambil waktu aktivitas terakhir
            $lastActivity = Cache::get($activityKey);
            $now = now();

            if ($lastActivity) {
                // Hitung selisih waktu
                $lastActivityStr = is_string($lastActivity) ? $lastActivity : '';
                $idleTime = $now->diffInSeconds($lastActivityStr);

                // Jika lebih dari 20 jam idle → logout
                if ($idleTime > $maxIdleTime) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    Cache::forget($activityKey);

                    return response()->json([
                        'message' => 'Session Anda telah berakhir karena tidak ada aktivitas selama 20 jam. Silakan login kembali.',
                        'redirect' => '/login'
                    ], 419); // 419 = Session Expired
                }
            }

            // Update activity timestamp di cache (durasi 24 jam untuk safety)
            Cache::put($activityKey, $now, $maxIdleTime + (60 * 60)); // Simpan 1 jam lebih lama dari max idle
        }

        return $next($request);
    }
}
