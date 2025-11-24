<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan; // <-- Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import
use Illuminate\Support\Facades\Storage; // <-- Import

class CompanySetupController extends Controller
{
    /**
     * Simpan informasi perusahaan baru dan tautkan ke user.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        $logoPath = null;

        // 2. Handle Upload Logo (jika ada)
        if ($request->hasFile('logo')) {
            // Simpan file di 'storage/app/public/logos'
            // Pastikan Anda sudah menjalankan "php artisan storage:link"
            $logoPath = $request->file('logo')->store('public/logos');

            // Ubah path agar bisa diakses dari web
            $logoPath = Storage::url($logoPath); 
        }

        // 3. Buat Perusahaan Baru
        $perusahaan = Perusahaan::create([
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'logo' => $logoPath, // Simpan path-nya
        ]);

        // 4. TAUTKAN Perusahaan ke User yang sedang login
        $user = Auth::user();
        $user->id_perusahaan = $perusahaan->id;
        $user->save();

        // 5. Kembalikan ke Dashboard
        // Halaman akan refresh, dan popup akan hilang
        return redirect('/dashboard');
    }
}