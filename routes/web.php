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

// Rute '/' (Root/Landing Page)
Route::get('/', function () {
    return view('welcome');
});

// Rute Halaman Auth Bawaan
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/lupa-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/reset-password/{token}', function (Request $request, $token) {
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email
    ]);
})->name('password.reset');

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