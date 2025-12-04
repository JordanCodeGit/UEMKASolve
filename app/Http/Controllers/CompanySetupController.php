<?php

namespace App\Http\Controllers;

use App\Models\Business; // [FIX] Gunakan Model Business, BUKAN Perusahaan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanySetupController extends Controller
{
    /**
     * Simpan informasi bisnis baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        // (Kita biarkan nama inputnya 'nama_perusahaan' jika view Anda masih pakai nama itu)
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255', // Input dari Form
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek double entry (jaga-jaga)
        if ($user->business) {
             return redirect()->route('dashboard');
        }

        $logoPath = null;

        // 2. Handle Upload Logo
        if ($request->hasFile('logo')) {
            // Simpan ke storage/app/public/logos
            $path = $request->file('logo')->store('logos', 'public');

            // Simpan path relatifnya saja (misal: logos/abc.jpg)
            // Nanti di view dipanggil pakai asset('storage/' . $business->logo_path)
            $logoPath = $path;
        }

        // 3. Buat Bisnis Baru (Target ke Tabel 'businesses')
        Business::create([
            'user_id' => $user->id, // [PENTING] Relasi HasOne
            'nama_usaha' => $validated['nama_perusahaan'], // Mapping: Input Form -> Kolom DB
            'logo_path' => $logoPath,
            'saldo' => 0 // Set saldo awal 0
        ]);

        // [CATATAN]: Kita TIDAK PERLU update $user->id_perusahaan lagi.
        // Relasi sudah otomatis terbentuk via user_id di tabel businesses.

        // 4. Kembalikan ke Dashboard
        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat!');
    }
}
