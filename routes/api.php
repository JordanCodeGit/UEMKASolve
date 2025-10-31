<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest; // Tambahkan ini jika belum ada
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// [BENAR] Route registrasi (Publik, tidak butuh auth)
Route::post('/register', [AuthController::class, 'register']);

// [TAMBAHKAN INI] Route login (Publik)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Contoh: 5 percobaan login per menit

// [TAMBAHKAN INI] Route Forgot & Reset Password (Publik)
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1') // Batasi request forgot password
        ->name('password.email'); // Beri nama route (standar Laravel)

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1') // Batasi request reset password
        ->name('password.update'); // Beri nama route (standar Laravel)

// [TAMBAHKAN INI] Rute Google Auth
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');

// Route verifikasi email (Harus publik tapi butuh 'signed')
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    // Redirect ke halaman sukses di front-end Anda
    return redirect(env('FRONTEND_URL', 'http://localhost:3000').'/email-verified');
})->middleware(['signed'])->name('verification.verify');


// --- Rute lain yang butuh otentikasi masuk ke sini ---
Route::middleware(['auth:sanctum'])->group(function () {

    // Contoh: Mengambil data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Contoh: Mengirim ulang email verifikasi
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.'], 200);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Link verifikasi baru telah dikirim.'], 200);
    })->middleware(['throttle:6,1'])->name('verification.send');


    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'getSummary']);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('categories', CategoryController::class);

    // --- Endpoint Dashboard, Buku Kas, Kategori, dll. akan ada di sini nanti ---

});

// --- Rute Login & Forgot Password (Publik) akan ditambahkan di sini nanti ---
