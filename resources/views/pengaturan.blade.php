@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')

<div class="content-card settings-card">

    <div class="profile-header-container">
        <div class="profile-header-banner">
            </div>
        <div class="profile-header-content">
            <div class="profile-avatar-placeholder" id="profile-avatar">
                </div>
            <div class="profile-info">
                <h2 id="profile-name">Memuat...</h2>
                <span id="profile-email">Memuat...</span>
            </div>
        </div>
    </div>

    <div class="tabs-nav-container">
        <nav class="tabs-nav full-width">
            <a href="#" class="tab-item active" id="tab-usaha">
                <i class="fa-solid fa-shop"></i> Profil Usaha
            </a>
            <a href="#" class="tab-item" id="tab-akun">
                <i class="fa-solid fa-user-gear"></i> Profil Akun
            </a>
        </nav>
    </div>

    <div class="tab-pane active" id="pane-usaha">
        <form id="form-profil-usaha">
            <h3 class="form-section-title">Ubah Profil Usaha</h3>
            <div id="usaha-message" class="form-message"></div>
            
            <div class="form-group-row">
                <label for="nama_usaha">Nama Usaha</label>
                <input type="text" id="nama_usaha" name="nama_usaha" class="form-input">
                <small>Nama usaha akan muncul di laporan PDF</small>
            </div>
            
            <div class="form-group-row">
                <label for="logo_usaha">Logo Usaha</label>
                <input type="file" id="logo_usaha" name="logo" class="form-input-file">
                <small>Logo akan ditampilkan di laporan (format: PNG, JPG, max 2MB)</small>
            </div>
            
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <div class="tab-pane" id="pane-akun" style="display: none;">
    
        <form id="form-profil-akun">
            
            <h3 class="form-section-title">Ubah Akun</h3>
            <div class="form-group-row-split">
                <div class="form-group-row">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="name" class="form-input">
                </div>
                <div class="form-group-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input">
                </div>
            </div>
<br>
            <h3 class="form-section-title">Ubah Password</h3>
            
            <div class="form-group-row">
                <label for="current_password">Password Saat Ini</label>
                <div class="form-group-password"> 
                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>
            
            <div class="form-group-row">
                <label for="password">Password Baru</label>
                <div class="form-group-password">
                    <input type="password" id="password" name="password" class="form-input" required>
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>
            <div class="form-group-row">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="form-group-password">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>
            
            <div class="form-footer">
                <div id="akun-message" class="form-message"></div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
        
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '{{ url("/login") }}';
            return;
        }

        // --- Elemen Header ---
        const profileAvatar = document.getElementById('profile-avatar');
        const profileName = document.getElementById('profile-name');
        const profileEmail = document.getElementById('profile-email');

        // --- Elemen Tab ---
        const tabUsaha = document.getElementById('tab-usaha');
        const tabAkun = document.getElementById('tab-akun');
        const paneUsaha = document.getElementById('pane-usaha');
        const paneAkun = document.getElementById('pane-akun');

        // --- Form 1 (Profil Usaha) ---
        const formUsaha = document.getElementById('form-profil-usaha');
        const inputNamaUsaha = document.getElementById('nama_usaha');
        const inputLogoUsaha = document.getElementById('logo_usaha');
        const usahaMessage = document.getElementById('usaha-message');

        // --- Form 2 (Profil Akun & Password) ---
        const formAkun = document.getElementById('form-profil-akun');
        const inputNamaLengkap = document.getElementById('nama_lengkap');
        const inputEmail = document.getElementById('email');
        const inputCurrentPassword = document.getElementById('current_password');
        const inputNewPassword = document.getElementById('password');
        const akunMessage = document.getElementById('akun-message');

        // --- Helper untuk menampilkan pesan ---
        function showMessage(el, message, isError = true) {
            el.textContent = message;
            el.className = isError ? 'form-message error' : 'form-message success';
        }

        // --- 1. [READ] Ambil Data Profil Saat Halaman Dimuat ---
        async function fetchProfileData() {
            try {
                const response = await fetch('{{ url("/api/profile") }}', { 
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
                });
                if (response.status === 401) return window.location.href = '{{ url("/login") }}';
                
                const data = await response.json();
                
                profileName.textContent = data.user.name;
                profileEmail.textContent = data.user.email;
                document.getElementById('global-user-name').textContent = result.user.name;
                if (data.business && data.business.logo_url) {
                    profileAvatar.innerHTML = `<img src="${data.business.logo_url}" alt="Logo">`;
                } else {
                    profileAvatar.innerHTML = data.user.name.charAt(0).toUpperCase();
                }
                
                if (data.business) {
                    inputNamaUsaha.value = data.business.nama_usaha;
                }
                
                inputNamaLengkap.value = data.user.name;
                inputEmail.value = data.user.email;
                
            } catch (error) {
                console.error('Error fetching profile:', error);
                showMessage(usahaMessage, 'Gagal memuat data profil.', true);
            }
        }

        // --- 2. [UPDATE USAHA] Submit Form Profil Usaha (Form 1) ---
        formUsaha.addEventListener('submit', async function(e) {
            e.preventDefault();
            showMessage(usahaMessage, 'Menyimpan...', false);

            const formData = new FormData();
            formData.append('nama_usaha', inputNamaUsaha.value);
            
            if (inputLogoUsaha.files.length > 0) {
                formData.append('logo', inputLogoUsaha.files[0]);
            }
            
            try {
                const response = await fetch('{{ url("/api/profile/update") }}', {
                    method: 'POST', 
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: formData 
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    showMessage(usahaMessage, 'Profil usaha berhasil diperbarui.', false);
                    
                    // Ambil elemen header global
                    const globalAvatar = document.getElementById('global-header-avatar');
                    
                    // Update avatar di halaman setting
                    if (result.business && result.business.logo_url) {
                        const newImgHtml = `<img src="${result.business.logo_url}" alt="Logo">`;
                        profileAvatar.innerHTML = newImgHtml;
                        
                        // [PERBAIKAN] Update juga avatar di header global
                        if (globalAvatar) {
                            globalAvatar.innerHTML = newImgHtml;
                        }
                    }
                } else if (response.status === 422) {
                    showMessage(usahaMessage, 'Error: ' + Object.values(result.errors)[0][0], true);
                } else {
                    showMessage(usahaMessage, 'Error: ' + (result.message || 'Gagal menyimpan.'), true);
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showMessage(usahaMessage, 'Gagal terhubung ke server.', true);
            }
        });

        // --- 3. [UPDATE AKUN] Submit Form Profil Akun (Form 2) ---
        // [ATURAN 6] Satu tombol ini menangani 2 API call
        formAkun.addEventListener('submit', async function(e) {
            e.preventDefault();
            showMessage(akunMessage, 'Menyimpan...', false);
            
            let success = true;
            
            // --- API Call 1: Update Nama & Email ---
            const akunData = {
                name: inputNamaLengkap.value,
                email: inputEmail.value
            };
            
            try {
                const response = await fetch('{{ url("/api/profile/update") }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                    body: JSON.stringify(akunData)
                });
                const result = await response.json();
                if (!response.ok) {
                    success = false;
                    if (response.status === 422) showMessage(akunMessage, 'Error Profil: ' + Object.values(result.errors)[0][0], true);
                    else showMessage(akunMessage, 'Error Profil: ' + (result.message || 'Gagal menyimpan.'), true);
                }
            } catch (error) {
                success = false;
                showMessage(akunMessage, 'Error koneksi (Profil).', true);
            }

            // --- API Call 2: Update Password (Hanya jika diisi) ---
            if (inputCurrentPassword.value) {
                const passwordData = {
                    current_password: inputCurrentPassword.value,
                    password: inputNewPassword.value,
                    password_confirmation: document.getElementById('password_confirmation').value
                };

                try {
                    const response = await fetch('{{ url("/api/profile/change-password") }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                        body: JSON.stringify(passwordData)
                    });
                    const result = await response.json();
                    
                    if (!response.ok) {
                        success = false;
                        if (response.status === 422) showMessage(akunMessage, 'Error Password: ' + Object.values(result.errors)[0][0], true);
                        else showMessage(akunMessage, 'Error Password: ' + (result.message || 'Gagal menyimpan.'), true);
                    }
                } catch (error) {
                    success = false;
                    showMessage(akunMessage, 'Error koneksi (Password).', true);
                }
            }
            
            // --- Tampilkan Pesan Sukses Akhir ---
            if (success) {
                showMessage(akunMessage, 'Perubahan berhasil disimpan.', false);
                formPassword.reset(); 

                // [PERBAIKAN] Ambil data terbaru untuk update header
                const profileResponse = await fetch('{{ url("/api/profile") }}', { headers: API_HEADERS });
                const profileData = await profileResponse.json();

                // Update header di halaman setting
                profileName.textContent = profileData.user.name;
                profileEmail.textContent = profileData.user.email;

                // Update header global
                document.getElementById('global-header-business-name').textContent = profileData.business.nama_usaha;
                if (profileData.user.name) {
                    document.getElementById('global-header-avatar').textContent = profileData.user.name.charAt(0).toUpperCase();
                }
            }
        });

        // --- 4. [ATURAN 5] Logika Ganti Tab ---
        tabUsaha.addEventListener('click', (e) => {
            e.preventDefault();
            tabUsaha.classList.add('active');
            tabAkun.classList.remove('active');
            paneUsaha.style.display = 'block';
            paneAkun.style.display = 'none';
        });
        tabAkun.addEventListener('click', (e) => {
            e.preventDefault();
            tabAkun.classList.add('active');
            tabUsaha.classList.remove('active');
            paneAkun.style.display = 'block';
            paneUsaha.style.display = 'none';
        });
        
        // --- 5. [ATURAN 5] Logika Ikon Mata Password ---
        document.querySelectorAll('.password-toggle-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling; // Ambil input
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        // --- Panggilan Awal ---
        fetchProfileData(); // Ambil data saat halaman dimuat
    });
</script>
@endpush