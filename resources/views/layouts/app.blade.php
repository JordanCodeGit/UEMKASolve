<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Uemkas</title>
    
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
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('buku-kas') }}" class="{{ Request::is('buku-kas') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Buku Kas</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('kategori') }}" class="{{ Request::is('kategori') ? 'active' : '' }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('pengaturan') }}" class="{{ Request::is('pengaturan*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i>
                        <span>Setting</span>
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
                        <input type="text" placeholder="Cari di Sini...">
                    </div>
                @endif
                
                <button class="notification-bell">
                    <i class="fa-regular fa-bell"></i>
                </button>
                
                <button class="theme-toggle-btn" id="theme-toggle">
                    <i class="fa-regular fa-moon"></i>
                </button>

                <div class="user-profile">
                    <span>Yantokopi</span>
                    <i class="fa-solid fa-chevron-down"></i>
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


    </body>
</html>