<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Uemkas</title>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @vite('resources/css/app.css')
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">

            <img src="{{ asset('images/logo_sidebar.png') }}" alt="Logo Uemkas" class="sidebar-logo-img">

            <span>Uemkas</span>
        </a>
        
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ Request::is('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house-chimney"></i>
                        <span>DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('buku-kas') }}" class="{{ Request::is('buku-kas') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>BUKU KAS</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('kategori') }}" class="{{ Request::is('kategori') ? 'active' : '' }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>KATEGORI</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('pengaturan') }}" class="{{ Request::is('pengaturan*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i>
                        <span>PENGATURAN</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <button class="upgrade-btn">
                Upgrade Plan
            </button>
            <small>@2025 UemkaSolve</small>
        </div>
    </aside>
    <div class="main-content-wrapper">
        
        <header class="top-bar">
            <h1 class="page-title">
                @yield('title')
            </h1>
            
            <div class="top-bar-right">
                @if (Request::routeIs('dashboard')) 
                    <div class="search-bar-top">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" placeholder="Cari di Sini..." id="header-search-input">
                    </div>
                @endif
                
                <button class="notification-bell">
                    <i class="fa-regular fa-bell"></i>
                </button>
                
                <button class="theme-toggle-btn" id="theme-toggle">
                    <i class="fa-regular fa-moon"></i>
                </button>

                <div class="user-profile-dropdown">
    
                    <div class="profile-avatar-header" id="global-header-avatar">
                        </div>
                    
                    <span class="profile-name" id="global-header-business-name">Memuat...</span>
                </div>
            </div>
        </header>
        <main class="content-area">
            @yield('content')
        </main>
        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');

            // 1. Cek tema tersimpan di localStorage
            const currentTheme = localStorage.getItem('theme');
            if (currentTheme === 'dark') {
                body.classList.add('dark-mode');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }

            // 2. Event listener untuk tombol
            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                
                if (body.classList.contains('dark-mode')) {
                    // Jika beralih ke Dark Mode
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                    localStorage.setItem('theme', 'dark'); // Simpan pilihan
                } else {
                    // Jika beralih ke Light Mode
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                    localStorage.setItem('theme', 'light'); // Simpan pilihan
                }
            });
        });
    </script>
    <script>
        (function() {
            const token = localStorage.getItem('auth_token');
            // [BARU] Ambil elemen baru
            const businessNameEl = document.getElementById('global-header-business-name');
            const avatarEl = document.getElementById('global-header-avatar');

            if (token && businessNameEl && avatarEl) {
                // [PERBAIKAN] Panggil /api/profile untuk data lengkap
                fetch('{{ url("/api/profile") }}', { 
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                })
                .then(response => {
                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        window.location.href = '{{ url("/login") }}';
                    }
                    return response.json();
                })
                .then(data => {
                    // [PERBAIKAN] Isi dengan data bisnis
                    if (data.business && data.business.nama_usaha) {
                        businessNameEl.textContent = data.business.nama_usaha;
                    } else if (data.user && data.user.name) {
                        businessNameEl.textContent = data.user.name; // Fallback ke nama user
                    }

                    if (data.business && data.business.logo_url) {
                        // Jika ada logo, tampilkan gambar
                        avatarEl.innerHTML = `<img src="${data.business.logo_url}" alt="Logo">`;
                    } else if (data.user && data.user.name) {
                        // Jika tidak, tampilkan inisial
                        avatarEl.textContent = data.user.name.charAt(0).toUpperCase();
                    }
                })
                .catch(err => {
                    console.error('Gagal mengambil data header profil:', err);
                    if(businessNameEl) businessNameEl.textContent = 'Error';
                });
            }
        })();
    </script>
            @stack('scripts')
    </body>
</html>