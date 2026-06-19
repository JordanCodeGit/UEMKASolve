<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    private const REGISTRATION_REQUIRED_MESSAGE = 'harap melakukan registrasi';

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
        ]);

        $verificationSent = true;

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            $verificationSent = false;

            Log::error('Mobile register verification email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => $verificationSent
                ? 'Registrasi berhasil. Silakan cek email untuk verifikasi.'
                : 'Registrasi berhasil, tetapi email verifikasi gagal dikirim. Silakan gunakan kirim ulang verifikasi.',
            'verification_sent' => $verificationSent,
            'user' => $this->formatUser($user->fresh()),
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email belum terdaftar.',
            ], 401);
        }

        if (!$user->password || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
            ], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek email Anda.',
            ], 401);
        }

        if ($user->isStaffWithoutActiveBusiness()) {
            $this->revokeStaffAccess($user);

            return response()->json([
                'message' => self::REGISTRATION_REQUIRED_MESSAGE,
            ], 403);
        }

        $hasPendingInvitation = BusinessMember::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if (!$user->role && !$hasPendingInvitation) {
            $user->role = 'owner';
            $user->save();
        }

        $deviceName = $validated['device_name'] ?? 'flutter-mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    public function googleLogin(Request $request)
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $payload = $this->verifyGoogleIdToken($validated['id_token']);

        if (!$payload) {
            return response()->json([
                'message' => 'Token Google tidak valid.',
            ], 401);
        }

        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'Pengguna Google';
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$email || !$emailVerified) {
            return response()->json([
                'message' => 'Email Google belum valid atau belum terverifikasi.',
            ], 422);
        }

        $email = strtolower(trim($email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
                'google_id' => $payload['sub'] ?? null,
                'role' => 'owner',
                'profile_photo_path' => $payload['picture'] ?? null,
            ]);
        } else {
            if ($user->isStaffWithoutActiveBusiness()) {
                $this->revokeStaffAccess($user);

                return response()->json([
                    'message' => self::REGISTRATION_REQUIRED_MESSAGE,
                ], 403);
            }

            $hasPendingInvitation = BusinessMember::where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            $user->forceFill([
                'email_verified_at' => $user->email_verified_at ?: now(),
                'google_id' => $user->google_id ?: ($payload['sub'] ?? null),
                'role' => (!$user->role && !$hasPendingInvitation) ? 'owner' : $user->role,
                'profile_photo_path' => $user->profile_photo_path ?: ($payload['picture'] ?? null),
            ])->save();
        }

        $token = $user->createToken($validated['device_name'] ?? 'flutter-android')->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower(trim($validated['email'])),
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Link reset password berhasil dikirim. Silakan cek inbox atau folder spam.',
                'status' => __($status),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower(trim($validated['email'])),
                'token' => $validated['token'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password berhasil diubah. Silakan login dengan password baru.',
                'status' => __($status),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function resendVerification(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email belum terdaftar.',
            ], 404);
        }

        if (!is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Email sudah diverifikasi.',
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Link verifikasi baru telah dikirim.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Mobile resend verification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mengirim email verifikasi.',
            ], 500);
        }
    }

    private function verifyGoogleIdToken(string $idToken): ?array
    {
        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if ($response->failed()) {
            Log::warning('Mobile Google token verification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $payload = $response->json();
        $clientId = config('services.google.client_id');

        if ($clientId && ($payload['aud'] ?? null) !== $clientId) {
            Log::warning('Mobile Google token audience mismatch', [
                'expected' => $clientId,
                'actual' => $payload['aud'] ?? null,
            ]);

            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    private function formatUser(User $user): array
    {
        $business = null;

        try {
            $user->loadMissing('business');

            if ($user->business) {
                $business = $this->formatBusinessSummary($user->business);
            }

            if (!$business) {
                $membership = BusinessMember::where('user_id', $user->id)
                    ->where('status', 'accepted')
                    ->with('business')
                    ->latest('accepted_at')
                    ->first();

                if ($membership && $membership->business) {
                    $business = $this->formatBusinessSummary($membership->business);
                }
            }
        } catch (\Throwable) {
            $business = null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'is_google_account' => !empty($user->google_id),
            'has_pending_invitation' => BusinessMember::where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists(),
            'business' => $business,
        ];
    }

    private function formatBusinessSummary($business): array
    {
        $logoPath = $business->logo_path ?? null;
        $logoUrl = $logoPath
            ? request()->getSchemeAndHttpHost() . '/storage/' . ltrim($logoPath, '/')
            : null;

        return [
            'id' => $business->id,
            'nama_usaha' => $business->nama_usaha ?? null,
            'logo' => $logoPath,
            'logo_path' => $logoPath,
            'logo_url' => $logoUrl,
        ];
    }

    private function revokeStaffAccess(User $user): void
    {
        $user->tokens()->delete();
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();
    }
}
