<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        // Konfigurasi Routing
        using: function () {
            Route::middleware('api')
                 ->prefix('api')
                 ->group(base_path('routes/api.php'));

            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {

        // [PERBAIKAN DISINI]
        // 1. Daftarkan SecurityHeaders secara GLOBAL.
        //    Ini agar header keamanan (X-Frame, HSTS, dll) muncul di SEMUA response,
        //    baik itu halaman web, API, atau response error.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // 2. Middleware khusus grup 'web' tetap disini
        $middleware->web(append: [
            \App\Http\Middleware\CheckCompanySetup::class,
            // \App\Http\Middleware\CheckUserActivity::class, // TEMPORARILY DISABLED
        ]);

        // Opsional: Trust Proxies (Penting jika pakai Cloudflare/Load Balancer)
        // $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi exception
    })->create();
