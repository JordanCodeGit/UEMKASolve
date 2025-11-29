<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Uemkas</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">
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
                    <a href="{{ route('pengaturan.show') }}" class="{{ Request::is('pengaturan') ? 'active' : '' }}">
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
        @if (!Request::is('pengaturan'))
            <header class="top-bar">
                <h1 class="page-title">
                    @yield('title')
                </h1>
                
                <div class="top-bar-right">
                    <div class="notification-wrapper" style="position: relative;">
                        
                        <button class="notification-bell" id="notif-btn">
                            <i class="fa-regular fa-bell"></i>
                            <span class="notif-badge" id="notif-badge" style="display: none;"></span>
                        </button>

                        <div class="notif-dropdown" id="notif-menu">
                            <div class="notif-header">
                                <h3>Notifikasi</h3>
                                <span class="mark-read" onclick="clearNotifications()">Tandai semua sudah dibaca</span>
                            </div>
                            
                            <div class="notif-list" id="notif-list">
                                </div>
                        </div>

                    </div>
                    

                    <div class="user-profile-dropdown" id="profileTriggerBtn">
                        
                        @if(optional($globalUser->perusahaan)->logo)
                            <img src="{{ $globalUser->perusahaan->logo }}" alt="Logo" class="profile-avatar-pojok">
                        @else
                            <div class="default-avatar-pojok">
                                {{ substr($globalUser->name, 0, 1) }}
                            </div>
                        @endif

                        <span class="profile-name">
                            {{ Auth::user()->perusahaan ? Auth::user()->perusahaan->nama_perusahaan : Auth::user()->name }}
                        </span>
                        <i class="fa-solid fa-chevron-down" style="margin-left: 8px; font-size: 12px; color: #64748b; transition: transform 0.2s;"></i>

                        <div class="header-dropdown-menu" id="headerDropdownMenu">

                            <a href="#" class="header-menu-item text-red" id="headerLogoutBtn">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Keluar</span>
                            </a>
                        </div>

                    </div>
                </div>
            </header>
        @endif

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
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('profileTriggerBtn');
            const dropdownMenu = document.getElementById('headerDropdownMenu');
            const logoutBtn = document.getElementById('headerLogoutBtn');

            const notifBtn = document.getElementById('notif-btn');
            const notifMenu = document.getElementById('notif-menu');
            const notifList = document.getElementById('notif-list');
            const notifBadge = document.getElementById('notif-badge');
            
            const notifications = [];

            // ===== UTILITY: DETECT BROWSER & OS =====
            function detectBrowserAndOS() {
                const ua = navigator.userAgent;
                let browser = 'Browser';
                let os = 'OS';

                // Detect OS
                if (ua.indexOf('Windows') > -1) os = 'Windows';
                else if (ua.indexOf('Mac') > -1) os = 'macOS';
                else if (ua.indexOf('Linux') > -1) os = 'Linux';
                else if (ua.indexOf('Android') > -1) os = 'Android';
                else if (ua.indexOf('iPhone') > -1 || ua.indexOf('iPad') > -1) os = 'iOS';

                // Detect Browser
                if (ua.indexOf('Chrome') > -1 && ua.indexOf('Chromium') === -1 && ua.indexOf('Edge') === -1) browser = 'Google Chrome';
                else if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) browser = 'Safari';
                else if (ua.indexOf('Firefox') > -1) browser = 'Mozilla Firefox';
                else if (ua.indexOf('Edge') > -1 || ua.indexOf('Edg') > -1) browser = 'Microsoft Edge';
                else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) browser = 'Opera';
                else if (ua.indexOf('Trident') > -1) browser = 'Internet Explorer';

                return { browser, os };
            }

            // ===== 1. NOTIFIKASI LOGIN =====
            const now = new Date();
            const userName = '{{ Auth::user()->name }}' || 'User';
            const { browser, os } = detectBrowserAndOS();
            const loginTime = now.toLocaleString('id-ID', { 
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            notifications.push({
                type: 'login',
                title: 'Login Berhasil',
                desc: `${userName} login pada ${loginTime} menggunakan ${browser} di ${os}`,
                time: 'Baru saja',
                icon: '{{ asset("icons/notif_login.png") }}'
            });

            // ===== 2. NOTIFIKASI H-7 SEBELUM AKHIR BULAN =====
            const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            const daysUntilMonthEnd = lastDayOfMonth - now.getDate();
            
            if (daysUntilMonthEnd <= 7 && daysUntilMonthEnd >= 0) {
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                const currentMonth = monthNames[now.getMonth()];
                
                notifications.push({
                    type: 'print',
                    title: 'Waktunya Cetak Buku Kas!',
                    desc: `Periode ${currentMonth} akan berakhir dalam ${daysUntilMonthEnd} hari. Segera cetak laporan Anda.`,
                    time: 'Pengingat',
                    icon: '<i class="fa-solid fa-print"></i>'
                });
            }

            // ===== CHECK BADGE STATE FROM LOCALSTORAGE =====
            const notifViewed = sessionStorage.getItem('notif_viewed');
            if (notifViewed !== 'true') {
                notifBadge.style.display = 'block';
            } else {
                notifBadge.style.display = 'none';
            }

            // --- RENDER NOTIFIKASI ---
            function renderNotifications() {
                notifList.innerHTML = '';
                
                if (notifications.length === 0) {
                    notifList.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.9rem;">Tidak ada notifikasi baru</div>';
                    return;
                }

                notifications.forEach(notif => {
                    const item = document.createElement('div');
                    item.className = 'notif-item';
                    
                    // Tentukan warna icon berdasarkan tipe
                    const iconClass = notif.type === 'print' ? 'icon-blue-light' : 'icon-green-light';
                    
                    // Render icon - jika string (image), pakai img tag; jika HTML, pakai innerHTML
                    let iconHTML = '';
                    if (notif.type === 'login') {
                        iconHTML = `<img src="${notif.icon}" alt="Login Icon" style="width:24px; height:24px; object-fit:contain;">`;
                    } else {
                        iconHTML = notif.icon;
                    }
                    
                    item.innerHTML = `
                        <div class="notif-icon ${iconClass}">
                            ${iconHTML}
                        </div>
                        <div class="notif-content">
                            <p class="notif-title">${notif.title}</p>
                            <p class="notif-desc">${notif.desc}</p>
                            <span class="notif-time">${notif.time}</span>
                        </div>
                    `;
                    notifList.appendChild(item);
                });
            }

            // --- EVENT LISTENERS ---
            
            // Toggle Menu & Mark as Viewed
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifMenu.style.display = notifMenu.style.display === 'block' ? 'none' : 'block';
                
                // Mark as viewed - hide badge
                if (notifMenu.style.display === 'block') {
                    sessionStorage.setItem('notif_viewed', 'true');
                    notifBadge.style.display = 'none';
                }
            });

            // Close Outside Click
            document.addEventListener('click', (e) => {
                if (!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
                    notifMenu.style.display = 'none';
                }
            });

            // Clear Notif (Hanya Visual)
            window.clearNotifications = function() {
                notifications.length = 0; // Kosongkan array
                renderNotifications();
                sessionStorage.setItem('notif_viewed', 'true');
                notifBadge.style.display = 'none';
            };

            // Render notifikasi saat pertama kali load
            renderNotifications();

            // 1. Toggle Menu saat klik Profil
            if (profileBtn) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Cegah event bubbling
                    this.classList.toggle('active');
                });
            }

            // 2. Tutup menu jika klik di luar
            document.addEventListener('click', function(e) {
                if (profileBtn && !profileBtn.contains(e.target)) {
                    profileBtn.classList.remove('active');
                }
            });

            // 3. LOGIKA LOGOUT (API Request)
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // UI Loading
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Keluar...</span>';
                    this.style.pointerEvents = 'none'; // Cegah klik ganda

                    const token = localStorage.getItem('auth_token');

                    try {
                        // Panggil Route Logout
                        // Pastikan route '/logout' di web.php mengarah ke AuthController@logout
                        await fetch('{{ url("/logout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}', // Wajib untuk Web Route
                                'Authorization': 'Bearer ' + token      // Wajib untuk hapus Token API
                            }
                        });
                    } catch (error) {
                        console.error("Logout error:", error);
                        // Tetap lanjutkan logout di browser meskipun API error
                    } finally {
                        // BERSIH-BERSIH TOKEN LOKAL
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_data');
                        
                        // Redirect ke Login
                        window.location.href = '{{ route("login") }}';
                    }
                });
            }
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