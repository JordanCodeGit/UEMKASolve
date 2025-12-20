@component('mail::message')
{{-- // Bagian Konten Email Verifikasi (Markdown) --}}
# Verifikasi Email Anda

Halo {{ $user->name }},

Terima kasih telah mendaftar di **UEMKASolve**!

Silakan klik tombol di bawah untuk memverifikasi email Anda:

@component('mail::button', ['url' => $verificationUrl])
Verifikasi Email
@endcomponent

Link ini akan berlaku selama **24 jam**.

Jika Anda tidak melakukan pendaftaran, abaikan email ini.

Salam,
Tim UEMKASolve
@endcomponent
