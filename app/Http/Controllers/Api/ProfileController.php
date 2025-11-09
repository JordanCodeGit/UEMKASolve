<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Pastikan Storage di-import

class ProfileController extends Controller
{
    /**
     * [FIXED] Mengambil data profil User dan Business yang sedang login.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Load relasi bisnis
        $business = $user->business; 
        
        // Pastikan accessor logo_url dipanggil jika bisnis ada
        if ($business) {
            $business->makeVisible(['logo_url']);
        }

        // Kembalikan JSON yang VALID (hanya satu 'user' key)
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'business' => $business 
        ], 200);
    }

    /**
     * Memperbarui data profil (User & Business) + Logo.
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
            
            // Hanya update jika ada data
            if (!empty($userData)) {
                $user->update($userData);
            }
        }

        // Update data Business (jika ada)
        if ($business && isset($validatedData['nama_usaha'])) {
            $business->update([
                'nama_usaha' => $validatedData['nama_usaha'],
            ]);
        }
        
        // Logic untuk Upload Logo
        if ($request->hasFile('logo')) {
            if ($business) {
                // Hapus logo lama jika ada
                if ($business->logo_path) {
                    Storage::disk('public')->delete($business->logo_path);
                }
                // Simpan logo baru
                $path = $request->file('logo')->store('logos', 'public');
                $business->update(['logo_path' => $path]);
            }
        }
        
        // Load ulang data yang sudah di-update
        $user->load('business');
        if ($user->business) {
             $user->business->makeVisible(['logo_url']);
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user->only(['id', 'name', 'email']), // Kirim data user
            'business' => $user->business, // Kirim data business
        ], 200);
    }

    /**
     * Mengubah password user yang sedang login.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user();
        
        $user->update([
            'password' => Hash::make($validatedData['password'])
        ]);

        return response()->json(['message' => 'Password berhasil diubah.'], 200);
    }
}