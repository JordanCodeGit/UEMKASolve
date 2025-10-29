<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Pastikan ini di-use
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Untuk Sanctum
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne

class User extends Authenticatable implements MustVerifyEmail // Implementasikan MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens; // Tambahkan HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the business associated with the user (one-to-one).
     */
    public function business(): HasOne
    {
        return $this->hasOne(Business::class);
    }
}
