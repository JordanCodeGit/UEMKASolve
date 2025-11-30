<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route; // Pastikan facade Route di-import

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
        // Daftarkan middleware global Anda di sini (jika ada)
        // Contoh:
        // $middleware->web(append: [
        //     \App\Http\Middleware\ExampleMiddleware::class,
        // ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckCompanySetup::class,
            // \App\Http\Middleware\CheckUserActivity::class, // TEMPORARILY DISABLED - Testing session persistence
        ]);

        // Middleware API (seperti throttle) biasanya sudah diatur di $middleware->api(...)
        // di kernel atau langsung di route group 'api' Laravel.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan exception (jika perlu)
    })->create();
