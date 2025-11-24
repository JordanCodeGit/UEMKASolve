<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Uemkas</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="logo-container">
                <img src="{{ url('img/logo.png') }}" alt="Logo" class="logo">
            </div>
        </div>

        <div class="auth-right">
            @yield('content')
        </div>
    </div>
    @stack('scripts')
</body>
</html>