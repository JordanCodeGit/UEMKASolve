<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\TransactionController;
/*
|--------------------------------------------------------------------------
| Rute Publik (Boleh diakses tanpa login)
|--------------------------------------------------------------------------
|
| Rute-rute ini adalah untuk tamu, seperti halaman login,
| register, dan landing page.
|
*/

// Rute Halaman Auth Bawaan
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// 1. Halaman Lupa Password
Route::get('/lupa-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

// 2. Proses Kirim Email (POST)
Route::post('/lupa-password', [AuthController::class, 'forgotPassword'])->name('password.email');

// 3. Halaman Reset Password (Link dari Email)
Route::get('/reset-password/{token}', function (Illuminate\Http\Request $request, $token) {
    return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
})->name('password.reset');

// 4. Proses Reset Password (POST)
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('/auth/google-success', function () {
    return view('auth.google-callback');
});

Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');


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
        $user = Illuminate\Support\Facades\Auth::user();
        
        // Cek apakah id_perusahaan kosong
        $needsCompanySetup = is_null($user->id_perusahaan);
        
        return view('dashboard', compact('needsCompanySetup'));
    })->name('dashboard');

    // Rute Buku Kas
    Route::get('/buku-kas', function () {
        return view('buku-kas');
    })->name('buku-kas');

    // Rute Kategori
    Route::get('/kategori', function () {
        return view('kategori');
    })->name('kategori');

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