<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business; // Gunakan Model Business (terbaru)

class SetupController extends Controller
{
    // Kode fungsi menyimpan setup perusahaan
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        assert($user !== null);

        // Cek double safety (kalau iseng nembak API padahal udah punya perusahaan)
        // [FIX] Menggunakan relasi business (bukan id_perusahaan)
        if ($user->business) {
            return response()->json(['message' => 'Anda sudah memiliki perusahaan.'], 400);
        }

        // 2. Buat Perusahaan Baru di Database (Menggunakan model Business)
        $business = Business::create([
            'user_id'    => $user->id,
            'nama_usaha' => $request->nama_perusahaan,
        ]);

        return response()->json([
            'message' => 'Profil usaha berhasil dibuat!',
            'user' => $user->load('business')
        ], 201);
    }
}
