<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Hapus 'api: ...' dari sini jika menggunakan closure 'using:' di bawah
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        // Gunakan closure 'using:' untuk kontrol penuh atas routing API
        using: function () {
            // Daftarkan route API dengan middleware 'api' dan prefix 'api'
            Route::middleware('api')
                 ->prefix('api')
                 ->group(base_path('routes/api.php'));

            // Daftarkan route web
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- DAFTARKAN SECURITY HEADERS DISINI ---

        $middleware->web(append: [
            // Middleware keamanan yang baru kita buat (ZAP Fix)
            \App\Http\Middleware\SecurityHeaders::class,

            // Middleware Anda sebelumnya
            \App\Http\Middleware\CheckCompanySetup::class,
            // \App\Http\Middleware\CheckUserActivity::class, // TEMPORARILY DISABLED
        ]);

        // Opsional: Pastikan proxy dipercaya jika di belakang Cloudflare/Load Balancer
        // $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan exception (jika perlu)
    })->create();
