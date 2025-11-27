@extends('layouts.auth') {{-- Menggunakan layout yang sama dengan "Daftar" --}}

@section('title', 'Reset Password')

@section('content')
    <div class="auth-form">
        <h2>Reset Password</h2>

        {{-- 1. Tampilkan Pesan Error Global (Jika validasi gagal) --}}
        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px; font-size: 14px; background-color: #fef2f2; padding: 10px; border-radius: 6px; border: 1px solid #fecaca;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 2. Form Standar (Action ke route password.update) --}}
        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password Baru" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    placeholder="Konfirmasi Password Baru" required>
            </div>

            {{-- Area pesan dihapus karena sudah diganti @if($errors) di atas --}}

            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Reset Password</button>
                <a href="{{ url('/login') }}" class="btn btn-secondary">Masuk</a>
            </div>

        </form>
    </div>
@endsection