@extends('layouts.auth') {{-- Menggunakan layout yang sama dengan "Daftar" --}}

@section('title', 'Reset Password')

@section('content')
    <div class="auth-form">
        <h2>Reset Password</h2>

        <form id="reset-password-form">

            <input type="hidden" id="reset_token" name="token" value="{{ $token }}">
            <input type="hidden" id="reset_email" name="email" value="{{ $email }}">

            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password Baru" required>
            </div>
            <div class="form-group">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    placeholder="Konfirmasi Password Baru" required>
            </div>

            <div id="form-message" style="margin: 15px 0; font-size: 14px;"></div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Reset Password</button>
                <a href="{{ url('/login') }}" class="btn btn-secondary">Masuk</a>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // // --- 1. Ambil Token & Email dari URL ---
            // const urlParams = new URLSearchParams(window.location.search);
            // const token = urlParams.get('token');
            // const email = urlParams.get('email');

            // // --- 2. Masukkan ke field tersembunyi ---
            // const tokenInput = document.getElementById('reset_token');
            // const emailInput = document.getElementById('reset_email');
            // if (tokenInput && emailInput) {
            //     tokenInput.value = token;
            //     emailInput.value = email;
            // } else {
            //     console.error('Hidden fields for token/email not found!');
            // }

            // --- 3. Tangani Form Submission ---
            const form = document.getElementById('reset-password-form');
            const messageDiv = document.getElementById('form-message');

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Hentikan submit HTML biasa

                messageDiv.textContent = 'Memproses...';
                messageDiv.style.color = 'gray';

                // Ambil semua data dari form (termasuk yang tersembunyi)
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // --- 4. Kirim ke API Back-End Anda ---
                fetch('http://127.0.0.1:8000/api/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.message) {
                            if (result.message === 'Password Anda telah berhasil direset.') {
                                // SUKSES
                                messageDiv.textContent = result.message +
                                    ' Anda akan diarahkan ke halaman login.';
                                messageDiv.style.color = 'green';
                                // Redirect ke login setelah 3 detik
                                setTimeout(() => {
                                    window.location.href = '{{ url('/login') }}';
                                }, 3000);
                            } else {
                                // GAGAL (misal: token tidak valid)
                                messageDiv.textContent = 'Error: ' + result.message;
                                messageDiv.style.color = 'red';
                            }
                        } else if (result.errors) {
                            // GAGAL (Validasi, misal password tidak cocok)
                            const firstError = Object.values(result.errors)[0][0];
                            messageDiv.textContent = 'Error: ' + firstError;
                            messageDiv.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                        messageDiv.style.color = 'red';
                    });
            });
        });
    </script>
@endsection
