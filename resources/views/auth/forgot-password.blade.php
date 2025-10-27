@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<div class="auth-form">
    <h2>Lupa Password</h2>
    <form action="#" method="POST">
        <div class="form-group">
            <input type="email" name="email" id="email" placeholder="Masukkan Email" required>
        </div>

        {{-- Tombol Kirim Permintaan --}}
        <div class="form-group">
             <button type="submit" class="btn btn-primary" style="width: 100%;">Kirim Permintaan</button>
        </div>
       
    </form>

    {{-- Tombol Navigasi Bawah --}}
    <div class="form-buttons" style="margin-top: 20px;">
        <a href="{{ url('/login') }}" class="btn btn-secondary">Masuk</a>
        <a href="{{ url('/register') }}" class="btn btn-secondary">Daftar</a>
    </div>

</div>
@endsection