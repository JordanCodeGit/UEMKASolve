<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySetup
{
    // Kode fungsi mengecek setup perusahaan
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // [FIX] Gunakan 'business', bukan 'perusahaan'
            // Karena di User.php sekarang namanya function business()
            $user->load('business');
            $activeBusiness = $user->activeBusiness();

            if (in_array($user->role, ['sekretaris', 'bendahara'], true) && !$activeBusiness) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akses usaha Anda sudah dicabut. Silakan login kembali.',
                    ], Response::HTTP_UNAUTHORIZED);
                }

                return redirect()->route('login')
                    ->with('error', 'Akses usaha Anda sudah dicabut. Silakan login kembali.');
            }

            // Cek apakah relasi business ada isinya
            $needsSetup = $user->role === 'owner' && $user->business === null;

            View::share('needsCompanySetup', $needsSetup);
            View::share('globalUser', $user);
            View::share('globalRole', $user->role);
            View::share('globalBusiness', $activeBusiness);
        }

        return $next($request);
    }
}
