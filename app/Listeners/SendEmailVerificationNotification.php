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
            Log::info('SendEmailVerificationNotification: User registered - ' . $event->user->email);
            
            // Jika user belum verifikasi email, kirim email
            if (!$event->user->hasVerifiedEmail()) {
                Mail::send(new VerifyEmailMail($event->user));
                Log::info('Verification email sent to: ' . $event->user->email);
            }
        } catch (\Exception $e) {
            Log::error('Error sending verification email: ' . $e->getMessage());
        }
    }
}
