<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOcrService
{
    protected $apiKey;

    // DAFTAR MODEL (PRIORITAS 1 S/D 5)
    protected $models = [
        'gemini-2.5-flash',      // 1. Paling Cepat
        'gemini-2.5-pro',          // 2. Standar Stabil
        'gemini-2.0-flash-exp',   // 3. Alternatif Flash
        'gemini-2.0-flash',       // 4. Versi Lite
        'gemini-2.0-flash-001',         // 5. Versi Klasik (Benteng Terakhir)'
    ];

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

   public function extractTransactionData(UploadedFile $image)
    {
        // 1. Cek API Key
        if (empty($this->apiKey)) {
            throw new \Exception("API Key Gemini belum disetting di file .env");
        }

        // 2. Siapkan Data Gambar
        $imageData = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType();

        // 3. Prompt (UPDATE: Tambah Field Kategori)
        // Kita beri daftar kategori umum agar AI memilih salah satu dari itu
        $prompt = "
            Analisis struk ini. Output JSON murni:
            {
                \"items\": [{\"nama_barang\": \"string\", \"qty\": 1, \"harga_satuan\": 0, \"total\": 0}],
                \"total_transaksi\": 0,
                \"tanggal\": \"YYYY-MM-DD HH:MM\",
                \"nama_toko\": \"Nama Toko\",
                \"kategori\": \"Kategori\"
            }
            Aturan:
            1. total_transaksi integer.
            2. tanggal default hari ini.
            3. Field 'kategori' HARUS memilih salah satu yang paling cocok dari daftar ini:
               [Makanan, Minuman, Transportasi, Belanja, Tagihan, Kesehatan, Pendidikan, Hiburan, Sedekah, Gaji, Bonus, Penjualan, Lainnya].
            4. Jika ragu, pilih 'Lainnya'.
            5. Tanpa markdown.
        ";

        $lastError = 'Unknown error';
        $allLimitReached = false;

        // 4. [AUTO-SWITCH] Loop semua model (Kode sama seperti sebelumnya)
        foreach ($this->models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                $response = Http::withOptions(['verify' => false])
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . $this->apiKey, [
                        'contents' => [[
                            'parts' => [
                                ['text' => $prompt],
                                ['inlineData' => ['mimeType' => $mimeType, 'data' => $imageData]]
                            ]
                        ]]
                    ]);

                if ($response->failed()) {
                    $status = $response->status();
                    $errBody = $response->json();
                    $msg = $errBody['error']['message'] ?? $response->body();
                    Log::warning("Model {$model} GAGAL ({$status}): {$msg}");
                    $lastError = $msg;
                    if ($status === 429) $allLimitReached = true;
                    continue;
                }

                $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $cleanJson = str_replace(['```json', '```'], '', $rawText);
                $data = json_decode(trim($cleanJson), true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Koneksi Error pada {$model}: " . $lastError);
            }
        }

        Log::error("SEMUA MODEL GEMINI GAGAL. Error terakhir: " . $lastError);

        if ($allLimitReached || str_contains(strtolower((string)$lastError), 'quota')) {
            throw new \Exception("Maaf, kuota scan AI harian sudah limit. Silakan input manual.");
        } else {
            throw new \Exception("Gagal Scan: Server AI sedang sibuk. Silakan input manual.");
        }
    }
}
