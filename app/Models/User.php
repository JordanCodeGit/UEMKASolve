<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne; // Pastikan ini ada
use App\Notifications\VerifyEmailNotification;

/**
 * @property-read Business|null $business
 * @property string $email
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        // 'id_perusahaan', <--- HAPUS INI. Kita tidak pakai kolom ini lagi di tabel users.
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Kode fungsi casting atribut
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the business associated with the user.
     * RELASI: User MEMILIKI satu Business.
     */
    // Kode fungsi relasi ke bisnis
    public function business(): HasOne
    {
        // Parameter kedua ('user_id') adalah Foreign Key yang ada di tabel businesses
        return $this->hasOne(Business::class, 'user_id');
    }

    /**
     * Send the email verification notification.
     */
    // Kode fungsi mengirim notifikasi verifikasi email
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }
}
