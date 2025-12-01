<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View; // <-- Tambahkan ini

class CheckCompanySetup
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user(); // 1. Ambil User
            assert($user !== null);
            $needsSetup = $user->id_perusahaan === null;

            // 2. Ambil data perusahaan JIKA SUDAH ADA
            if (!$needsSetup) {
                $user->load('perusahaan');
            }

            // 3. Bagikan status popup ke view
            View::share('needsCompanySetup', $needsSetup);

            // 4. (BARU) Bagikan data user LENGKAP ke semua view
            // Kita beri nama 'globalUser' agar tidak bentrok
            View::share('globalUser', $user);
        }

        return $next($request);
    }
}
