<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Dapatkan business_id milik user yang terotentikasi.
     */
    private function getBusinessId(): int
    {
        return Auth::user()->business->id;
    }

    /**
     * Menampilkan daftar kategori milik user.
     * Sesuai Aturan Otorisasi: difilter berdasarkan business_id.
     */
    public function index(Request $request): JsonResponse
    {
        $businessId = $this->getBusinessId();

        $query = Category::where('business_id', $businessId)
                         ->latest('created_at'); // Urutkan dari yg terbaru

        // (Opsional) Tambahkan filter berdasarkan tipe
        if ($request->has('tipe')) {
            $request->validate([
                'tipe' => [Rule::in(['pemasukan', 'pengeluaran'])]
            ]);
            $query->where('tipe', $request->tipe);
        }

        $categories = $query->get(); // Ambil semua kategori (bukan paginate, asumsi list tidak terlalu panjang)

        return response()->json($categories, 200);
    }

    /**
     * Menyimpan kategori baru.
     * Validasi ditangani oleh StoreCategoryRequest.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Tambahkan business_id milik user ke data
        $validatedData['business_id'] = $this->getBusinessId();

        $category = Category::create($validatedData);

        return response()->json($category, 201); // 201 Created
    }

    /**
     * Menampilkan satu kategori spesifik.
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function show(Category $category): JsonResponse
    {
        // Otorisasi: Pastikan kategori ini milik user yang login
        if ($category->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        return response()->json($category, 200);
    }

    /**
     * Memperbarui kategori.
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        // Otorisasi: Pastikan kategori ini milik user yang login
        if ($category->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        $validatedData = $request->validated();

        $category->update($validatedData);

        return response()->json($category, 200);
    }

    /**
     * Menghapus kategori (Soft Delete).
     * Sesuai Aturan Otorisasi: cek kepemilikan.
     */
    public function destroy(Category $category): Response
    {
        // Otorisasi: Pastikan kategori ini milik user yang login
        if ($category->business_id !== $this->getBusinessId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        // Catatan: Anda mungkin ingin menambahkan logika di sini
        // untuk mencegah penghapusan kategori yang masih digunakan oleh transaksi.
        // if ($category->transactions()->exists()) {
        //     return response()->json(['message' => 'Kategori tidak dapat dihapus karena masih memiliki transaksi.'], 409); // 409 Conflict
        // }

        // Lakukan Soft Delete (sesuai Aturan #2)
        $category->delete();

        return response()->noContent(); // 204 No Content
    }
}
