<?php

// -------------------------------------------------------------------------
// [START] SECURITY HEADERS INJECTION
// -------------------------------------------------------------------------
// Bagian ini memaksa server mengirim header keamanan
// untuk mem-bypass konfigurasi server/hosting yang memblokir .htaccess

// 1. Sembunyikan versi PHP (X-Powered-By)
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
}

// 2. Anti-Clickjacking (Mencegah web di-iframe orang lain)
header('X-Frame-Options: SAMEORIGIN');

// 3. Anti-MIME Sniffing (Mencegah browser menebak tipe file)
header('X-Content-Type-Options: nosniff');

// 4. Strict Transport Security (HSTS) - Memaksa HTTPS
// (Hanya aktif jika akses saat ini sudah HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// 5. XSS Protection (Keamanan tambahan untuk browser lama)
header('X-XSS-Protection: 1; mode=block');

// -------------------------------------------------------------------------
// [END] SECURITY HEADERS INJECTION
// -------------------------------------------------------------------------

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
