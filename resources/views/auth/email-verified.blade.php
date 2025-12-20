@extends('layouts.auth')

@section('title', 'Email Terverifikasi')

@section('content')
{{-- // Bagian Notifikasi Verifikasi Berhasil --}}
<div class="auth-form" style="text-align: center; padding: 40px 20px;">
    <div style="margin-bottom: 30px;">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    
    <h2 style="color: #4CAF50; margin-bottom: 15px;">Email Berhasil Diverifikasi!</h2>
    
    <p style="font-size: 16px; color: #666; margin-bottom: 10px;">
        Terima kasih telah memverifikasi email Anda.
    </p>
    
    <p style="font-size: 14px; color: #999; margin-bottom: 30px;">
        Akun Anda sekarang siap digunakan. Silakan kembali ke halaman login untuk masuk.
    </p>
    
    <a href="{{ url('/login') }}" class="btn btn-primary" style="display: inline-block; padding: 12px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Kembali ke Login
    </a>
    
    <p style="font-size: 12px; color: #ccc; margin-top: 30px;">
        Anda akan dialihkan secara otomatis dalam 5 detik...
    </p>
</div>

<script>
    // Kode pengalihan otomatis ke login
    setTimeout(function() {
        window.location.href = '{{ url("/login") }}';
    }, 5000);
</script>
@endsection
