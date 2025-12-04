<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SetupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rute Publik (Tanpa Login) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// --- Google Auth ---
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');

// --- Rute Terproteksi (Login Wajib) ---
Route::middleware(['auth:sanctum'])->group(function () {

    // 1. User & Auth
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. Email Verification
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.'], 200);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Link verifikasi baru telah dikirim.'], 200);
    })->middleware(['throttle:6,1'])->name('verification.send.api');

    // 3. Activity Tracking
    Route::post('/update-activity', function (Request $request) {
        return response()->json(['message' => 'Activity updated']);
    })->name('activity.update');

    // 4. Setup Bisnis
    Route::post('/setup-perusahaan', [SetupController::class, 'store']);

    // 5. Dashboard Data
    Route::get('/dashboard', [DashboardController::class, 'getSummary']);

    // 6. Transaksi (CRUD MANUAL - EKSPLISIT)
    // Diubah dari apiResource agar Hosting mengenali DELETE/PUT dengan pasti (Anti 404)
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // 7. Kategori (CRUD MANUAL - EKSPLISIT)
    // Diubah dari apiResource untuk memperbaiki error drag-drop & delete (Anti 404)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // 8. Profile
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    // 9. Report
    Route::get('/report/download', [ReportController::class, 'downloadReport']);
});
