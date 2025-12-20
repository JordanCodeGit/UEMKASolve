@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
{{-- // Bagian Form Lupa Password --}}
<div class="auth-form">
    <h2>Lupa Password</h2>
    
    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
        Masukkan email Anda. Kami akan mengirimkan link untuk mereset password Anda.
    </p>

    {{-- 1. Tampilkan Pesan Sukses (Jika Link Terkirim) --}}
    @if (session('status'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #a7f3d0;">
            <i class="fa-solid fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    {{-- 2. Tampilkan Error (Jika Email Tidak Ada) --}}
    @if ($errors->any())
        <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fecaca;">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first('email') }}
        </div>
    @endif

    {{-- 3. Form Utama --}}
    <form action="{{ route('password.email') }}" method="POST">
        @csrf {{-- WAJIB ADA --}}

        <div class="form-group">
            <input type="email" name="email" id="email" 
                   placeholder="Masukkan Email" 
                   value="{{ old('email') }}" 
                   required
                   style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;">
        </div>

        {{-- Tombol Kirim Permintaan --}}
        <div class="form-group" style="margin-top: 20px;">
             <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 8px; font-weight: 600;">
                Kirim Link Reset
             </button>
        </div>
    </form>

    {{-- Link Kembali ke Login --}}
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('login') }}" style="text-decoration: none; color: #64748b; font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
        </a>
    </div>

</div>
@endsection