<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

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

        // Update data User (jika ada)
        if (isset($validatedData['name']) || isset($validatedData['email'])) {
            $userData = [];
            if (isset($validatedData['name'])) $userData['name'] = $validatedData['name'];
            if (isset($validatedData['email'])) $userData['email'] = $validatedData['email'];
            $user->update($userData);
        }

        // Update data Business (jika ada)
        if ($business && isset($validatedData['nama_usaha'])) {
            $business->update([
                'nama_usaha' => $validatedData['nama_usaha'],
            ]);
        }

        // (Opsional) Logic untuk upload logo
        // if ($request->hasFile('logo')) {
        //     $path = $request->file('logo')->store('logos', 'public');
        //     $business->update(['logo_path' => $path]);
        // }

        // Load ulang data yang sudah di-update
        $user->load('business');

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user, // Kirim data user & business yang ter-update
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
