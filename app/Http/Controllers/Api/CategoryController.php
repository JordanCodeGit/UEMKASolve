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
     * Helper: Ambil ID Bisnis dari relasi User -> Business
     * [PERBAIKAN LOGIKA]
     */
    private function getCurrentCompanyId()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah user punya relasi 'business'
        // Kita akses property ->business (ini memanggil fungsi business() di User model)
        if ($user && $user->business) {
            return $user->business->id; // Ambil ID dari tabel businesses
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $companyId = $this->getCurrentCompanyId();

        if (!$companyId) {
            // Return array kosong jika belum punya bisnis (biar frontend tidak error)
            return response()->json([], 200);
        }

        $query = Category::where('business_id', $companyId)
            ->orderBy('nama_kategori', 'asc');

        if ($request->has('tipe') && in_array($request->tipe, ['pemasukan', 'pengeluaran'])) {
            $query->where('tipe', $request->tipe);
        }

        return response()->json($query->get(), 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $companyId = $this->getCurrentCompanyId();

        if (!$companyId) {
            return response()->json([
                'message' => 'Akun Anda belum memiliki Bisnis. Silakan buat profil bisnis terlebih dahulu.'
            ], 400);
        }

        $validatedData = $request->validated();

        // Set business_id otomatis dari sistem (bukan dari input user)
        $validatedData['business_id'] = $companyId;

        // Hapus tag HTML jahat (Sanitization)
        $validatedData['nama_kategori'] = strip_tags($validatedData['nama_kategori']);

        $category = Category::create($validatedData);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
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

        $validatedData = $request->validated();

        // Sanitization saat update
        if (isset($validatedData['nama_kategori'])) {
             $validatedData['nama_kategori'] = strip_tags($validatedData['nama_kategori']);
        }

        $category->update($validatedData);

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
