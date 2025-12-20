<!doctype html>
<html>

<head>
    {{-- // Bagian Head & Meta --}}
    <meta charset="utf-8">
    <title>Verifikasi Email</title>
</head>

<body>
    {{-- // Bagian Konten Email Verifikasi (HTML) --}}
    <h1>Verifikasi Email Anda</h1>
    <p>Halo {{ $user->name }},</p>
    <p>Terima kasih telah mendaftar di <strong>UEMKASolve</strong>!</p>
    <p>Silakan kunjungi link berikut untuk memverifikasi email Anda:</p>
    <p><a href="{{ $verificationUrl }}">Verifikasi Email</a></p>
    <p>Link ini akan berlaku selama 24 jam.</p>
    <p>Jika Anda tidak melakukan pendaftaran, abaikan email ini.</p>
    <p>Salam,<br>Tim UEMKASolve</p>
</body>

</html>
