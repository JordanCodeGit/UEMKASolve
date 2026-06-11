<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memproses Login...</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const token = @json($token);
            const next = @json($next ?? '/dashboard');

            if (token) {
                localStorage.setItem('auth_token', token);
                window.location.replace(next || '/dashboard');
                return;
            }

            window.location.replace('/login');
        });
    </script>
</head>
<body>
    <p style="text-align:center; margin-top: 50px;">Sedang masuk ke Dashboard...</p>
</body>
</html>
