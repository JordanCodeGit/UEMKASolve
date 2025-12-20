<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Memproses Login...</title>
    <script>
        // Kode penanganan token Google Login
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Ambil token dari URL (dikirim oleh AuthController)
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            if (token) {
                // 2. Simpan Token ke Browser
                localStorage.setItem('auth_token', token);
                
                // 3. Langsung pindah ke Dashboard
                window.location.href = '/dashboard';
            } else {
                // Jika gagal, balik ke login
                window.location.href = '/login';
            }
        });
    </script>
</head>
<body>
    <p style="text-align:center; margin-top: 50px;">Sedang masuk ke Dashboard...</p>
</body>
</html>