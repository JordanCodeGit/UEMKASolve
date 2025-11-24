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
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Helper: Ambil ID Perusahaan langsung dari kolom 'id_perusahaan' di tabel users.
     */
    private function getCurrentCompanyId()
    {
        $user = Auth::user();

        // [UPDATE] Mengambil value langsung dari kolom 'id_perusahaan' milik user
        if ($user && $user->id_perusahaan) {
            return $user->id_perusahaan;
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $companyId = $this->getCurrentCompanyId();

        // Jika user tidak punya id_perusahaan (belum diset), return kosong
        if (!$companyId) {
            return response()->json([], 200);
        }

        // [MAPPING] id_perusahaan (User) ---> business_id (Category)
        $query = Category::where('business_id', $companyId)
                         ->latest('created_at'); 

        if ($request->has('tipe') && in_array($request->tipe, ['pemasukan', 'pengeluaran'])) {
            $query->where('tipe', $request->tipe);
        }

        return response()->json($query->get(), 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $companyId = $this->getCurrentCompanyId();

        // Cek jika user belum punya perusahaan
        if (!$companyId) {
            return response()->json(['message' => 'Akun Anda belum terhubung dengan perusahaan manapun.'], 400);
        }

        $validatedData = $request->validated();
        
        // [KUNCI] Masukkan ID Perusahaan user ke kolom business_id kategori
        $validatedData['business_id'] = $companyId;

        // [PERBAIKAN] Bersihkan input nama dari tag HTML (strip_tags)
        $validatedData['nama_kategori'] = strip_tags($validatedData['nama_kategori']);

        // Simpan ke Database
        // (Ini yang akan error jika business_id tidak ada di $fillable Model)
        $category = Category::create($validatedData);

        return response()->json($category, 201); 
    }

    public function show(Category $category): JsonResponse
    {
        // Validasi kepemilikan
        if ($category->business_id !== $this->getCurrentCompanyId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        return response()->json($category, 200);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        if ($category->business_id !== $this->getCurrentCompanyId()) {
            return response()->json(['message' => 'Tidak ditemukan.'], 404);
        }

        $category->update($request->validated());

        // [PERBAIKAN] Bersihkan input saat update juga
        if (isset($validatedData['nama_kategori'])) {
            $validatedData['nama_kategori'] = strip_tags($validatedData['nama_kategori']);
        }
        return response()->json($category, 200);
    }

    public function destroy(Category $category): Response
    {
        if ($category->business_id !== $this->getCurrentCompanyId()) {
            return response(['message' => 'Tidak ditemukan.'], 404);
        }

        $category->delete();
        return response()->noContent(); 
    }
}