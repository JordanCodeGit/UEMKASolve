<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Konfigurasi View Composer
        // Kode ini otomatis mengirim variabel $globalUser & $needsCompanySetup ke SEMUA file blade
        View::composer('*', function ($view) {
            $user = Auth::user();

            if ($user) {
                // [LOGIC BARU]
                // 1. Load relasi 'business' (sesuai fungsi baru di User.php)
                //    Ini akan mencari data di tabel businesses.
                $user->load('business');

                // 2. Cek apakah user sudah punya bisnis?
                //    Jika object $user->business itu null, artinya belum setup.
                $needsSetup = $user->business === null;

                // Kirim variabel ke view
                $view->with('needsCompanySetup', $needsSetup);
                $view->with('globalUser', $user);
            }
        });
    }
}
