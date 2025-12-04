<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class CheckCompanySetup
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // [FIX] Gunakan 'business', bukan 'perusahaan'
            // Karena di User.php sekarang namanya function business()
            $user->load('business');

            // Cek apakah relasi business ada isinya
            $needsSetup = $user->business === null;

            View::share('needsCompanySetup', $needsSetup);
            View::share('globalUser', $user);
        }

        return $next($request);
    }
}
