<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_usaha',
        'logo_path',
    ];

    /**
     * [BARU] Buat $appends agar 'logo_url' selalu ditambahkan ke JSON.
     * Sembunyikan 'logo_path' asli.
     */
    protected $appends = ['logo_url'];
    protected $hidden = ['logo_path'];

    /**
     * Get the user that owns the business (one-to-one inverse).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for the business.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the transactions for the business.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * [BARU] Accessor untuk mendapatkan URL lengkap logo.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo_path
                            ? Storage::disk('public')->url($this->logo_path)
                            : null, // Kembalikan null jika tidak ada logo
        );
    }
}
