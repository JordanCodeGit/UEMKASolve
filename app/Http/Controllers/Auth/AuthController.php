<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest; // Gunakan RegisterRequest
use App\Models\User;
use App\Models\Business;
use Illuminate\Auth\Events\Registered;   // Event untuk verifikasi email
use Illuminate\Support\Facades\DB;       // Untuk database transaction
use Illuminate\Support\Facades\Hash;     // Untuk hashing password
use Illuminate\Http\JsonResponse;        // Untuk type hinting response
// use Illuminate\Support\Facades\Log; // Uncomment jika ingin menggunakan Log

class AuthController extends Controller
{
    /**
     * Handle user registration request.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Gunakan DB Transaction untuk memastikan User & Business dibuat bersamaan atau gagal bersamaan
        DB::beginTransaction();

        try {
            // 1. Ambil data yang sudah divalidasi dari RegisterRequest
            $validatedData = $request->validated();

            // 2. Buat User baru
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // 3. Buat Business baru yang terhubung ke User
            // Gunakan nama default jika 'nama_usaha' tidak ada di validasi
            $businessName = 'Bisnis ' . $validatedData['name'];

            $business = Business::create([
                'user_id' => $user->id,
                'nama_usaha' => $businessName,
                // logo_path akan dihandle terpisah (misal saat edit profil)
            ]);

            // 4. Trigger event 'Registered' bawaan Laravel
            // Ini akan otomatis mengirim email verifikasi jika User model
            // mengimplementasikan MustVerifyEmail
            event(new Registered($user));

            // 5. Commit transaction jika semua langkah berhasil
            DB::commit();

            // 6. Berikan respons sukses
            return response()->json([
                'message' => 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi.',
                // Kembalikan data user dasar (opsional, sesuaikan dengan kebutuhan FE)
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'business_name' => $business->nama_usaha,
                ]
            ], 201); // Kode status 201 Created

        } catch (\Throwable $e) { // Tangkap semua jenis error/exception
            // 7. Rollback transaction jika terjadi error di langkah manapun
            DB::rollBack();

            // 8. Log error (opsional tapi sangat disarankan di production)
            // Log::error('Registrasi gagal: ' . $e->getMessage());

            // 9. Berikan respons error generik ke client
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
                // 'error' => $e->getMessage() // Detail error sebaiknya tidak dikirim ke client di production
            ], 500); // Kode status 500 Internal Server Error
        }
    }

    // --- Method lain (login, forgot password, dll.) akan ditambahkan di sini nanti ---
}
