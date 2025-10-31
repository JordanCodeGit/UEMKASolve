<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest; // Gunakan RegisterRequest
use App\Models\User;
use App\Models\Business;
use Illuminate\Auth\Events\Registered;   // Event untuk verifikasi email
use Illuminate\Support\Facades\DB;       // Untuk database transaction
use Illuminate\Support\Facades\Hash;     // Untuk hashing password
use Illuminate\Http\JsonResponse;        // Untuk type hinting response
// use Illuminate\Support\Facades\Log; // Uncomment jika ingin menggunakan Log
use App\Http\Requests\LoginRequest; // <-- Tambahkan ini
use Illuminate\Support\Facades\Auth; // <-- Tambahkan ini
use Illuminate\Http\Request;         // <-- Tambahkan ini (untuk logout nanti)
use App\Http\Requests\ForgotPasswordRequest; // <-- Tambahkan ini
use App\Http\Requests\ResetPasswordRequest;  // <-- Tambahkan ini
use Illuminate\Support\Facades\Password;     // <-- Tambahkan ini
use Illuminate\Auth\Events\PasswordReset;    // <-- Tambahkan ini
use Illuminate\Support\Str;                  // <-- Tambahkan ini
use Laravel\Socialite\Facades\Socialite; // <-- Tambahkan ini
use Exception;                          // <-- Tambahkan ini

class AuthController extends Controller
{
    /**
     * Handle user registration request.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Gunakan DB Transaction untuk memastikan User & Business dibuat bersamaan atau gagal bersamaan
        DB::beginTransaction();

        try {
            // 1. Ambil data yang sudah divalidasi dari RegisterRequest
            $validatedData = $request->validated();

            // 2. Buat User baru
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // 3. Buat Business baru yang terhubung ke User
            // Gunakan nama default jika 'nama_usaha' tidak ada di validasi
            $businessName = 'Bisnis ' . $validatedData['name'];

            $business = Business::create([
                'user_id' => $user->id,
                'nama_usaha' => $businessName,
                // logo_path akan dihandle terpisah (misal saat edit profil)
            ]);

            // 4. Trigger event 'Registered' bawaan Laravel
            // Ini akan otomatis mengirim email verifikasi jika User model
            // mengimplementasikan MustVerifyEmail
            event(new Registered($user));

            // 5. Commit transaction jika semua langkah berhasil
            DB::commit();

            // 6. Berikan respons sukses
            return response()->json([
                'message' => 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi.',
                // Kembalikan data user dasar (opsional, sesuaikan dengan kebutuhan FE)
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'business_name' => $business->nama_usaha,
                ]
            ], 201); // Kode status 201 Created

        } catch (\Throwable $e) { // Tangkap semua jenis error/exception
            // 7. Rollback transaction jika terjadi error di langkah manapun
            DB::rollBack();

            // 8. Log error (opsional tapi sangat disarankan di production)
            // Log::error('Registrasi gagal: ' . $e->getMessage());

            // 9. Berikan respons error generik ke client
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
                // 'error' => $e->getMessage() // Detail error sebaiknya tidak dikirim ke client di production
            ], 500); // Kode status 500 Internal Server Error
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Ambil kredensial yang sudah divalidasi
        $credentials = $request->validated();

        // 2. Coba lakukan autentikasi
        if (!Auth::attempt($credentials)) {
            // Jika email/password salah
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401); // Kode status 401 Unauthorized
        }

        // 3. Autentikasi berhasil, ambil data user
        $user = Auth::user();

        // 4. (Opsional tapi Direkomendasikan) Cek Verifikasi Email
        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'message' => 'Email Anda belum diverifikasi. Silakan cek email Anda.'
        //     ], 403); // Kode status 403 Forbidden
        // }

        // 5. Buat token API Sanctum
        // Gunakan email user sebagai nama perangkat default jika tidak dikirim
        $deviceName = $request->input('device_name', $user->email);
        $token = $user->createToken($deviceName)->plainTextToken;

        // 6. Ambil data bisnis terkait (jika ada)
        $business = $user->business;

        // 7. Berikan respons sukses dengan token dan data user/bisnis
        return response()->json([
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'business' => $business ? [ // Hanya kirim jika bisnis ada
                    'id' => $business->id,
                    'nama_usaha' => $business->nama_usaha,
                    'logo_path' => $business->logo_path,
                ] : null,
            ]
        ], 200); // Kode status 200 OK
    }

    /**
     * Handle user logout request (Contoh).
     * Membutuhkan middleware auth:sanctum
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token API yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.'], 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        // Ambil email yang sudah divalidasi
        $credentials = $request->validated();

        // Kirim link reset menggunakan Password Broker bawaan Laravel
        $status = Password::sendResetLink($credentials);

        // Cek status pengiriman
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => trans($status)], 200); // Pesan sukses dari Laravel
        }

        // Jika gagal (misal email tidak ada, meskipun sudah divalidasi ulang)
        return response()->json(['message' => trans($status)], 400); // Pesan error dari Laravel
    }

    /**
     * Handle reset password request.
     * Mengatur ulang password pengguna menggunakan token dari email.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Ambil data yang sudah divalidasi (email, token, password, password_confirmation)
        $credentials = $request->validated();

        // Coba reset password menggunakan Password Broker
        $status = Password::reset($credentials, function (User $user, string $password) {
            // Callback ini dieksekusi jika email & token valid
            $user->forceFill([ // Gunakan forceFill untuk update password
                'password' => Hash::make($password),
                'remember_token' => Str::random(60), // Generate remember token baru
            ])->save();

            event(new PasswordReset($user)); // Trigger event password reset
        });

        // Cek status reset
        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password Anda telah berhasil direset.'], 200);
        }

        // Jika gagal (misal token tidak valid, email salah)
        return response()->json(['message' => trans($status)], 400);
    }

    public function redirectToGoogle()
    {
        // Redirect user ke halaman login Google
        return Socialite::driver('google')->stateless()->redirect();
        // ->stateless() penting untuk API
    }

    /**
     * Obtain the user information from Google.
     * Handle callback after user logs in via Google.
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user di database berdasarkan google_id atau email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User sudah ada, update google_id jika belum ada (opsional)
                // if (empty($user->google_id)) {
                //     $user->update(['google_id' => $googleUser->getId()]);
                // }

                // Cek verifikasi email (opsional, tergantung kebijakan Anda)
                // if (!$user->hasVerifiedEmail()) {
                //     $user->markEmailAsVerified(); // Atau kirim notifikasi lain
                // }

            } else {
                // User belum ada -> Buat User & Business baru
                DB::beginTransaction();
                try {
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        // 'google_id' => $googleUser->getId(), // Tambahkan kolom google_id jika perlu
                        'password' => Hash::make(Str::random(24)), // Buat password acak
                        'email_verified_at' => now(), // Anggap email Google sudah terverifikasi
                    ]);

                    $businessName = 'Bisnis ' . $googleUser->getName();
                    Business::create([
                        'user_id' => $user->id,
                        'nama_usaha' => $businessName,
                    ]);
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    // Log::error('Gagal membuat user dari Google: ' . $e->getMessage());
                    return response()->json(['message' => 'Gagal mendaftarkan pengguna baru.'], 500);
                }
            }

            // Buat token API Sanctum untuk user
            $deviceName = $user->email . ' (Google)';
            $token = $user->createToken($deviceName)->plainTextToken;

            // Ambil data bisnis terkait
            $business = $user->business;

            // Redirect ke Front End dengan membawa token (Cara 1: Query Parameter)
            // $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            // return redirect($frontendUrl . '/auth/callback?token=' . $token);

            // ATAU Kembalikan JSON dengan token (Cara 2: Lebih cocok untuk API)
            return response()->json([
                'message' => 'Login via Google berhasil.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [ /* ... data user & business seperti di method login ... */ ]
            ], 200);


        } catch (Exception $e) {
            // Tangani error (misal: user batal login, kredensial salah, dll.)
            // Log::error('Google Auth Error: '.$e->getMessage());
            // return redirect(env('FRONTEND_URL', 'http://localhost:3000').'/login?error=google_auth_failed');
            return response()->json(['message' => 'Autentikasi Google gagal.', 'error' => $e->getMessage()], 401);
        }
    }

    // --- Method lain (login, forgot password, dll.) akan ditambahkan di sini nanti ---
}
