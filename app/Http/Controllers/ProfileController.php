<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan & profile.
     */
    public function show()
    {
        $user = Auth::user();
        $user->load('business');
        return view('pengaturan', ['user' => $user]);
    }

    /**
     * Update Profil Usaha (Logo & Nama)
     */
    public function updateUsaha(Request $request)
    {
        $request->validate([
            'nama_usaha' => 'required|string|max:32',
            'logo'       => 'nullable|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $business = $user->business;

        if (!$business) {
            $business = Business::create([
                'user_id'    => $user->id,
                'nama_usaha' => $request->nama_usaha,
                'saldo'      => 0
            ]);
        }

        $business->nama_usaha = $request->nama_usaha;

        if ($request->hasFile('logo')) {
            if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $business->logo_path = $request->file('logo')->store('logos', 'public');
        }

        $business->save();

        return back()->with('success', 'Profil usaha berhasil diperbarui!')
                     ->with('active_tab', 'usaha');
    }

    /**
     * Update Profil Akun (Nama, Email, Password)
     */
    public function updateAkun(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Input Dasar
        $rules = [
            'name'     => 'required|string|max:32',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ];

        // Jika user bukan login Google (punya password), wajib isi password lama
        if ($user->password !== null) {
            $rules['current_password'] = 'required_with:password';
        } else {
            $rules['current_password'] = 'nullable';
        }

        // Jalankan Validasi
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'akun');
        }

        // 2. Update Nama
        $user->name = $request->name;

        // 3. LOGIKA UBAH PASSWORD (DIPERBAIKI)
        if ($request->filled('password')) {

            // Cek hanya jika user memiliki password lama (bukan user Google)
            if ($user->password !== null) {

                // A. Cek: Apakah Password Lama BENAR?
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()
                        ->withErrors(['current_password' => 'Password saat ini salah.'])
                        ->withInput()
                        ->with('active_tab', 'akun');
                }

                // B. [FIX] Cek: Apakah Password Baru SAMA dengan Password Lama?
                // Logikanya: Jika di-check hasilnya TRUE (sama), maka kita RETURN ERROR.
                if (Hash::check($request->password, $user->password)) {
                    return back()
                        ->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.'])
                        ->withInput()
                        ->with('active_tab', 'akun');
                }
            }

            // Jika lolos, simpan password baru
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()
            ->with('success', 'Profil akun berhasil diperbarui!')
            ->with('active_tab', 'akun');
    }
}
