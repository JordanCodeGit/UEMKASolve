@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')

    <style>
        /* Responsive untuk Pengaturan - Desktop */
        @media (max-width: 1024px) {
            .profile-header-container {
                padding: 30px 20px;
            }

            .profile-header-content {
                gap: 15px;
                padding: 0 15px;
            }

            .profile-avatar-placeholder {
                width: 150px;
                height: 150px;
            }
        }

        /* Responsive untuk Pengaturan - Tablet */
        @media (max-width: 768px) {
            .content-card.settings-card {
                margin: 12px;
                border-radius: 18px;
                padding: 0;
                overflow: hidden;
            }

            .settings-content-card {
                padding: 16px !important;
                border-radius: 0;
            }

            .profile-header-content {
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                gap: 10px;
                padding: 0;
                top: -44px;
                right: auto;
                left: 0;
                margin-bottom: -44px;
            }

            .profile-header-container {
                padding: 16px 16px 0 16px;
                margin-bottom: 0;
            }

            .profile-header-banner {
                height: 150px;
                border-radius: 18px;
            }

            .profile-avatar-placeholder {
                width: 110px;
                height: 110px;
                border-radius: 14px;
            }

            .profile-info {
                padding-top: 0;
            }

            .profile-info h2 {
                font-size: 20px;
            }

            .tabs-nav-container {
                padding: 10px 16px 0 16px;
            }

            .tabs-nav.full-width {
                border-radius: 16px;
            }

            .tabs-nav.full-width .tab-item {
                font-size: 13px;
                padding: 10px 10px;
                border-radius: 14px;
            }

            /* Logout button only needed on mobile/tablet (no header) */
            .settings-mobile-logout {
                display: block !important;
            }
        }

        /* Responsive untuk Pengaturan - Mobile */
        @media (max-width: 480px) {
            .content-card.settings-card {
                margin: 10px;
                border-radius: 18px;
                padding: 0;
            }

            .settings-content-card {
                padding: 14px !important;
            }
        }

        /* Helper Text Error */
        .text-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Hidden by default (desktop has header logout) */
        .settings-mobile-logout {
            display: none;
            margin-top: 12px;
        }

        .settings-logout-btn {
            width: 100%;
            background: transparent;
            border: 1px solid var(--color-primary-red);
            color: var(--color-primary-red);
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .settings-logout-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

    <div class="content-card settings-card" data-active-tab="{{ session('active_tab') }}">

        {{-- // Bagian Header Profil (Banner & Avatar) --}}
        <div class="profile-header-container">
            <div class="profile-header-banner">
            </div>

            <div class="profile-header-content">
                <div class="profile-avatar-placeholder" id="profile-avatar">
                    {{-- [FIX] Menggunakan business->logo_path dan asset('storage/...') --}}
                    @if ($user->business && $user->business->logo_path)
                        <img src="{{ asset('storage/' . $user->business->logo_path) }}" alt="Logo Usaha"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                    @else
                        <i class="fa-solid fa-shop" style="font-size: 2.5rem; color: #94a3b8;"></i>
                    @endif
                </div>

                <div class="profile-info">
                    <h2 id="profile-name">{{ $user->name }}</h2>
                    <span id="profile-email">{{ $user->email }}</span>
                </div>
            </div>
        </div>

        {{-- // Navigasi Tab (Profil Usaha / Profil Akun) --}}
        <div class="tabs-nav-container">
            <nav class="tabs-nav full-width">
                <a href="#" class="tab-item active" id="tab-usaha" onclick="switchTab(event, 'usaha')">
                    <i class="fa-solid fa-shop"></i> Profil Usaha
                </a>

                <a href="#" class="tab-item" id="tab-akun" onclick="switchTab(event, 'akun')">
                    <i class="fa-solid fa-user-gear"></i> Profil Akun
                </a>
            </nav>
        </div>

        <div class="settings-content-card">

            {{-- // Bagian Notifikasi (Success/Error) --}}
            <div class="alert-container">

                @if (session('success'))
                    <div class="alert-popup alert-success" id="auto-close-alert">
                        <div class="alert-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="alert-message">
                            <strong>Berhasil!</strong>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                {{-- Error Global (opsional, karena sudah ada error inline di bawah) --}}
                @if ($errors->any())
                    <div class="alert-popup alert-error">
                        <div class="alert-icon">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                        <div class="alert-message">
                            <strong>Gagal!</strong>
                            <span>Periksa kembali inputan Anda.</span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

            </div>

            {{-- // Panel Tab: Profil Usaha --}}
            <div class="tab-pane active" id="pane-usaha">

                <form id="form-profil-usaha" action="{{ route('pengaturan.update.usaha') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <h3 class="form-section-title">Ubah Profil Usaha</h3>

                    <div class="form-group-row">
                        <label for="nama_usaha">Nama Usaha</label>
                        {{-- [FIX] Menggunakan nama_usaha (bukan nama_perusahaan) --}}
                        <input type="text" id="nama_usaha" name="nama_usaha" maxlength="32"
                            value="{{ $user->business->nama_usaha ?? '' }}" placeholder="Contoh: Toko Kopi Saya">
                        <small>Nama usaha akan muncul di laporan PDF</small>
                        @error('nama_usaha')
                            <small class="text-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group-row">
                        <label>Logo Usaha</label>

                        <input type="file" name="logo" accept="image/*">
                        <small>Format: PNG, JPG, max 2MB</small>
                        @error('logo')
                            <small class="text-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>

                    {{-- <div class="settings-mobile-logout">
                        <button type="button" class="settings-logout-btn" data-settings-logout>
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </button>
                    </div> --}}
                </form>
            </div>

        </div>

        {{-- // Panel Tab: Profil Akun --}}
        <div class="tab-pane" id="pane-akun" style="display: none;">

            <form id="form-profil-akun" action="{{ route('pengaturan.update.akun') }}" method="POST">
                @csrf

                <h3 class="form-section-title">Ubah Akun</h3>

                <div class="form-row-split">
                    <div class="form-col">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="name" value="{{ $user->name }}" required>
                        @error('name')
                            <small class="text-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-col">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled
                            style="background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed;">

                        <input type="hidden" name="email" value="{{ $user->email }}">
                    </div>
                </div>

                <h3 class="form-section-title" style="margin-top: 30px;">Ubah Password</h3>

                {{-- LOGIKA TAMPILAN: Cek User Biasa vs Google --}}
                @if (Auth::user()->password !== null)
                    {{-- CASE: USER BIASA (Wajib isi password lama) --}}
                    <div class="form-group-row">
                        <label for="current_password">Password Saat Ini <span style="color:red">*</span></label>
                        <div class="password-wrapper-settings">
                            <input type="password" id="current_password" name="current_password" placeholder="••••••••">
                            <i class="fa-solid fa-eye password-toggle-icon"></i>
                        </div>
                        @error('current_password')
                            <small class="text-error">{{ $message }}</small>
                        @enderror
                    </div>
                @else
                    {{-- CASE: USER GOOGLE (Info saja) --}}
                    <div
                        style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 12px;">
                        <i class="fa-brands fa-google" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Anda login menggunakan Google.</strong><br>
                            Anda belum memiliki password. Silakan buat password baru di bawah ini agar bisa login secara
                            manual.
                        </div>
                    </div>
                @endif

                <div class="form-group-row">
                    <label for="password">Password Baru</label>
                    <div class="password-wrapper-settings">
                        <input type="password" id="password" name="password" placeholder="••••••••">
                        <i class="fa-solid fa-eye password-toggle-icon"></i>
                    </div>
                    @error('password')
                        <small class="text-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group-row">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <div class="password-wrapper-settings">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="••••••••">
                        <i class="fa-solid fa-eye password-toggle-icon"></i>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>

                <div class="settings-mobile-logout">
                    <button type="button" class="settings-logout-btn" data-settings-logout>
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </button>
                </div>
            </form>

        </div>

    </div>
@endsection

@push('scripts')
    {{-- // Kode fungsi berpindah antar tab pengaturan --}}
    <script>
        function switchTab(event, tabName) {
            if (event) event.preventDefault();

            document.querySelectorAll('.tab-pane').forEach(el => {
                el.style.display = 'none';
                el.classList.remove('active');
            });

            document.querySelectorAll('.tab-item').forEach(btn => {
                btn.classList.remove('active');
            });

            const selectedPane = document.getElementById('pane-' + tabName);
            if (selectedPane) {
                selectedPane.style.display = 'block';
            }

            const selectedBtn = document.getElementById('tab-' + tabName);
            if (selectedBtn) {
                selectedBtn.classList.add('active');
            }
        }
// Kode inisialisasi saat halaman dimuat

        document.addEventListener("DOMContentLoaded", function() {
            const tabToActivate = document.querySelector('.content-card.settings-card')?.dataset?.activeTab;
            if (tabToActivate) switchTab(null, tabToActivate);

            const successAlert = document.getElementById('auto-close-alert');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.add('fade-out');
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500);
                }, 4000);
            }

            document.querySelectorAll('.password-toggle-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    if (input && input.tagName === 'INPUT') {
                        if (input.type === 'password') {
                            input.type = 'text';
                            this.classList.remove('fa-eye');
                            this.classList.add('fa-eye-slash');
                        } else {
                            input.type = 'password';
                            this.classList.remove('fa-eye-slash');
                            this.classList.add('fa-eye');
                        }
                    }
                });
            });

            // Logout button for mobile (no header)
            document.querySelectorAll('[data-settings-logout]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    this.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Keluar...';
                    this.disabled = true;

                    // Bersih-bersih (samakan dengan logic logout di header)
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                    localStorage.removeItem('login_history');
                    sessionStorage.clear();

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
            });

        });
    </script>
@endpush
