@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="auth-form">
    <h2>Daftar</h2>
    
    <form id="register-form">
        
        <div id="form-message" style="margin-bottom: 15px; font-size: 14px; text-align: center;"></div>

        <div class="form-group">
            <input type="text" name="name" id="name" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" id="password" placeholder="Password" required>
        </div>
        
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Daftar</button>
            <a href="{{ url('/login') }}" class="btn btn-secondary">Masuk</a>
        </div>
        
        </form>
</div>

<!-- Modal/Popup untuk Email Verification -->
<div id="verification-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 40px; border-radius: 8px; text-align: center; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                <path d="m3 4 9 8 9-8"></path>
            </svg>
        </div>
        <h3 style="color: #333; margin-bottom: 10px;">Verifikasi Email Anda</h3>
        <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
            Email verifikasi telah dikirim ke <strong id="email-display"></strong>
        </p>
        <p style="color: #999; margin-bottom: 20px; font-size: 13px;">
            Silakan cek inbox atau folder spam Anda. Klik link dalam email untuk memverifikasi akun Anda.
        </p>
        <a href="{{ url('/login') }}" class="btn btn-primary" style="display: inline-block; padding: 10px 25px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Kembali ke Login
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const registerForm = document.getElementById('register-form');
        const messageDiv = document.getElementById('form-message');
        const verificationModal = document.getElementById('verification-modal');
        const emailDisplay = document.getElementById('email-display');

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            messageDiv.textContent = 'Mendaftarkan...';
            messageDiv.style.color = 'gray';

            const formData = new FormData(registerForm);
            const email = formData.get('email');
            
            // Buat 'nama_usaha' dari 'name'
            const name = formData.get('name');
            formData.append('nama_usaha', 'Bisnis ' + name); 
            
            const data = Object.fromEntries(formData.entries());

            // Kirim data ke API Back-End
            fetch('{{ url("/api/register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                return response.json().then(result => ({ status: response.status, body: result }));
            })
            .then(result => {
                
                if (result.status === 201) {
                    // --- SUKSES ---
                    messageDiv.textContent = '';
                    
                    // Tampilkan modal verifikasi email
                    emailDisplay.textContent = email;
                    verificationModal.style.display = 'flex';
                    
                    // Reset form
                    registerForm.reset();
                
                } else if (result.status === 422) {
                    // --- GAGAL VALIDASI (Input salah) ---
                    const errors = result.body.errors;
                    const firstError = Object.values(errors)[0][0];
                    messageDiv.textContent = 'Error: ' + firstError;
                    messageDiv.style.color = 'red';
                
                } else {
                    // --- GAGAL LAINNYA (Server error) ---
                    messageDiv.textContent = 'Error: ' + (result.body.message || 'Terjadi kesalahan server.');
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'Tidak dapat terhubung ke server.';
                messageDiv.style.color = 'red';
            });
        });
    });
</script>
@endpush