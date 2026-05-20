<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Role - {{ config('app.name', 'UemkaSolve') }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="onboarding-page">
    <main class="onboarding-shell">
        <section class="onboarding-panel">
            <div class="onboarding-brand">
                <img src="{{ asset('icons/logo.png') }}" alt="UEMKASolve">
            </div>

            <h1>Selamat Datang di UEMKASolve</h1>
            <p>Pilih peran Anda untuk memulai manajemen keuangan yang lebih teratur.</p>

            <form action="{{ route('onboarding.store') }}" method="POST" class="role-picker">
                @csrf

                <div class="role-card-grid" aria-label="Pilih peran akun">
                <label class="role-card role-card--left">
                    <input type="radio" name="role" value="owner" required>
                    <span class="role-card-icon">
                        <img src="{{ asset('icons/owner.png') }}" alt="">
                    </span>
                    <span class="role-card-copy">
                        <strong>Pemilik Usaha</strong>
                        <small>Memegang kendali penuh atas bisnis dan memantau performa keuangan secara keseluruhan.</small>
                    </span>
                </label>

                <label class="role-card role-card--center">
                    <input type="radio" name="role" value="sekretaris" required>
                    <span class="role-card-icon">
                        <img src="{{ asset('icons/sekretaris.png') }}" alt="">
                    </span>
                    <span class="role-card-copy">
                        <strong>Sekretaris</strong>
                        <small>Bertanggung jawab untuk mengecek laporan keuangan dan mencetak kategori pengeluaran.</small>
                    </span>
                </label>

                <label class="role-card role-card--right">
                    <input type="radio" name="role" value="bendahara" required>
                    <span class="role-card-icon">
                        <img src="{{ asset('icons/bendahara.png') }}" alt="">
                    </span>
                    <span class="role-card-copy">
                        <strong>Bendahara</strong>
                        <small>Berfungsi untuk melakukan pencatatan transaksi masuk dan keluar secara akurat.</small>
                    </span>
                </label>
                </div>

                @error('role')
                    <div class="onboarding-error">{{ $message }}</div>
                @enderror

                <button type="submit" class="onboarding-submit">Lanjutkan</button>
            </form>
        </section>
    </main>
</body>

</html>
