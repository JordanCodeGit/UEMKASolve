<?php

namespace App\Support;

class MailDelivery
{
    public static function isInboxMailerConfigured(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if ($mailer !== 'smtp') {
            return true;
        }

        return self::filledConfig('mail.mailers.smtp.host')
            && self::filledConfig('mail.mailers.smtp.port')
            && self::filledConfig('mail.mailers.smtp.username')
            && self::filledConfig('mail.mailers.smtp.password')
            && self::filledConfig('mail.from.address');
    }

    public static function allowsDevelopmentFallback(): bool
    {
        return app()->environment('local') && (bool) config('app.debug');
    }

    public static function configurationErrorMessage(): string
    {
        return 'Konfigurasi email SMTP belum siap. Isi MAIL_MAILER=smtp, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, dan MAIL_FROM_ADDRESS di .env hosting.';
    }

    private static function filledConfig(string $key): bool
    {
        $value = config($key);

        return !blank($value) && $value !== 'null';
    }
}
