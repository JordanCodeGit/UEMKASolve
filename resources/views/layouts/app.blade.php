<!DOCTYPE html>
<html lang="id">

<head>
    {{-- // Bagian Head & Meta --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

<body class="{{ Request::is('buku-kas') ? 'page-buku-kas' : '' }} {{ Request::is('kategori') ? 'page-kategori' : '' }} {{ Request::is('pengaturan') ? 'page-pengaturan' : '' }}">

    {{-- // Bagian Sidebar Navigasi --}}
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
            {{-- // Bagian Header & Top Bar --}}
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
                        {{-- LOGO / AVATAR --}}
                        <span id="global-header-avatar" style="display: flex; align-items: center;">
                             @if (optional($globalUser->business)->logo_path)
                                <img src="{{ asset('storage/' . $globalUser->business->logo_path) }}" alt="Logo" class="profile-avatar-pojok" style="object-fit: cover;">
                            @else
                                <div class="default-avatar-pojok">
                                    @php
                                        $displayName = optional($globalUser->business)->nama_usaha ?? $globalUser->name;
                                    @endphp
                                    {{ substr($displayName, 0, 1) }}
                                </div>
                            @endif
                        </span>

                        {{-- NAMA --}}
                        <span class="profile-name" id="global-header-business-name">
                            {{ optional($globalUser->business)->nama_usaha ?? $globalUser->name }}
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

        {{-- // Bagian Konten Utama --}}
        <main class="content-area">
            @yield('content')
        </main>

    </div>

    {{-- // Bagian Navigasi Bawah (Mobile) --}}
    <nav class="mobile-bottom-nav" aria-label="Navigasi bawah">
        <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house-chimney"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('buku-kas') }}" class="mobile-bottom-nav__item {{ Request::is('buku-kas') ? 'active' : '' }}">
            <i class="fa-solid fa-book-open"></i>
            <span>Buku Kas</span>
        </a>
        <a href="{{ route('kategori') }}" class="mobile-bottom-nav__item {{ Request::is('kategori') ? 'active' : '' }}">
            <i class="fa-solid fa-tags"></i>
            <span>Kategori</span>
        </a>
        <a href="{{ route('pengaturan.show') }}" class="mobile-bottom-nav__item {{ Request::is('pengaturan') ? 'active' : '' }}">
            <i class="fa-solid fa-gear"></i>
            <span>Pengaturan</span>
        </a>
    </nav>

    {{-- SCRIPT PENGATURAN TEMA --}}
    <script>
        // Kode fungsi pengaturan tema
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            if (!themeToggle) return;

            const body = document.body;
            const icon = themeToggle.querySelector('i');

            const currentTheme = localStorage.getItem('theme');
            if (currentTheme === 'dark') {
                body.classList.add('dark-mode');
                if (icon) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            }

            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                if (body.classList.contains('dark-mode')) {
                    if (icon) {
                        icon.classList.remove('fa-moon');
                        icon.classList.add('fa-sun');
                    }
                    localStorage.setItem('theme', 'dark');
                } else {
                    if (icon) {
                        icon.classList.remove('fa-sun');
                        icon.classList.add('fa-moon');
                    }
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>

    {{-- SCRIPT UTAMA (NOTIFIKASI & LOGIC) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- SETUP ELEMENTS ---
            const profileBtn = document.getElementById('profileTriggerBtn');
            const dropdownMenu = document.getElementById('headerDropdownMenu');
            const logoutBtn = document.getElementById('headerLogoutBtn');

            const notifBtn = document.getElementById('notif-btn');
            const notifMenu = document.getElementById('notif-menu');
            const notifList = document.getElementById('notif-list');
            const notifBadge = document.getElementById('notif-badge');

            const notifications = [];

            // Kode fungsi deteksi browser & OS
            // ===== UTILITY: DETECT BROWSER & OS =====
            function detectBrowserAndOS() {
                const ua = navigator.userAgent;
                let browser = 'Browser';
                let os = 'OS';

                if (ua.indexOf('Windows') > -1) os = 'Windows';
                else if (ua.indexOf('Mac') > -1) os = 'macOS';
                else if (ua.indexOf('Linux') > -1) os = 'Linux';
                else if (ua.indexOf('Android') > -1) os = 'Android';
                else if (ua.indexOf('iPhone') > -1 || ua.indexOf('iPad') > -1) os = 'iOS';

                if (ua.indexOf('Edg') > -1 || ua.indexOf('Edge') > -1) browser = 'Microsoft Edge';
                else if (ua.indexOf('Firefox') > -1) browser = 'Mozilla Firefox';
                else if (ua.indexOf('Chrome') > -1 && ua.indexOf('Chromium') === -1) browser = 'Google Chrome';
                else if (ua.indexOf('Safari') > -1) browser = 'Safari';
                else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) browser = 'Opera';
                else if (ua.indexOf('Trident') > -1) browser = 'Internet Explorer';

                return { browser, os };
            }

            // Kode fungsi simpan & load riwayat login
            // ===== FUNGSI: SIMPAN & LOAD RIWAYAT LOGIN =====
            function saveLoginHistory(loginData) {
                let history = JSON.parse(localStorage.getItem('login_history')) || [];
                history.unshift(loginData);
                history = history.slice(0, 10);
                localStorage.setItem('login_history', JSON.stringify(history));
            }

            function getLoginHistory() {
                return JSON.parse(localStorage.getItem('login_history')) || [];
            }

            // ===== 1. LOGIKA PENCATATAN LOGIN (FIXED: ANTI SPAM) =====
            const now = new Date();
            const userName = '{{ Auth::user()->name }}' || 'User';
            const { browser, os } = detectBrowserAndOS();
            const loginTime = now.toLocaleString('id-ID', {
                weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            // [FIX] Cek flag 'login_recorded'. Jika belum ada, baru simpan.
            // Flag ini dihapus saat di halaman Login.
            if (!sessionStorage.getItem('login_recorded')) {
                const currentLogin = {
                    user: userName,
                    browser: browser,
                    os: os,
                    time: now.toISOString(),
                    timeDisplay: loginTime
                };
                saveLoginHistory(currentLogin);

                // Set flag agar tidak dicatat lagi saat refresh/pindah halaman
                sessionStorage.setItem('login_recorded', 'true');
            }

            // Generate HTML (Ambil dari LocalStorage yang isinya sudah aman/tidak duplikat)
            const loginHistory = getLoginHistory();
            let historyHTML = `
                <div style="max-height: 250px; overflow-y: auto; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                    <p style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; padding: 0 8px;">📋 Riwayat Login Terbaru:</p>
                    <div style="max-height: 200px; overflow-y: auto;">
            `;

            loginHistory.forEach((login, idx) => {
                const loginDate = new Date(login.time).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: '2-digit',
                    hour: '2-digit', minute: '2-digit'
                });
                const isCurrentLogin = idx === 0 ?
                    '<span style="background: #22c55e; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem; font-weight: bold;">Baru</span>' : '';

                historyHTML += `
                    <div style="padding: 8px; background: ${idx % 2 === 0 ? '#f8fafc' : 'white'}; border-left: 3px solid ${idx === 0 ? '#22c55e' : '#cbd5e1'}; margin-bottom: 6px; font-size: 0.8rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #1e293b;">${login.user}</span>
                            ${isCurrentLogin}
                        </div>
                        <div style="color: #64748b; font-size: 0.75rem;">
                            <div>🌐 ${login.browser} • ${login.os}</div>
                            <div style="margin-top: 2px;">📅 ${loginDate}</div>
                        </div>
                    </div>
                `;
            });

            historyHTML += `</div></div>`;

            // ===== CHECK MONTH-END STATUS =====
            const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            const daysUntilMonthEnd = lastDayOfMonth - now.getDate();

            // Notifikasi Akhir Bulan
            if (daysUntilMonthEnd <= 3 && daysUntilMonthEnd >= 0) {
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                const currentMonth = monthNames[now.getMonth()];
                const daysLabel = daysUntilMonthEnd === 0 ? 'hari ini' : `dalam ${daysUntilMonthEnd} hari`;

                notifications.push({
                    type: 'print',
                    title: 'Waktunya Cetak Buku Kas!',
                    desc: `Periode ${currentMonth} akan berakhir ${daysLabel}.`,
                    time: 'Pengingat',
                    icon: '<i class="fa-solid fa-print"></i>'
                });
            }

            // ===== 2. MENAMPILKAN NOTIFIKASI LOGIN =====
            // [FIX] Cek apakah user sudah menekan "Tandai dibaca"?
            const isNotifViewed = sessionStorage.getItem('notif_viewed') === 'true';

            // Jika BELUM dibaca, tampilkan terus (Persistent)
            if (!isNotifViewed) {
                notifications.push({
                    type: 'login',
                    title: 'Login Berhasil',
                    desc: `Semua riwayat login Anda tersimpan di bawah.`,
                    descExtended: historyHTML,
                    time: 'Baru saja',
                    icon: "{{ asset('icons/notif_login.png') }}"
                });
            }

            // ===== CHECK BADGE STATE =====
            if (notifBadge) {
                if (!isNotifViewed && notifications.length > 0) {
                    notifBadge.style.display = 'block';
                } else {
                    notifBadge.style.display = 'none';
                }
            }

            // Kode fungsi render notifikasi
            // --- RENDER NOTIFIKASI ---
            function renderNotifications() {
                if (!notifList) return;
                notifList.innerHTML = '';

                if (notifications.length === 0) {
                    notifList.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.9rem;">Tidak ada notifikasi baru</div>';
                    return;
                }

                notifications.forEach(notif => {
                    const item = document.createElement('div');
                    item.className = 'notif-item';
                    const iconClass = notif.type === 'print' ? 'icon-blue-light' : 'icon-green-light';

                    let iconHTML = '';
                    if (notif.type === 'login') {
                        iconHTML = `<img src="${notif.icon}" alt="Login Icon" style="width:24px; height:24px; object-fit:contain;">`;
                    } else {
                        iconHTML = notif.icon;
                    }

                    const extendedContent = notif.descExtended ? `${notif.descExtended}` : '';

                    item.innerHTML = `
                        <div class="notif-icon ${iconClass}">${iconHTML}</div>
                        <div class="notif-content">
                            <p class="notif-title">${notif.title}</p>
                            <p class="notif-desc">${notif.desc}</p>
                            ${extendedContent}
                            <span class="notif-time">${notif.time}</span>
                        </div>
                    `;
                    notifList.appendChild(item);
                });
            }

            // --- EVENT LISTENERS ---
            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifMenu.style.display = notifMenu.style.display === 'block' ? 'none' : 'block';
                });
            }

            if (notifBtn && notifMenu) {
                document.addEventListener('click', (e) => {
                    if (!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
                        notifMenu.style.display = 'none';
                    }
                });
            }

            // Clear Notif (Hanya Visual & Set Flag)
            window.clearNotifications = function() {
                notifications.length = 0;
                renderNotifications();
                sessionStorage.setItem('notif_viewed', 'true'); // Tandai sudah dibaca
                if (notifBadge) notifBadge.style.display = 'none';
            };

            if (notifList) renderNotifications();

            // Toggle Profile Menu
            if (profileBtn) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileBtn.classList.toggle('active');
                });
            }
            document.addEventListener('click', function(e) {
                if (profileBtn && !profileBtn.contains(e.target)) {
                    profileBtn.classList.remove('active');
                }
            });

            // Kode fungsi logout
            // 3. LOGIKA LOGOUT
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Keluar...</span>';
                    this.style.pointerEvents = 'none';

                    // Bersih-bersih
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                    localStorage.removeItem('login_history');
                    sessionStorage.clear(); // Hapus semua flag (login_recorded & notif_viewed)

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('/logout') }}";
                    form.style.display = 'none';
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        });
    </script>
    {{-- // Kode fungsi fetch data profil --}}
    <script>
        (function() {
            const token = localStorage.getItem('auth_token');
            const businessNameEl = document.getElementById('global-header-business-name');
            const avatarEl = document.getElementById('global-header-avatar');

            if (token && businessNameEl && avatarEl) {
                fetch("{{ url('/api/profile') }}", {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        }
                    })
                    .then(response => {
                        if (response.status === 401) {
                            localStorage.removeItem('auth_token');
                            window.location.href = "{{ url('/login') }}";
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.business && data.business.nama_usaha) {
                            businessNameEl.textContent = data.business.nama_usaha;
                        } else if (data.user && data.user.name) {
                            businessNameEl.textContent = data.user.name;
                        }

                        if (data.business && data.business.logo_url) {
                            avatarEl.innerHTML = `<img src="${data.business.logo_url}" alt="Logo" class="profile-avatar-pojok">`;
                        } else if (data.user && data.user.name) {
                            avatarEl.innerHTML = `<div class="default-avatar-pojok">${data.user.name.charAt(0).toUpperCase()}</div>`;
                        }
                    })
                    .catch(err => {});
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
