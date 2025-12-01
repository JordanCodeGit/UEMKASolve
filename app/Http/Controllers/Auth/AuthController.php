<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
// use App\Models\Business; // Tidak dipakai lagi karena diganti Perusahaan
use App\Models\Perusahaan; // Pastikan model ini ada
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Handle user registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            // 1. Buat User Baru
            // Kita set id_perusahaan NULL agar nanti Pop-up di dashboard muncul
            /** @var string $name */
            $name = $validatedData['name'];
            /** @var string $email */
            $email = $validatedData['email'];
            /** @var string $password */
            $password = $validatedData['password'];
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'id_perusahaan' => null, // Biarkan kosong
            ]);

            Log::info('User created for registration: ' . $user->email);

            // CATATAN: Kita TIDAK membuat perusahaan di sini.
            // Biarkan user membuatnya nanti lewat Pop-up di Dashboard.

            // Trigger Registered event untuk mengirim email verifikasi
            Log::info('Triggering Registered event for: ' . $user->email);
            event(new Registered($user));

            DB::commit();

            Log::info('Registration successful for: ' . $user->email);

            return response()->json([
                'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi.',
                // 'error' => $e->getMessage() // Uncomment untuk debug
            ], 500);
        }
    }

    /**
     * Handle Login Request
     *
     * Remember Me Feature:
     * - Jika checkbox 'remember' di-check: session berlaku 12 jam (720 menit)
     * - Jika checkbox 'remember' tidak di-check: session berlaku selama browser terbuka
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $rememberMe = $request->boolean('remember'); // Ambil nilai remember checkbox

        // 1. Cek Apakah User Ada?
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // Email belum terdaftar
            return response()->json([
                'message' => 'Email belum terdaftar.'
            ], 401);
        }

        // 2. Cek Password
        /** @var string $credPassword */
        $credPassword = $credentials['password'];
        $userPassword = $user->password ?? '';
        if (!Hash::check($credPassword, $userPassword)) {
            // Password salah
            return response()->json([
                'message' => 'Password salah.'
            ], 401);
        }

        // 3. Cek Email Verifikasi
        if (!$user->email_verified_at) {
            // Email belum diverifikasi
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi.'
            ], 403);
        }

        // --- JIKA LULUS ---

        // Login Session Web dengan Remember Me
        Log::info('User login - Remember Me: ' . ($rememberMe ? 'YES (12 hours)' : 'NO (browser session)'));
        Auth::login($user, $rememberMe); // True = remember me selama 12 jam, False = session browser only
        $request->session()->regenerate();

        // Buat Token API
        $deviceNameInput = $request->input('device_name');
        $deviceName = is_string($deviceNameInput) ? $deviceNameInput : $user->email;
        $token = $user->createToken($deviceName)->plainTextToken;
        $perusahaan = $user->perusahaan;

        return response()->json([
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'id_perusahaan' => $user->id_perusahaan,
                'business' => $perusahaan ? [
                    'id' => $perusahaan->id,
                    'nama_usaha' => $perusahaan->nama_perusahaan,
                    'logo' => $perusahaan->logo,
                ] : null,
            ]
        ], 200);
    }

    /**
     * Handle Google Callback
     */
    public function handleGoogleCallback()
    {
        try {
            /** @phpstan-ignore-next-line */
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // --- USER BARU ---
                $user = User::create([
                    'name'              => $googleUser->getName(),
                    'email'             => $googleUser->getEmail(),
                    'google_id'         => $googleUser->getId(),
                    'password'          => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'id_perusahaan'     => null
                ]);
            } else {
                // --- USER LAMA ---
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            // Buat Token API
            $token = $user->createToken('google-login')->plainTextToken;

            // [PERBAIKAN UTAMA DI SINI] ---------------------------

            // 1. Login dengan "Remember Me" (true) agar session lebih kuat
            Auth::login($user, true);

            // 2. Regenerate Session ID (Wajib agar tidak dianggap session palsu)
            request()->session()->regenerate();

            // -----------------------------------------------------

            // Redirect ke Frontend
            return redirect('/auth/google-success?token=' . $token);
        } catch (\Exception $e) {
            return redirect('/login?error=google_failed');
        }
    }

    /**
     * Handle user logout request.
     */
    public function logout(Request $request)
    {
        // Hapus token API (jika ada)
        $user = $request->user();
        if ($user) {
            /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        // Hapus Session Web
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Jika request adalah AJAX/JSON, return JSON response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logout berhasil.'], 200);
        }

        // Jika request normal, redirect ke login
        return redirect()->route('login')->with('success', 'Logout berhasil.');
    }

    // --- Forgot & Reset Password (Tidak Berubah) ---
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $credentials = $request->validated();
        $status = Password::sendResetLink($credentials);

        if ($status == Password::RESET_LINK_SENT) {
            // [PERBAIKAN] Jangan return JSON, tapi kembalikan ke halaman view dengan pesan sukses
            return back()->with('status', __($status));
        }

        // [PERBAIKAN] Kembalikan dengan error
        return back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $credentials = $request->validated();
        $status = Password::reset($credentials, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            event(new PasswordReset($user));
        });

        if ($status == Password::PASSWORD_RESET) {
            // [PERBAIKAN] Redirect ke halaman LOGIN dengan pesan sukses
            return redirect()->route('login')->with('status', 'Password berhasil diubah! Silakan login.');
        }

        return back()->withErrors(['email' => __(is_string($status) ? $status : '')]);
    }

    public function redirectToGoogle()
    {
        /** @phpstan-ignore-next-line */
        return Socialite::driver('google')->stateless()->redirect();
    }
}
