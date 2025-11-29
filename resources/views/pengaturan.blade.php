@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')

<div class="content-card settings-card">

    <div class="profile-header-container">
        <div class="profile-header-banner">
        </div>
        
        <div class="profile-header-content">
            <div class="profile-avatar-placeholder" id="profile-avatar">
                @if($user->perusahaan && $user->perusahaan->logo)
                    <img src="{{ asset($user->perusahaan->logo) }}" alt="Logo Usaha" 
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
                    <input type="text" id="nama_usaha" name="nama_perusahaan" maxlength="32"
                        value="{{ $user->perusahaan->nama_perusahaan ?? '' }}" 
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
                    <div class="password-wrapper"> 
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
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••">
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>

            <div class="form-group-row">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
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
        // 1. Mencegah link melompat ke atas (default href="#")
        if(event) event.preventDefault();

        // 2. Sembunyikan semua konten tab (class .tab-pane dari kode HTML sebelumnya)
        document.querySelectorAll('.tab-pane').forEach(el => {
            el.style.display = 'none';
            el.classList.remove('active'); // Hapus class active dari konten
        });

        // 3. Matikan status active di semua tombol navigasi (class .tab-item punya Anda)
        document.querySelectorAll('.tab-item').forEach(btn => {
            btn.classList.remove('active');
        });

        // 4. Tampilkan konten yang dipilih (Target ID: pane-usaha / pane-akun)
        const selectedPane = document.getElementById('pane-' + tabName);
        if (selectedPane) {
            selectedPane.style.display = 'block';
        }

        // 5. Aktifkan tombol navigasi yang diklik (Target ID: tab-usaha / tab-akun)
        const selectedBtn = document.getElementById('tab-' + tabName);
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. LOGIKA ALERT / FLASH MESSAGE (Otomatis Hilang) ---
        const successAlert = document.getElementById('auto-close-alert');
        
        if (successAlert) {
            // Tunggu 4 detik
            setTimeout(() => {
                // Tambahkan class animasi keluar
                successAlert.classList.add('fade-out');
                
                // Hapus dari HTML setelah animasi selesai (0.5 detik)
                setTimeout(() => {
                    successAlert.remove();
                }, 500);
            }, 4000); // 4000 ms = 4 detik
        }

        // --- 3. LOGIKA MATA PASSWORD (Opsional untuk Tab Akun nanti) ---
        document.querySelectorAll('.password-toggle-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                
                // Pastikan elemen yang ditemukan benar-benar INPUT
                if (input && input.tagName === 'INPUT') {
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        input.type = 'password';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    }
                }
            });
        });

    });
</script>
@endpush