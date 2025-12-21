<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintLaporanController;

/*
|--------------------------------------------------------------------------
| 1. Rute Publik (Tamu)
|--------------------------------------------------------------------------
*/

// Halaman Login & Register (Redirect ke dashboard jika sudah login)
Route::middleware('guest')->group(function () {
    Route::get('/', function () { return view('auth.login'); });
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::get('/register', function () { return view('auth.register'); })->name('register');

    // Auth Google
    Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Auth Process
    Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');

    // Lupa Password & Verifikasi
    Route::get('/lupa-password', function () { return view('auth.forgot-password'); })->name('password.request');
    Route::get('/email-verified', function () { return view('auth.email-verified'); })->name('email.verified');
});

Route::get('/auth/google-success', function () { return view('auth.google-callback'); });


/*
|--------------------------------------------------------------------------
| 2. Rute Terlindungi (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- RUTE LOGOUT (PENTING) ---
    // Kita definisikan eksplisit di sini agar aman
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');

    // Buku Kas & Kategori
    Route::get('/buku-kas', function () { return view('buku-kas'); })->name('buku-kas');
    Route::get('/kategori', function () { return view('kategori'); })->name('kategori');

    // Pengaturan & Profil
    Route::get('/pengaturan', [ProfileController::class, 'show'])->name('pengaturan.show');
    Route::post('/pengaturan/update-usaha', [ProfileController::class, 'updateUsaha'])->name('pengaturan.update.usaha');
    Route::post('/pengaturan/update-akun', [ProfileController::class, 'updateAkun'])->name('pengaturan.update.akun');

    // Setup Awal Perusahaan
    Route::post('/company-setup', [CompanySetupController::class, 'store'])->name('company.setup.store');

    // API Internal (untuk Chart & PDF)
    Route::get('/api/dashboard-data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::post('/api/print-laporan', [PrintLaporanController::class, 'generatePdf'])->name('print.laporan');
});


/*
|--------------------------------------------------------------------------
| 3. Rute Maintenance & Debug (Hapus saat Production)
|--------------------------------------------------------------------------
*/
Route::get('/fix-config', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return 'Cache & Route Cleared!';
});

// Load route auth bawaan (jika masih diperlukan untuk reset password, dsb)
require __DIR__ . '/auth.php';
