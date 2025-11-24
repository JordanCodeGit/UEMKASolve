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
                // Cek apakah perlu setup perusahaan (jika id_perusahaan null)
                $needsSetup = is_null($user->id_perusahaan);
                
                // Kirim variabel ke view
                $view->with('needsCompanySetup', $needsSetup);
                $view->with('globalUser', $user);
                
                // (Opsional) Load data perusahaan biar bisa dipanggil $globalUser->perusahaan->logo
                if (!$needsSetup && !$user->relationLoaded('perusahaan')) {
                    $user->load('perusahaan');
                }
            }
        });
    }
}
