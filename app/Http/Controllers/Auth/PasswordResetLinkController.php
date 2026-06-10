<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MailDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (!MailDelivery::isInboxMailerConfigured() && !MailDelivery::allowsDevelopmentFallback()) {
            $message = MailDelivery::configurationErrorMessage();

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 503);
            }

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        if (!MailDelivery::isInboxMailerConfigured()) {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => [__(Password::INVALID_USER)],
                ]);
            }

            $token = Password::broker()->createToken($user);
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            Log::info('Password reset email skipped because SMTP is not configured for development.', [
                'email' => $user->email,
                'reset_url' => $resetUrl,
            ]);

            $message = 'Link reset password berhasil dibuat untuk pengujian lokal.';

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => $message,
                    'reset_url' => $resetUrl,
                ], 202);
            }

            return back()
                ->with('status', $message)
                ->with('reset_link', $resetUrl);
        }

        try {
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $mailError) {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => [__(Password::INVALID_USER)],
                ]);
            }

            $token = Password::broker()->createToken($user);
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            Log::error('Password reset email failed', [
                'email' => $user->email,
                'message' => $mailError->getMessage(),
                'reset_url' => $resetUrl,
            ]);

            if (!MailDelivery::allowsDevelopmentFallback()) {
                $message = 'Email reset password belum dapat dikirim. Periksa konfigurasi SMTP hosting.';

                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 502);
                }

                throw ValidationException::withMessages([
                    'email' => [$message],
                ]);
            }

            $message = 'Link reset password berhasil dibuat untuk pengujian lokal.';

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => $message,
                    'reset_url' => $resetUrl,
                ], 202);
            }

            return back()
                ->with('status', $message)
                ->with('reset_link', $resetUrl);
        }

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => __($status)]);
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
