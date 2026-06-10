<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MailDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        assert($user !== null);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        if (!MailDelivery::isInboxMailerConfigured() && !MailDelivery::allowsDevelopmentFallback()) {
            return response()->json([
                'status' => 'mail-not-configured',
                'message' => MailDelivery::configurationErrorMessage(),
            ], 503);
        }

        if (!MailDelivery::isInboxMailerConfigured()) {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            Log::info('Verification notification skipped because SMTP is not configured for development.', [
                'email' => $user->email,
                'verification_url' => $verificationUrl,
            ]);

            return response()->json([
                'status' => 'verification-link-created',
                'message' => 'Link verifikasi dibuat untuk pengujian lokal.',
                'verification_url' => $verificationUrl,
            ], 202);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $mailError) {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            Log::error('Verification notification failed', [
                'email' => $user->email,
                'message' => $mailError->getMessage(),
                'verification_url' => $verificationUrl,
            ]);

            if (!MailDelivery::allowsDevelopmentFallback()) {
                return response()->json([
                    'status' => 'verification-email-failed',
                    'message' => 'Email verifikasi belum dapat dikirim. Periksa konfigurasi SMTP hosting.',
                ], 502);
            }

            return response()->json([
                'status' => 'verification-link-created',
                'message' => 'Link verifikasi dibuat untuk pengujian lokal.',
                'verification_url' => $verificationUrl,
            ], 202);
        }

        return response()->json(['status' => 'verification-link-sent']);
    }
}
