<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Perusahaan; // Pastikan Model Perusahaan sudah ada

class SetupController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        assert($user !== null);

        // Cek double safety (kalau iseng nembak API padahal udah punya perusahaan)
        if ($user->id_perusahaan) {
            return response()->json(['message' => 'Anda sudah memiliki perusahaan.'], 400);
        }

        // 2. Buat Perusahaan Baru di Database
        $perusahaan = Perusahaan::create([
            'nama_perusahaan' => $request->nama_perusahaan,
            // Tambahkan kolom lain jika ada default value, misal: 'alamat' => '-'
        ]);

        // 3. Sambungkan User ke Perusahaan tersebut
        $user->update([
            'id_perusahaan' => $perusahaan->id
        ]);

        return response()->json([
            'message' => 'Profil usaha berhasil dibuat!',
            'user' => $user
        ], 201);
    }
}
