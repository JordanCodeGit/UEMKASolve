<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class VerifyEmailNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    // Kode fungsi menentukan channel pengiriman
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // Kode fungsi mendefinisikan representasi email
    public function toMail(object $notifiable): MailMessage
    {
        /** @var \App\Models\User $notifiable */
        // Generate signed URL untuk verifikasi email
        // Gunakan full URL dengan APP_URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Log untuk debugging
        Log::info('Sending email verification to: ' . $notifiable->email);
        Log::info('Verification URL: ' . $verificationUrl);

        return (new MailMessage)
            ->subject('Verifikasi Email Anda')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Terima kasih telah mendaftar di UEMKASolve!')
            ->line('Silakan klik tombol di bawah untuk memverifikasi email Anda.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Link ini akan berlaku selama 24 jam.')
            ->line('Jika Anda tidak melakukan pendaftaran, abaikan email ini.')
            ->salutation('Salam, Tim UEMKASolve');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
