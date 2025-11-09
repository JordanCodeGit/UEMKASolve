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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const registerForm = document.getElementById('register-form');
        const messageDiv = document.getElementById('form-message');

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            messageDiv.textContent = 'Mendaftarkan...';
            messageDiv.style.color = 'gray';

            const formData = new FormData(registerForm);
            
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
                    messageDiv.textContent = 'Registrasi berhasil! Silakan cek email untuk verifikasi. Mengarahkan ke login...';
                    messageDiv.style.color = 'green';
                    
                    setTimeout(() => {
                        window.location.href = '{{ url("/login") }}';
                    }, 3000);
                
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