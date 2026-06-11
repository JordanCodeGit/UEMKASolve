<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiOcrService;
use Illuminate\Support\Facades\Validator;

class OcrController extends Controller
{
    protected $ocrService;

    public function __construct(GeminiOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
        ], [
            'image.required' => 'Silakan pilih foto struk terlebih dahulu.',
            'image.file' => 'File yang diunggah tidak valid.',
            'image.mimes' => 'Format yang dapat kami terima hanya PNG, JPG, atau JPEG.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('image'),
            ], 422);
        }

        try {
            $file = $request->file('image');

            // Panggil Service Manual tadi
            $data = $this->ocrService->extractTransactionData($file);

            if (!$data) {
                return response()->json([
                    'message' => 'AI tidak menemukan data struk yang valid. Pastikan foto struk tidak terpotong atau buram, lalu upload ulang.',
                ], 422);
            }

            $quality = is_array($data['quality'] ?? null) ? $data['quality'] : [];
            $isReceipt = $this->qualityFlag($quality, 'is_receipt', true);
            $isCutOff = $this->qualityFlag($quality, 'is_cut_off', false);
            $isBlurry = $this->qualityFlag($quality, 'is_blurry', false);
            $isReadable = $this->qualityFlag($quality, 'readable', true);
            $isDark = $this->qualityFlag($quality, 'is_dark', false);

            if (!$isReceipt) {
                return response()->json([
                    'message' => 'File yang diunggah belum terdeteksi sebagai struk. Mohon upload foto struk transaksi yang jelas.',
                ], 422);
            }

            if ($isCutOff || $isBlurry || !$isReadable) {
                return response()->json([
                    'message' => 'Struk terlihat terpotong atau buram sehingga tidak bisa dibaca dengan aman. Mohon upload ulang foto struk yang utuh, jelas, dan tidak blur.',
                ], 422);
            }

            if (!$this->hasTransactionData($data)) {
                return response()->json([
                    'message' => 'AI tidak menemukan data struk yang valid. Pastikan foto struk tidak terpotong atau buram, lalu upload ulang.',
                ], 422);
            }

            $response = [
                'message' => 'Scan Berhasil',
                'data' => $data,
            ];

            if ($isDark) {
                $response['warning'] = 'Struk berhasil dibaca, namun foto terlihat gelap. Untuk hasil lebih akurat, upload struk dengan kondisi cahaya cukup terang.';
            }

            return response()->json($response);

        } catch (\Throwable $e) {
            $message = str_contains($e->getMessage(), 'API Key')
                ? 'Layanan OCR belum siap. Hubungi admin untuk mengecek konfigurasi AI.'
                : $e->getMessage();

            return response()->json([
                'message' => $message,
            ], 503);
        }
    }

    private function qualityFlag(array $quality, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $quality)) {
            return $default;
        }

        return filter_var($quality[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function hasTransactionData(array $data): bool
    {
        return !empty($data['total_transaksi'])
            || !empty($data['items'])
            || !empty($data['nama_toko']);
    }
}
