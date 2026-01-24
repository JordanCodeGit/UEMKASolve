<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiOcrService;

class OcrController extends Controller
{
    protected $ocrService;

    public function __construct(GeminiOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096', // Max 4MB
        ]);

        try {
            $file = $request->file('image');

            // Panggil Service Manual tadi
            $data = $this->ocrService->extractTransactionData($file);

            if (!$data) {
                return response()->json(['message' => 'AI tidak menemukan data struk yang valid.'], 422);
            }

            return response()->json([
                'message' => 'Scan Berhasil',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal Scan: ' . $e->getMessage()
            ], 500);
        }
    }
}
