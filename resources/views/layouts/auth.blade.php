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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite('resources/css/app.css')
    <style>
        /* Minimal toast styles */
        #toast-container {
            position: fixed;
            right: 16px;
            bottom: 20px;
            z-index: 1100;
        }

        .toast-message {
            background: rgba(0, 0, 0, 0.85);
            color: #fff;
            padding: 10px 14px;
            margin-top: 8px;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            font-size: 0.95rem;
            max-width: 320px;
        }

        .toast-success {
            background: #16a34a;
        }

        .toast-error {
            background: #dc2626;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="logo-container">
                <img src="{{ asset('img/logo.png') }}" alt="logo" class="logo">
            </div>
        </div>

        <div class="auth-right">
            @yield('content')
        </div>
    </div>
    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    <script>
        function showToast(type, message, timeout = 3500) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const div = document.createElement('div');
            div.className = 'toast-message ' + (type === 'success' ? 'toast-success' : 'toast-error');
            div.textContent = message;
            container.appendChild(div);
            setTimeout(() => {
                div.style.transition = 'opacity 250ms ease, transform 250ms ease';
                div.style.opacity = '0';
                div.style.transform = 'translateY(6px)';
                setTimeout(() => container.removeChild(div), 300);
            }, timeout);
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Show server-side session messages as minimal toast
            @if (session('status'))
                showToast('success', {!! json_encode(session('status')) !!});
            @endif

            @if (isset($errors) && $errors->any())
                // show first error
                showToast('error', {!! json_encode($errors->first()) !!});
            @endif
        });
    </script>
    @stack('scripts')
</body>

</html>
