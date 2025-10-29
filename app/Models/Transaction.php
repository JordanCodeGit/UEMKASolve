<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class Transaction extends Model
{
    use HasFactory, SoftDeletes; // Gunakan SoftDeletes

    protected $fillable = [
        'business_id',
        'category_id',
        'jumlah',
        'catatan',
        'tanggal_transaksi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2', // Pastikan jumlah di-cast sebagai decimal
            'tanggal_transaksi' => 'date', // Cast tanggal
        ];
    }

    /**
     * Get the business that owns the transaction.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the category associated with the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
