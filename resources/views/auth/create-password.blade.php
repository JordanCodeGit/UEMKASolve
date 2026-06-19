@extends('layouts.auth')

@section('title', 'Buat Password')

@section('content')
    <div class="auth-form">
        <h2>Buat Password</h2>

        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
            @php
                $roleLabels = collect(explode(',', $member->role))
                    ->map(fn ($role) => trim($role) === 'sekretaris' ? 'Sekretaris' : (trim($role) === 'bendahara' ? 'Bendahara' : ucfirst(trim($role))))
                    ->filter()
                    ->values()
                    ->implode(' dan ');
            @endphp
            Anda diundang sebagai {{ $roleLabels }} untuk bergabung ke {{ $member->business->nama_usaha }}.
        </p>

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px; font-size: 14px; background-color: #fef2f2; padding: 10px; border-radius: 6px; border: 1px solid #fecaca;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('members.password.store', $token) }}" method="POST">
            @csrf

            <div class="form-group">
                <input type="email" value="{{ $email }}" disabled
                    style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; background: #f8fafc; color: #64748b;">
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Password Baru" required>
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Password" required>
                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 8px; font-weight: 600;">
                    Buat Password
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.password-toggle-icon').forEach(icon => {
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    const input = this.previousElementSibling;

                    if (!input || input.tagName !== 'INPUT') return;

                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
@endpush
