@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="auth-form">
    <h2>Masuk</h2>
    
    <form action="#" method="POST" id="login-form">
        <div id="form-message" style="color: red; margin-bottom: 15px; font-size: 14px; font-weight: bold; min-height: 20px;"></div>
        
        <div class="form-group">
            <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        
        <div class="form-group">
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fa-solid fa-eye password-toggle-icon"></i>
            </div>
        </div>
        
        <div class="form-options">
            <label class="checkbox-container">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="forgot-password">Lupa Password?</a>
        </div>
        
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Masuk</button>
            <a href="{{ url('/register') }}" class="btn btn-secondary">Daftar</a>
        </div>
    </form>
    
    <div class="divider">
        <span>atau</span>
    </div>
    
    <a href="{{ route('login.google') }}" class="btn-login-google">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google icon">
        Masuk dengan Google
    </a>
</div>
@endsection    

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. BERSIH-BERSIH SESSION LAMA (PENTING)
        // Hapus token lama setiap kali buka halaman login agar tidak bentrok
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        console.log('Session lama telah dibersihkan.');

        // 2. SETUP VARIABEL
        const loginForm = document.getElementById('login-form');
        const messageDiv = document.getElementById('form-message');
        // Ambil tombol submit agar bisa di-disable saat loading
        const submitBtn = loginForm.querySelector('button[type="submit"]'); 

        // 3. EVENT LISTENER SUBMIT
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // UI Loading
            messageDiv.textContent = 'Memproses...';
            messageDiv.style.color = 'blue';
            submitBtn.disabled = true; // Cegah klik ganda
            submitBtn.textContent = 'Loading...';

            // Ambil data form
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            // FIX: Convert checkbox value "on" ke boolean true/false
            // Checkbox HTML: <input type="checkbox" name="remember">
            // Ketika di-cek: formData.get('remember') = "on"
            // Ketika tidak: formData.get('remember') = undefined
            data.remember = data.remember === 'on' ? true : false;

            // [PERBAIKAN UTAMA DI SINI] 
            // 1. Gunakan route('login.process') yang ada di web.php (bukan /api/login)
            // 2. Tambahkan Header 'X-CSRF-TOKEN' (Wajib untuk form web Laravel)
            
            fetch('{{ route("login.process") }}', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // <--- WAJIB ADA
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Cek jika error 419 (CSRF Token Mismatch)
                if (response.status === 419) {
                    throw new Error('Halaman kadaluarsa, silakan refresh browser.');
                }
                return response.json();
            })
            .then(result => {
                if (result.access_token) {
                    // --- JIKA SUKSES ---
                    messageDiv.textContent = 'Login berhasil! Mengarahkan...';
                    messageDiv.style.color = 'green';
                    
                    // Simpan Token untuk kebutuhan API di Dashboard nanti
                    localStorage.setItem('auth_token', result.access_token);
                    
                    // Redirect ke Dashboard
                    // Karena Session Web sudah dibuat di Controller, ini tidak akan mental lagi
                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard") }}'; 
                    }, 500);
                
                } else {
                    // --- JIKA GAGAL ---
                    // Tampilkan pesan error dari server (misal: "Email belum terdaftar")
                    throw new Error(result.message || 'Email atau password salah.');
                }
            })
            .catch(error => {
                console.error('Login Error:', error);
                messageDiv.textContent = error.message;
                messageDiv.style.color = 'red';
                
                // Reset Tombol
                submitBtn.disabled = false;
                submitBtn.textContent = 'Masuk';
            });
        });

        // 4. TOGGLE PASSWORD VISIBILITY
        document.querySelectorAll('.password-toggle-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                
                // Pastikan elemen yang ditemukan benar-benar INPUT
                if (input && input.tagName === 'INPUT') {
                    if (input.type === 'password') {
                        // Password → Text (Buka mata)
                        input.type = 'text';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        // Text → Password (Tutup mata)
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