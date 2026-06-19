<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business; // Gunakan Model Business (terbaru)
use Illuminate\Http\Request;

class SetupController extends Controller
{
    // Kode fungsi menyimpan setup perusahaan
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_usaha' => ['nullable', 'string', 'max:255'],
            'nama_perusahaan' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 401);
        }

        // Cek double safety (kalau iseng nembak API padahal udah punya perusahaan)
        // [FIX] Menggunakan relasi business (bukan id_perusahaan)
        if ($user->business) {
            return response()->json(['message' => 'Anda sudah memiliki perusahaan.'], 400);
        }

        $namaUsaha = $request->input('nama_usaha')
            ?: $request->input('nama_perusahaan');

        if (!$namaUsaha) {
            return response()->json([
                'message' => 'Nama usaha wajib diisi.',
            ], 422);
        }

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // 2. Buat Perusahaan Baru di Database (Menggunakan model Business)
        $business = Business::create([
            'user_id'    => $user->id,
            'nama_usaha' => $namaUsaha,
            'logo_path' => $logoPath,
        ]);

        return response()->json([
            'message' => 'Profil usaha berhasil dibuat!',
            'business' => $this->formatBusiness($business),
            'user' => $user->fresh()->load('business'),
        ], 201);
    }

    private function formatBusiness(Business $business): array
    {
        $logoPath = $business->logo_path;
        $logoUrl = $logoPath
            ? request()->getSchemeAndHttpHost() . '/storage/' . ltrim($logoPath, '/')
            : null;

        return [
            'id' => $business->id,
            'nama_usaha' => $business->nama_usaha,
            'logo' => $logoPath,
            'logo_path' => $logoPath,
            'logo_url' => $logoUrl,
        ];
    }
}
