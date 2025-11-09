@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="auth-form">
    <h2>Masuk</h2>
    <form action="#" method="POST" id="login-form">
        <div id="form-message" style="color: red; margin-bottom: 15px; font-size: 14px;"></div>
        <div class="form-group">
            <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" id="password" placeholder="Password" required>
        </div>
        <div class="form-options">
            <label class="checkbox-container">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
            <a href="{{ url('/lupa-password') }}" class="forgot-password">Lupa Password?</a>
        </div>
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Masuk</button>
            <a href="{{ url('/register') }}" class="btn btn-secondary">Daftar</a>
        </div>
    </form>
    <div class="divider">
        <span>atau</span>
    </div>
    <button class="btn btn-google">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google icon">
        Masuk dengan Google
    </button>
</div>
@endsection     

@push('scripts')
<script>
    // Tunggu sampai halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Ambil elemen form dan pesan
        const loginForm = document.getElementById('login-form');
        const messageDiv = document.getElementById('form-message');

        // 2. Tambahkan event listener saat form di-submit
        loginForm.addEventListener('submit', function(e) {
            
            // 3. Hentikan submit form HTML biasa (agar halaman tidak refresh)
            e.preventDefault();
            
            messageDiv.textContent = 'Memproses...'; // Pesan loading

            // 4. Ambil data dari form
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            // 5. Kirim data ke API Back-End Anda menggunakan Fetch
            fetch('{{ url("/api/login") }}', { // Menggunakan URL API
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' // Minta respons JSON
                },
                body: JSON.stringify(data) // Kirim data sebagai JSON
            })
            .then(response => response.json()) // Ubah respons menjadi JSON
            .then(result => {
                // 6. Tangani Respons dari Back-End
                
                if (result.access_token) {
                    // JIKA SUKSES (dapat token)
                    messageDiv.textContent = 'Login berhasil! Mengarahkan...';
                    messageDiv.style.color = 'green';
                    
                    // [PENTING] Simpan token di browser (localStorage)
                    localStorage.setItem('auth_token', result.access_token);
                    
                    // Arahkan ke halaman dashboard
                    window.location.href = '{{ url("/dashboard") }}'; 
                
                } else {
                    // JIKA GAGAL (misal: "Email atau password salah")
                    messageDiv.textContent = result.message || 'Terjadi kesalahan.';
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                // Tangani error jaringan
                console.error('Error:', error);
                messageDiv.textContent = 'Tidak dapat terhubung ke server.';
                messageDiv.style.color = 'red';
            });
        });
    });
</script>
@endpush