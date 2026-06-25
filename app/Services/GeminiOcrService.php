<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOcrService
{
    // =========================================================
    // KONFIGURASI MULTI API KEY
    // Tambahkan API Key baru dari Google Project yang BERBEDA
    // di file .env dengan format GEMINI_API_KEY_1, _2, _3, dst.
    // =========================================================
    protected array $apiKeys = [];

    // Model yang dicoba per API Key (urutan prioritas)
    protected array $models = [
        'gemini-2.5-flash',      // PRIORITAS UTAMA: Terbukti aktif & tersedia
        'gemini-2.0-flash',      // Cadangan 1
        'gemini-2.0-flash-lite', // Cadangan 2: Lebih ringan
    ];

    public function __construct()
    {
        // Kumpulkan semua API key yang tersedia dari .env
        // Format: GEMINI_API_KEY_1, GEMINI_API_KEY_2, GEMINI_API_KEY_3, dst.
        // Juga support GEMINI_API_KEY (key lama, sebagai key pertama)
        $keys = [];

        // Support key lama (backward compatible)
        if (!empty(env('GEMINI_API_KEY'))) {
            $keys[] = trim(env('GEMINI_API_KEY'));
        }

        // Tambahkan key bernomor (key 1, 2, 3, ...)
        for ($i = 1; $i <= 10; $i++) {
            $key = env("GEMINI_API_KEY_{$i}");
            if (!empty($key)) {
                $trimmedKey = trim($key);
                // Hindari duplikat
                if (!in_array($trimmedKey, $keys)) {
                    $keys[] = $trimmedKey;
                }
            }
        }

        $this->apiKeys = $keys;
    }

    public function extractTransactionData(UploadedFile $image): array
    {
        // 1. Cek apakah ada minimal 1 API Key
        if (empty($this->apiKeys)) {
            throw new \Exception("API Key Gemini belum disetting di file .env");
        }

        // 2. Siapkan Data Gambar
        $imageData = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType  = $image->getMimeType();

        // 3. Prompt Analisis Struk
        $prompt = "
            Analisis foto struk ini. Output JSON murni:
            {
                \"quality\": {
                    \"is_receipt\": true,
                    \"is_cut_off\": false,
                    \"is_blurry\": false,
                    \"is_dark\": false,
                    \"readable\": true,
                    \"has_multiple_receipts\": false,
                    \"receipt_count\": 1,
                    \"reason\": \"\"
                },
                \"items\": [{\"nama_barang\": \"string\", \"qty\": 1, \"harga_satuan\": 0, \"total\": 0}],
                \"total_transaksi\": 0,
                \"tanggal\": \"YYYY-MM-DD HH:MM atau null\",
                \"nama_toko\": \"Nama Toko\",
                \"kategori\": \"Kategori\"
            }
            Aturan:
            1. Cek kualitas foto lebih dulu.
            2. Jika bukan struk, set quality.is_receipt=false dan quality.readable=false.
            3. Jika dalam satu foto terlihat lebih dari 1 struk/nota terpisah, set quality.has_multiple_receipts=true, quality.receipt_count sesuai jumlah struk yang terlihat, quality.readable=false, dan jangan ekstrak data transaksi.
            4. Jika hanya ada 1 struk, set quality.has_multiple_receipts=false dan quality.receipt_count=1.
            5. Jika struk terpotong, bagian total/tanggal/item penting tidak terlihat, atau tepi struk hilang, set quality.is_cut_off=true dan quality.readable=false.
            6. Jika foto buram/blur sehingga angka atau item tidak aman dibaca, set quality.is_blurry=true dan quality.readable=false.
            7. Jika foto gelap tetapi masih bisa dibaca, set quality.is_dark=true, quality.readable=true, lalu tetap ekstrak datanya.
            8. Jika quality.readable=false, biarkan items kosong, total_transaksi=0, nama_toko kosong, dan isi quality.reason singkat.
            9. total_transaksi integer.
            10. tanggal WAJIB mengikuti tanggal yang tertulis pada struk. Jika tanggal tidak terlihat atau tidak yakin, isi null, jangan mengarang tanggal hari ini.
            11. Field 'kategori' HARUS memilih salah satu yang paling cocok dari daftar ini:
               [Makanan, Minuman, Transportasi, Belanja, Tagihan, Kesehatan, Pendidikan, Hiburan, Sedekah, Gaji, Bonus, Penjualan, Lainnya].
            12. Jika ragu, pilih 'Lainnya'.
            13. Tanpa markdown.
        ";

        $lastError        = 'Unknown error';
        $allKeysExhausted = true;

        // 4. [ROTASI MULTI KEY] Loop setiap API Key
        foreach ($this->apiKeys as $keyIndex => $apiKey) {
            $keyLabel        = 'Key-' . ($keyIndex + 1);
            $keyLimitReached = false;

            Log::info("OCR: Mencoba {$keyLabel}...");

            // Loop setiap model untuk key ini
            foreach ($this->models as $modelIndex => $model) {
                try {
                    // Jeda kecil antar percobaan model (kecuali model pertama)
                    if ($modelIndex > 0) {
                        sleep(1);
                    }

                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                    $response = Http::withOptions(['verify' => false])
                        ->timeout(45)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url . '?key=' . $apiKey, [
                            'contents' => [[
                                'parts' => [
                                    ['text'       => $prompt],
                                    ['inlineData' => ['mimeType' => $mimeType, 'data' => $imageData]],
                                ],
                            ]],
                        ]);

                    if ($response->failed()) {
                        $status  = $response->status();
                        $errBody = $response->json();
                        $msg     = $errBody['error']['message'] ?? $response->body();

                        Log::warning("OCR [{$keyLabel}][{$model}] GAGAL ({$status}): " . substr($msg, 0, 200));
                        $lastError = $msg;

                        if ($status === 429 || $status === 503) {
                            // Quota habis pada key ini — tandai & skip semua model key ini
                            $keyLimitReached = true;
                            break; // Langsung beralih ke key berikutnya
                        }

                        if ($status === 404) {
                            // Model tidak tersedia — coba model berikutnya
                            Log::warning("OCR [{$keyLabel}][{$model}] tidak tersedia (404), skip.");
                            continue;
                        }

                        // Error lain (400, 401, dll) — skip model ini
                        continue;
                    }

                    // Response berhasil — parse JSON
                    $rawText   = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanJson = str_replace(['```json', '```'], '', $rawText);
                    $start     = strpos($cleanJson, '{');
                    $end       = strrpos($cleanJson, '}');

                    if ($start !== false && $end !== false && $end >= $start) {
                        $cleanJson = substr($cleanJson, $start, $end - $start + 1);
                    }

                    $data = json_decode(trim($cleanJson), true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info("OCR: Berhasil dengan {$keyLabel} + model {$model}.");
                        return $data;
                    }

                    Log::warning("OCR [{$keyLabel}][{$model}] JSON tidak valid, coba model berikutnya.");

                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning("OCR [{$keyLabel}][{$model}] Koneksi Error: " . $lastError);
                }
            }

            // Jika key ini gagal bukan karena limit, ada kemungkinan error lain
            if (!$keyLimitReached) {
                $allKeysExhausted = false;
            }

            Log::warning("OCR: {$keyLabel} tidak berhasil, beralih ke key berikutnya...");
        }

        // 5. Semua key & model sudah dicoba, semua gagal
        $totalKeys = count($this->apiKeys);
        Log::error("OCR: SEMUA {$totalKeys} API KEY GAGAL. Error terakhir: " . $lastError);

        $isQuotaError = $allKeysExhausted
            || str_contains(strtolower((string) $lastError), 'quota')
            || str_contains(strtolower((string) $lastError), 'resource_exhausted');

        if ($isQuotaError) {
            $keyInfo = $totalKeys > 1 ? "Semua {$totalKeys} API Key" : "Kuota scan AI";
            throw new \Exception("{$keyInfo} sudah mencapai limit harian. Kuota akan reset tengah malam (sekitar pukul 15.00 WIB). Silakan input manual untuk sementara.");
        }

        throw new \Exception("Gagal Scan: Server AI sedang sibuk. Silakan coba beberapa saat lagi atau input manual.");
    }
}
