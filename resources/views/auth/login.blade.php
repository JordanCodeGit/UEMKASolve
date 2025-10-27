@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="auth-form">
    <h2>Masuk</h2>
    <form action="#" method="POST">
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