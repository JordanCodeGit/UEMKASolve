<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan & profile.
     */
    // Kode fungsi menampilkan profil
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // [FIX] Load relasi 'business' (bukan perusahaan lagi)
        $user->load('business');

        return view('pengaturan', [
            'user' => $user
        ]);
    }

    // Kode fungsi memperbarui data usaha
    public function updateUsaha(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_usaha' => 'required|string|max:32', // Sesuaikan dengan view baru
            'logo' => 'nullable|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah user sudah punya bisnis
        // Karena sistem baru mewajibkan popup, seharusnya user->business sudah ada.
        $business = $user->business;

        if (!$business) {
            // Fallback jika data hilang (sangat jarang terjadi jika popup jalan)
            $business = Business::create([
                'user_id' => $user->id,
                'nama_usaha' => $request->nama_usaha,
                'saldo' => 0
            ]);
        }

        // 1. Update Nama Usaha
        $business->nama_usaha = $request->nama_usaha;

        // 2. Update Logo (Jika ada upload baru)
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
                Storage::disk('public')->delete($business->logo_path);
            }

            // Simpan logo baru ke folder 'logos' di disk public
            $path = $request->file('logo')->store('logos', 'public');
            $business->logo_path = $path;
        }

        $business->save();

        return back()->with('success', 'Profil usaha berhasil diperbarui!');
    }

    // Kode fungsi memperbarui data akun
    public function updateAkun(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validasi Data Dasar
        $rules = [
            'name'  => ['required', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ];

        // 2. LOGIKA PASSWORD
        if ($request->filled('password')) {

            // Akun yang login via Google tidak boleh mengubah/membuat password dari halaman ini
            if (!empty($user->google_id)) {
                throw ValidationException::withMessages([
                    'password' => 'Akun Google tidak dapat mengubah password.',
                ]);
            }

            // Rule dasar password baru
            $rules['password'] = ['confirmed', \Illuminate\Validation\Rules\Password::defaults()];

            // Validasi Password Lama (Hanya jika user sudah punya password)
            if ($user->password !== null) {
                $rules['current_password'] = ['required', 'current_password'];
            }
        }

        // Jalankan Validasi Dasar Dulu
        $validated = $request->validate($rules);

        // --- [BARU] CEK APAKAH PASSWORD BARU == PASSWORD LAMA? ---
        if ($request->filled('password') && $user->password !== null) {
            // Kita cek: Jika password inputan COCOK dengan password di database
            if (Hash::check($request->password, $user->password)) {
                // Lempar Error Validasi
                throw ValidationException::withMessages([
                    'password' => 'Password baru tidak boleh sama dengan password lama.',
                ]);
            }
        }
        // ---------------------------------------------------------

        // 4. Update Data User
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // 5. Update Password
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()
            ->with('success', 'Profil akun berhasil diperbarui!')
            ->with('active_tab', 'akun');
    }
}
