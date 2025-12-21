<?php

// [START] SECURITY HEADERS - BRUTE FORCE MODE
// Memaksa header keamanan dikirim sepagi mungkin
// untuk melawan konfigurasi default server hosting.

if (!headers_sent()) {
    // 1. Anti-Clickjacking (Agar tidak bisa di-iframe)
    header('X-Frame-Options: SAMEORIGIN');

    // 2. Anti-MIME Sniffing
    header('X-Content-Type-Options: nosniff');

    // 3. XSS Protection
    header('X-XSS-Protection: 1; mode=block');

    // 4. HSTS (Paksa HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // 5. Coba hapus jejak PHP
    if (function_exists('header_remove')) {
        header_remove('X-Powered-By');
    }
}
// [END] SECURITY HEADERS

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Cek mode maintenance
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Load Composer
require __DIR__.'/../vendor/autoload.php';

// Jalankan Laravel
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
