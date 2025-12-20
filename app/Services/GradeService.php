<?php

namespace App\Services;

class GradeService
{
    // Kode fungsi menghitung grade berdasarkan skor
    public function calculateGrade(int $score)
    {
        if ($score < 0 || $score > 100) {
            return 'INVALID';
        }
        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 70) {
            return 'B';
        }
        return 'C';
    }
}
