<?php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth; // <-- Import Auth
    use Illuminate\Support\Facades\Storage;
    use App\Models\Perusahaan;
    use Illuminate\Support\Facades\Hash;

    class ProfileController extends Controller
    {
        /**
         * Menampilkan halaman pengaturan & profile.
         */
        public function show()
        {
            // 1. Ambil user yang sedang login
            $user = Auth::user();

            // 2. Ambil juga data perusahaannya (via relasi)
            $user->load('perusahaan'); 

            return view('pengaturan', [
                'user' => $user 
            ]);
        }

        public function updateUsaha(Request $request)
        {
            $request->validate([
                'nama_perusahaan' => 'required|string|max:32',
                'logo' => 'nullable|image|max:2048',
            ]);

            $user = Auth::user();
            
            // Cek apakah user sudah punya perusahaan atau belum
            if ($user->perusahaan) {
                $perusahaan = $user->perusahaan;
            } else {
                // Jika belum (kasus langka), buat baru
                $perusahaan = new Perusahaan();
            }

            // 1. Update Nama
            $perusahaan->nama_perusahaan = $request->nama_perusahaan;

            // 2. Update Logo (Jika ada upload baru)
            if ($request->hasFile('logo')) {
                // Hapus logo lama jika ada (opsional, biar hemat storage)
                if ($perusahaan->logo && Storage::exists(str_replace('/storage', 'public', $perusahaan->logo))) {
                    Storage::delete(str_replace('/storage', 'public', $perusahaan->logo));
                }
                
                // Simpan logo baru
                $path = $request->file('logo')->store('public/logos');
                $perusahaan->logo = Storage::url($path);
            }

            $perusahaan->save();

            // Tautkan user ke perusahaan jika belum
            if (!$user->id_perusahaan) {
                $user->id_perusahaan = $perusahaan->id;
                $user->save();
            }

            return back()->with('success', 'Profil usaha berhasil diperbarui!');
        }
        public function updateAkun(Request $request)
        {
            $user = Auth::user();

            // 1. Tentukan Aturan Validasi Dasar
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|min:8|confirmed',
            ];

            // 2. Logika Kondisional:
            // Jika user SUDAH PUNYA password (bukan login Google murni),
            // maka dia WAJIB mengisi current_password jika ingin ganti password.
            if ($user->password !== null) {
                $rules['current_password'] = 'required_with:password';
            } else {
                // Jika password NULL (User Google), current_password boleh kosong
                $rules['current_password'] = 'nullable';
            }

            // Jalankan Validasi
            $request->validate($rules);

            // 3. Update Nama & Email
            $user->name = $request->name;
            $user->email = $request->email;

            // 4. Update Password
            if ($request->filled('password')) {
                
                // Cek: Apakah user punya password lama?
                if ($user->password !== null) {
                    // JIKA ADA, kita harus cek kecocokannya
                    if (!Hash::check($request->current_password, $user->password)) {
                        return back()->withErrors(['current_password' => 'Password saat ini salah.']);
                    }
                }
                // JIKA TIDAK ADA (User Google), lewati pengecekan Hash::check,
                // langsung izinkan dia membuat password baru.

                // Set password baru
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return back()->with('success', 'Profil akun berhasil diperbarui!');
        }
    }

