<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile and business data.
     */
    // Kode fungsi mengambil data profil API
    public function getProfile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('business');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'business' => $user->business ? [
                'id' => $user->business->id,
                'nama_usaha' => $user->business->nama_usaha,
                'logo_url' => $user->business->logo_path ? asset('storage/' . $user->business->logo_path) : null,
            ] : null
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    // Kode fungsi memperbarui profil API
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    // Kode fungsi mengubah password API
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!empty($user->google_id)) {
            return response()->json([
                'message' => 'Akun Google tidak dapat mengubah password.'
            ], 403);
        }

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.'
        ]);
    }
}
