<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // 1. Fix: Strict-Transport-Security (HSTS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // 2. Fix: X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. Fix: X-Frame-Options (Anti-clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 4. Fix: X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 5. Fix: Menghapus X-Powered-By
        $response->headers->remove('X-Powered-By');

        // -----------------------------------------------------------
        // BAGIAN INI KITA MATIKAN DULU (COMMENT OUT)
        // KARENA MEMBLOKIR CSS/JS VITE ANDA
        // -----------------------------------------------------------
        /*
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
               "connect-src 'self';";

        $response->headers->set('Content-Security-Policy', $csp);
        */

        return $response;
    }
}
