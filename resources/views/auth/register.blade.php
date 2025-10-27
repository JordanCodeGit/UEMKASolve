@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="auth-form">
    <h2>Daftar</h2>
    <form action="#" method="POST">
        <div class="form-group">
            <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama lengkap" required>
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
    <div class="divider">
        <span>atau</span>
    </div>
    <button class="btn btn-google">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google icon">
        Daftar dengan Google
    </button>
</div>
@endsection