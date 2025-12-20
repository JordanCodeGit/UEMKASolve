<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $tipe
 * @property string $nama_kategori
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', // <--- TAMBAHKAN BARIS INI
        'nama_kategori',
        'tipe',
        'ikon',
    ];

    // Relasi (Opsional, agar rapi)
    // Kategori milik sebuah Perusahaan
    // Kode fungsi relasi ke perusahaan
    public function perusahaan()
    {
        // belongsTo(ModelTujuannya, 'nama_foreign_key_di_tabel_ini')
        return $this->belongsTo(Perusahaan::class, 'business_id');
    }

    /**
     * Get the transactions for the category.
     */
    // Kode fungsi relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    // Kode fungsi boot model (soft delete cascade)
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            // Hapus semua transaksi yang punya category_id ini
            $category->transactions()->delete();
        });
    }
}
