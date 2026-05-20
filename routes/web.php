<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PrintLaporanController;
use App\Http\Controllers\MemberController;

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

    // Onboarding pemilihan role pertama kali
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buku Kas & Kategori
    Route::get('/buku-kas', function () {
        if (!Auth::user()->role && session('show_role_onboarding')) return redirect()->route('onboarding.show');
        return view(Auth::user()->role === 'sekretaris' ? 'sekretaris.dashboard' : 'buku-kas');
    })->name('buku-kas');
    Route::get('/kategori', function () {
        if (!Auth::user()->role && session('show_role_onboarding')) return redirect()->route('onboarding.show');
        if (in_array(Auth::user()->role, ['owner', 'bendahara'], true)) return redirect()->route('dashboard');
        return view('kategori');
    })->name('kategori');

    Route::get('/anggota', [MemberController::class, 'index'])->name('anggota.index');
    Route::post('/anggota', [MemberController::class, 'store'])->name('anggota.store');
    Route::delete('/anggota/{member}', [MemberController::class, 'destroy'])->name('anggota.destroy');
    Route::post('/invitations/{member}/accept', [MemberController::class, 'acceptPending'])->name('members.accept.pending');
    Route::post('/invitations/{member}/reject', [MemberController::class, 'rejectPending'])->name('members.reject.pending');

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

Route::get('/invitations/{token}/accept', [MemberController::class, 'accept'])->name('members.accept');


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
