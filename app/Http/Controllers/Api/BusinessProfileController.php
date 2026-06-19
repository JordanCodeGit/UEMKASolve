<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'nama_usaha' => ['required', 'string', 'max:32'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 401);
        }

        if ($user->role !== 'owner') {
            return response()->json([
                'message' => 'Hanya owner yang dapat mengubah profil usaha.',
            ], 403);
        }

        $business = method_exists($user, 'activeBusiness')
            ? $user->activeBusiness()
            : $user->business;

        if (!$business) {
            $business = Business::create([
                'user_id' => $user->id,
                'nama_usaha' => $request->nama_usaha,
            ]);
        }

        $business->nama_usaha = $request->nama_usaha;

        if ($request->hasFile('logo')) {
            if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
                Storage::disk('public')->delete($business->logo_path);
            }

            $business->logo_path = $request->file('logo')->store('logos', 'public');
        }

        $business->save();

        return response()->json([
            'message' => 'Profil usaha berhasil diperbarui.',
            'business' => $this->formatBusiness($business),
        ]);
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
