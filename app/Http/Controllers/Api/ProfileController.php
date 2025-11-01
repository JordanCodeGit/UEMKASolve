<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Mengambil data profil User dan Business yang sedang login.
     * Sesuai diagram "ambilDataProfil".
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Load relasi bisnis (sesuai Class Diagram)
        $user->load('business');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'business' => $user->business // Akan mengembalikan data bisnis atau null
        ], 200);
    }

    /**
     * Memperbarui data profil (User & Business).
     * Sesuai diagram "mengisiDataProfilUsaha" & "memvalidasiDataProfil".
     */
public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        $business = $user->business;
        $validatedData = $request->validated();

        // --- Update Data Teks (Sama seperti sebelumnya) ---
        if (isset($validatedData['name']) || isset($validatedData['email'])) {
            $userData = [];
            if (isset($validatedData['name'])) $userData['name'] = $validatedData['name'];
            if (isset($validatedData['email'])) $userData['email'] = $validatedData['email'];
            $user->update($userData);
        }
        if ($business && isset($validatedData['nama_usaha'])) {
            $business->update([
                'nama_usaha' => $validatedData['nama_usaha'],
            ]);
        }

        // --- [TAMBAHAN] Logic untuk Upload Logo ---
        if ($request->hasFile('logo')) {
            // Pastikan bisnis ada
            if ($business) {
                // Hapus logo lama jika ada
                if ($business->logo_path) {
                    Storage::disk('public')->delete($business->logo_path);
                }

                // Simpan logo baru di 'storage/app/public/logos'
                // Path yang disimpan di DB adalah 'logos/namafile.png'
                $path = $request->file('logo')->store('logos', 'public');

                // Update database
                $business->update(['logo_path' => $path]);
            }
        }

        // Load ulang data yang sudah di-update
        $user->load('business');

        // [MODIFIKASI] Kita perlu accessor untuk URL logo
        // Kita akan tambahkan ini di Model Business (Langkah 4)
        $user->business->makeVisible(['logo_url']); // Panggil accessor

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            // Kembalikan data user DAN business yang sudah di-update
            'user' => $user->only(['id', 'name', 'email']),
            'business' => $user->business,
        ], 200);
    }

    /**
     * Mengubah password user yang sedang login.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        // Validasi (termasuk cek password lama) sudah ditangani oleh ChangePasswordRequest
        $validatedData = $request->validated();

        $user = Auth::user();

        // Update password baru
        $user->update([
            'password' => Hash::make($validatedData['password'])
        ]);

        return response()->json(['message' => 'Password berhasil diubah.'], 200);
    }
}
