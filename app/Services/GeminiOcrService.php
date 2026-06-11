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

        // 3. Prompt (UPDATE: Tambah Field Kualitas Gambar)
        // Kita beri daftar kategori umum agar AI memilih salah satu dari itu
        $prompt = "
            Analisis foto struk ini. Output JSON murni:
            {
                \"quality\": {
                    \"is_receipt\": true,
                    \"is_cut_off\": false,
                    \"is_blurry\": false,
                    \"is_dark\": false,
                    \"readable\": true,
                    \"reason\": \"\"
                },
                \"items\": [{\"nama_barang\": \"string\", \"qty\": 1, \"harga_satuan\": 0, \"total\": 0}],
                \"total_transaksi\": 0,
                \"tanggal\": \"YYYY-MM-DD HH:MM\",
                \"nama_toko\": \"Nama Toko\",
                \"kategori\": \"Kategori\"
            }
            Aturan:
            1. Cek kualitas foto lebih dulu.
            2. Jika bukan struk, set quality.is_receipt=false dan quality.readable=false.
            3. Jika struk terpotong, bagian total/tanggal/item penting tidak terlihat, atau tepi struk hilang, set quality.is_cut_off=true dan quality.readable=false.
            4. Jika foto buram/blur sehingga angka atau item tidak aman dibaca, set quality.is_blurry=true dan quality.readable=false.
            5. Jika foto gelap tetapi masih bisa dibaca, set quality.is_dark=true, quality.readable=true, lalu tetap ekstrak datanya.
            6. Jika quality.readable=false, biarkan items kosong, total_transaksi=0, nama_toko kosong, dan isi quality.reason singkat.
            7. total_transaksi integer.
            8. tanggal default hari ini.
            9. Field 'kategori' HARUS memilih salah satu yang paling cocok dari daftar ini:
               [Makanan, Minuman, Transportasi, Belanja, Tagihan, Kesehatan, Pendidikan, Hiburan, Sedekah, Gaji, Bonus, Penjualan, Lainnya].
            10. Jika ragu, pilih 'Lainnya'.
            11. Tanpa markdown.
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
                $start = strpos($cleanJson, '{');
                $end = strrpos($cleanJson, '}');

                if ($start !== false && $end !== false && $end >= $start) {
                    $cleanJson = substr($cleanJson, $start, $end - $start + 1);
                }

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
