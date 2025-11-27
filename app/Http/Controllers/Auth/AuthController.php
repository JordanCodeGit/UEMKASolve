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
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'id_perusahaan' => null, // Biarkan kosong
            ]);

            // CATATAN: Kita TIDAK membuat perusahaan di sini.
            // Biarkan user membuatnya nanti lewat Pop-up di Dashboard.

            event(new Registered($user));

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil. Silakan login.',
                'user' => $user
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi.',
                // 'error' => $e->getMessage() // Uncomment untuk debug
            ], 500);
        }
    }

    /**
     * Handle Login Request
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // 1. Cek Apakah User Ada?
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // Email belum terdaftar
            return response()->json([
                'message' => 'Email belum terdaftar.'
            ], 401);
        }

        // 2. Cek Password
        if (!Hash::check($credentials['password'], $user->password)) {
            // Password salah
            return response()->json([
                'message' => 'Password salah.'
            ], 401);
        }

        // --- JIKA LULUS ---
        
        // Login Session Web
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Buat Token API
        $deviceName = $request->input('device_name', $user->email);
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
    public function logout(Request $request): JsonResponse
    {
        // Hapus token API
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        // Hapus Session Web
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.'], 200);
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

        return back()->withErrors(['email' => __($status)]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
}