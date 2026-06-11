<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiOcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OcrScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ocr_rejects_non_png_jpg_or_jpeg_files(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'bendahara']));

        $this->postJson('/api/ocr/scan', [
            'image' => UploadedFile::fake()->create('struk.pdf', 12, 'application/pdf'),
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Format yang dapat kami terima hanya PNG, JPG, atau JPEG.');
    }

    public function test_ocr_rejects_cut_or_blurry_receipts(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'bendahara']));

        $this->mockOcrService([
            'quality' => [
                'is_receipt' => true,
                'is_cut_off' => true,
                'is_blurry' => true,
                'is_dark' => false,
                'readable' => false,
                'reason' => 'Bagian total tidak terlihat.',
            ],
            'items' => [],
            'total_transaksi' => 0,
            'tanggal' => '',
            'nama_toko' => '',
            'kategori' => 'Lainnya',
        ]);

        $this->postJson('/api/ocr/scan', [
            'image' => UploadedFile::fake()->image('struk.jpg', 800, 1200),
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Struk terlihat terpotong atau buram sehingga tidak bisa dibaca dengan aman. Mohon upload ulang foto struk yang utuh, jelas, dan tidak blur.');
    }

    public function test_ocr_accepts_dark_but_readable_receipts_with_warning(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'bendahara']));

        $this->mockOcrService([
            'quality' => [
                'is_receipt' => true,
                'is_cut_off' => false,
                'is_blurry' => false,
                'is_dark' => true,
                'readable' => true,
                'reason' => 'Foto gelap tetapi teks masih terbaca.',
            ],
            'items' => [
                ['nama_barang' => 'Pulpen', 'qty' => 1, 'harga_satuan' => 5000, 'total' => 5000],
            ],
            'total_transaksi' => 5000,
            'tanggal' => '2026-06-11 18:30',
            'nama_toko' => 'Toko ATK',
            'kategori' => 'Belanja',
        ]);

        $this->postJson('/api/ocr/scan', [
            'image' => UploadedFile::fake()->image('struk.png', 800, 1200),
        ])
            ->assertOk()
            ->assertJsonPath('warning', 'Struk berhasil dibaca, namun foto terlihat gelap. Untuk hasil lebih akurat, upload struk dengan kondisi cahaya cukup terang.')
            ->assertJsonPath('data.total_transaksi', 5000);
    }

    private function mockOcrService(array $response): void
    {
        $this->app->instance(GeminiOcrService::class, new class($response) extends GeminiOcrService {
            public function __construct(private array $response)
            {
                //
            }

            public function extractTransactionData(UploadedFile $image)
            {
                return $this->response;
            }
        });
    }
}
