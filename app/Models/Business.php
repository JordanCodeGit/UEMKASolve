<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Gunakan Model bawaan Laravel
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model // Jangan extends Perusahaan lagi
{
    use HasFactory;

    // Pastikan dia menunjuk ke tabel yang benar
    protected $table = 'businesses';

    // Properti yang tadi kita bahas (Wajib ada)
    protected $fillable = [
        'user_id',
        'nama_usaha', 
        'logo_path',
    ];

    // Kode fungsi relasi ke transaksi
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'business_id');
    }

    // Relasi ke kategori (Tambahkan ini agar lengkap)
    // Kode fungsi relasi ke kategori
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'business_id');
    }
}
