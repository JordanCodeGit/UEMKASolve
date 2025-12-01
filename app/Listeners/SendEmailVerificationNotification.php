<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\VerifyEmailMail;

class SendEmailVerificationNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        try {
            // Log untuk debugging
            $emailAttr = $event->user->getAttribute('email');
            $email = is_string($emailAttr) ? $emailAttr : '';
            Log::info('SendEmailVerificationNotification: User registered - ' . $email);

            // Jika user belum verifikasi email, kirim email
            if (!$event->user->hasVerifiedEmail()) {
                /** @var \App\Models\User $user */
                $user = $event->user;
                // Pastikan kita mengirim ke alamat user — gunakan Mail::to() agar header To ter-set
                Mail::to($user->getEmailForVerification() ?? $email)->send(new VerifyEmailMail($user));
                Log::info('Verification email sent to: ' . $email);
            }
        } catch (\Exception $e) {
            Log::error('Error sending verification email: ' . $e->getMessage());
        }
    }
}
