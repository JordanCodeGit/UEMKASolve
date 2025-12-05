<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintLaporanController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
/*
|--------------------------------------------------------------------------
| Rute Publik (Boleh diakses tanpa login)
|--------------------------------------------------------------------------
|
| Rute-rute ini adalah untuk tamu, seperti halaman login,
| register, dan landing page.
|
*/

// Rute '/' (Root/Landing Page)
Route::get('/', function () {
    // SECURITY: Jika sudah login, redirect ke dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
});

// Rute Halaman Auth Bawaan
Route::get('/login', function () {
    // SECURITY: Jika sudah login, redirect ke dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    // SECURITY: Jika sudah login, redirect ke dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.register');
})->name('register');

// ========== EMAIL VERIFICATION ROUTES ==========
// Halaman verifikasi email berhasil
Route::get('/email-verified', function () {
    return view('auth.email-verified');
})->name('email.verified');

// 1. Halaman Lupa Password
Route::get('/lupa-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/auth/google-success', function () {
    return view('auth.google-callback');
});

Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== TESTING ROUTE (DELETE SESSION UNTUK TESTING) =====
Route::get('/test-clear-session', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login')->with('success', 'Session cleared! Now you can test without login.');
})->name('test.clear.session');


/*
|--------------------------------------------------------------------------
| Rute Autentikasi Google
|--------------------------------------------------------------------------
*/
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| Rute Terlindungi (WAJIB Login untuk Mengakses)
|--------------------------------------------------------------------------
|
| Semua rute aplikasi Anda (dashboard, buku kas, dll.)
| HARUS berada di dalam grup middleware 'auth' ini.
|
*/

// 2. PERBAIKAN: Semua rute aplikasi dipindahkan ke dalam grup 'auth'
Route::middleware(['auth'])->group(function () {

    // Rute Dashboard
    Route::get('/dashboard', function () {
        // HAPUS semua logika pengecekan di sini.
        // Biarkan AppServiceProvider yang mengurus variabel $needsCompanySetup.
        return view('dashboard');
    })->name('dashboard');

    // Rute Buku Kas
    Route::get('/buku-kas', function () {
        return view('buku-kas');
    })->name('buku-kas');

    // Rute Kategori
    Route::get('/kategori', function () {
        return view('kategori');
    })->name('kategori');

    // Rute API untuk Dashboard Data (untuk print laporan)
    Route::get('/api/dashboard-data', [DashboardController::class, 'getData'])->name('dashboard.data');

    // Rute untuk generate PDF laporan
    Route::post('/api/print-laporan', [PrintLaporanController::class, 'generatePdf'])->name('print.laporan');

    // 3. PERBAIKAN: Rute /pengaturan yang duplikat dihapus.
    // Ini adalah satu-satunya rute pengaturan yang benar.
    Route::get('/pengaturan', [ProfileController::class, 'show'])->name('pengaturan.show');

    // Rute untuk memproses form setup perusahaan (popup)
    Route::post('/company-setup', [CompanySetupController::class, 'store'])
        ->name('company.setup.store');

    // Route Update Profil Usaha (yang sudah ada)
    Route::post('/pengaturan/update-usaha', [ProfileController::class, 'updateUsaha'])->name('pengaturan.update.usaha');

    // Route Update Profil Akun (BARU)
    Route::post('/pengaturan/update-akun', [ProfileController::class, 'updateAkun'])->name('pengaturan.update.akun');
});

// Load additional auth-related POST routes (register, login, verification, password actions)
require __DIR__ . '/auth.php';

// --- ROUTE DARURAT UNTUK CLEAR CACHE DI HOSTING ---
Route::get('/fix-config', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return 'Cache Cleared! Config, Route, View, Optimize cleared.';
});

// --- ROUTE DIAGNOSA (HANYA UNTUK TESTING) ---
Route::get('/cek-kodingan', function () {
    $files = [
        'routes/api.php' => base_path('routes/api.php'),
        'TransactionController.php' => app_path('Http/Controllers/Api/TransactionController.php'),
    ];

    echo "<h1>Hasil Intip File Server</h1>";

    foreach ($files as $name => $path) {
        echo "<h3>File: $name</h3>";
        if (file_exists($path)) {
            $content = file_get_contents($path);
            echo "<textarea style='width:100%; height:300px; font-family:monospace;'>" . htmlspecialchars($content) . "</textarea>";
        } else {
            echo "<p style='color:red'>File tidak ditemukan di path: $path</p>";
        }
        echo "<hr>";
    }
});
