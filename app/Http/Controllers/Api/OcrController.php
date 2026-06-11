<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiOcrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class OcrController extends Controller
{
    private const ALLOWED_IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    private const ALLOWED_IMAGE_MIME_TYPES = ['image/png', 'image/jpeg'];
    private const UNSUPPORTED_FORMAT_MESSAGE = 'format yang anda upload tidak didukung';
    private const IMAGE_ONLY_MESSAGE = 'format yang diterima hanya format gambar (png, jpeg, jpg, dll)';

    protected $ocrService;

    public function __construct(GeminiOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'file', 'max:4096'],
        ], [
            'image.required' => 'Silakan pilih foto struk terlebih dahulu.',
            'image.file' => 'File yang diunggah tidak valid.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('image'),
            ], 422);
        }

        $file = $request->file('image');

        if (!$this->hasSupportedImageExtension($file)) {
            return response()->json([
                'message' => self::UNSUPPORTED_FORMAT_MESSAGE,
            ], 422);
        }

        if (!$this->isReadableImage($file)) {
            return response()->json([
                'message' => self::IMAGE_ONLY_MESSAGE,
            ], 422);
        }

        try {
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

    private function hasSupportedImageExtension(?UploadedFile $file): bool
    {
        if (!$file) {
            return false;
        }

        return in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_IMAGE_EXTENSIONS, true);
    }

    private function isReadableImage(UploadedFile $file): bool
    {
        $path = $file->getRealPath();

        if (!$path) {
            return false;
        }

        $imageInfo = @getimagesize($path);

        if ($imageInfo === false) {
            return false;
        }

        return in_array(strtolower($imageInfo['mime'] ?? ''), self::ALLOWED_IMAGE_MIME_TYPES, true);
    }
}
