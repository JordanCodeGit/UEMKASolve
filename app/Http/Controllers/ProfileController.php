<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Business; // [FIX] Gunakan Model Business
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan & profile.
     */
    public function show()
    {
        $user = Auth::user();

        // [FIX] Load relasi 'business' (bukan perusahaan lagi)
        $user->load('business');

        return view('pengaturan', [
            'user' => $user
        ]);
    }

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

    public function updateAkun(Request $request)
    {
        // Bagian ini TIDAK DIUBAH (Logika User sudah benar)
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:32',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ];

        if ($user->password !== null) {
            $rules['current_password'] = 'required_with:password';
        } else {
            $rules['current_password'] = 'nullable';
        }

        $request->validate($rules);

        $user->name = $request->name;

        if ($request->filled('password')) {
            if ($user->password !== null) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Password saat ini salah.']);
                }
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil akun berhasil diperbarui!');
    }
}
