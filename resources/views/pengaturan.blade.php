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
            margin: 0;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            background-color: #f5f7fa;
        }

        .settings-content-card {
            padding: 0 !important;
            border-radius: 0;
        }

        .profile-header-content {
            top: -30px;
            right: 0;
        }
    }

    /* Responsive untuk Pengaturan - Mobile */
    @media (max-width: 480px) {
        .content-card.settings-card {
            margin: 0;
            border-radius: 0;
            padding: 0;
        }

        .settings-content-card {
            padding: 0 !important;
        }
    }
</style>

<div class="content-card settings-card">

    <div class="profile-header-container">
        <div class="profile-header-banner">
        </div>

        <div class="profile-header-content">
            <div class="profile-avatar-placeholder" id="profile-avatar">
                {{-- [FIX] Menggunakan business->logo_path dan asset('storage/...') --}}
                @if($user->business && $user->business->logo_path)
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

        <div class="alert-container">

            @if(session('success'))
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

            @if($errors->any())
                <div class="alert-popup alert-error">
                    <div class="alert-icon">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="alert-message">
                        <strong>Gagal!</strong>
                        <ul>
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

        </div>

        <div class="tab-pane active" id="pane-usaha">

            <form id="form-profil-usaha" action="{{ route('pengaturan.update.usaha') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h3 class="form-section-title">Ubah Profil Usaha</h3>

                <div class="form-group-row">
                    <label for="nama_usaha">Nama Usaha</label>
                    {{-- [FIX] Menggunakan nama_usaha (bukan nama_perusahaan) untuk name dan value --}}
                    <input type="text" id="nama_usaha" name="nama_usaha" maxlength="32"
                        value="{{ $user->business->nama_usaha ?? '' }}"
                        placeholder="Contoh: Toko Kopi Saya">
                    <small>Nama usaha akan muncul di laporan PDF</small>
                </div>

                <div class="form-group-row">
                    <label>Logo Usaha</label>

                    <input type="file" name="logo" accept="image/*">
                    <small>Format: PNG, JPG, max 2MB</small>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

    </div>

   <div class="tab-pane" id="pane-akun" style="display: none;">

        <form id="form-profil-akun" action="{{ route('pengaturan.update.akun') }}" method="POST">
            @csrf

            <h3 class="form-section-title">Ubah Akun</h3>

            <div class="form-row-split">
                <div class="form-col">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="name"
                        value="{{ $user->name }}" required>
                </div>
                <div class="form-col">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled
                           style="background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed;">

                    <input type="hidden" name="email" value="{{ $user->email }}">
                </div>
            </div>

            <h3 class="form-section-title" style="margin-top: 30px;">Ubah Password</h3>

            @if(Auth::user()->password !== null)
                <div class="form-group-row">
                    <label for="current_password">Password Saat Ini</label>
                    <div class="password-wrapper-settings">
                        <input type="password" id="current_password" name="current_password" placeholder="••••••••">
                        <i class="fa-solid fa-eye password-toggle-icon"></i>
                    </div>
                </div>
            @else
                <div class="alert-floating alert-success" style="margin-bottom: 15px; background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                    <i class="fa-solid fa-info-circle"></i> Anda login via Google. Silakan buat password baru untuk login manual (opsional).
                </div>
            @endif

            <div class="form-group-row">
                <label for="password">Password Baru</label>
                <div class="password-wrapper-settings">
                    <input type="password" id="password" name="password" placeholder="••••••••">
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>

            <div class="form-group-row">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="password-wrapper-settings">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>

    </div>

</div>
@endsection

@push('scripts')
<script>

    function switchTab(event, tabName) {
        if(event) event.preventDefault();

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

    document.addEventListener("DOMContentLoaded", function() {

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

    });
</script>
@endpush
