<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Rute untuk halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Rute untuk halaman Register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/lupa-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/reset-password/{token}', function (Request $request, $token) {
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email
    ]);
})->name('password.reset');

Route::get('/pengaturan', function () {
    return view('pengaturan');
})->name('pengaturan');

// Tambahkan ini sebagai default setelah login/register
Route::get('/dashboard', function () {
    return "Halaman Dashboard";
})->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard'); // Arahkan ke file baru
})->name('dashboard');

Route::get('/buku-kas', function () {
    return view('buku-kas'); // Arahkan ke file baru
})->name('buku-kas');

Route::get('/kategori', function () {
    return view('kategori');
})->name('kategori');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

