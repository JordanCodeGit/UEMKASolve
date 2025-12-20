<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Tampilkan semua kategori milik bisnis user
     * [UPDATED] Mendukung filter ?tipe=pemasukan atau ?tipe=pengeluaran
     */
    // Kode fungsi mengambil daftar kategori
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cek apakah user punya bisnis
        if (!$user->business) {
            return response()->json([], 200);
        }

        // 1. Mulai Query Dasar (Milik bisnis user)
        $query = Category::where('business_id', $user->business->id);

        // 2. [LOGIKA BARU] Cek apakah Frontend meminta tipe tertentu?
        // Jika URL-nya: /api/categories?tipe=pengeluaran
        // Maka kita tambahkan filter WHERE tipe = 'pengeluaran'
        if ($request->has('tipe') && !empty($request->tipe)) {
            $query->where('tipe', $request->tipe);
        }

        // 3. Urutkan dan Eksekusi
        $categories = $query->orderBy('tipe', 'desc') // Pemasukan dulu, baru pengeluaran (jika tidak difilter)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($categories);
    }

    /**
     * Simpan kategori baru
     */
    // Kode fungsi menyimpan kategori baru
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->business) {
            return response()->json(['message' => 'Bisnis tidak ditemukan'], 400);
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'ikon' => 'required|string',
        ]);

        $category = Category::create([
            'business_id' => $user->business->id,
            'nama_kategori' => strip_tags($request->nama_kategori),
            'tipe' => $request->tipe,
            'ikon' => $request->ikon
        ]);

        return response()->json($category, 201);
    }

    /**
     * Update Kategori (Bisa Drag-Drop atau Edit Form)
     */
    // Kode fungsi memperbarui data kategori
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Cari kategori & Pastikan milik bisnis user ini
        $category = Category::where('id', $id)
            ->where('business_id', $user->business->id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan atau akses ditolak'], 404);
        }

        // --- SKENARIO 1: DRAG & DROP (Hanya update tipe) ---
        // Ciri: Request punya 'tipe' tapi TIDAK punya 'nama_kategori'
        if ($request->has('tipe') && !$request->has('nama_kategori')) {
            $request->validate(['tipe' => 'required|in:pemasukan,pengeluaran']);

            // Update tipe
            $category->tipe = $request->tipe;

            // (Opsional) Update folder ikon agar warna ikut berubah
            // Misal: 'pemasukan/Button.png' -> 'pengeluaran/Button.png'
            if ($category->ikon && str_contains($category->ikon, '/')) {
                $filename = basename($category->ikon);
                $category->ikon = $request->tipe . '/' . $filename;
            }

            $category->save();
            return response()->json($category);
        }

        // --- SKENARIO 2: EDIT FORM (Nama & Ikon) ---
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'ikon' => 'nullable|string',
        ]);

        $category->update([
            'nama_kategori' => strip_tags($validated['nama_kategori']),
            'tipe' => $validated['tipe'],
            'ikon' => $validated['ikon'] ?? $category->ikon // Pakai ikon lama jika kosong
        ]);

        return response()->json($category);
    }

    /**
     * Hapus Kategori (Soft Delete)
     */
    // Kode fungsi menghapus kategori
    public function destroy($id)
    {
        $user = Auth::user();

        $category = Category::where('id', $id)
            ->where('business_id', $user->business->id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        // Hapus kategori (Soft Delete)
        // Note: Transaksi terkait tetap aman di database, tapi mungkin perlu logic tambahan
        // di laporan untuk menangani transaksi yang kategorinya null/terhapus.
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus'], 200);
    }
}
