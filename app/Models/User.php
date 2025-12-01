<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Notifications\VerifyEmailNotification;

/**
 * @property-read Perusahaan|null $perusahaan
 * @property-read Business|null $business
 * @property string $email
 * @property int|null $id_perusahaan
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'id_perusahaan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    public function perusahaan(): BelongsTo
    {
        // User ini 'milik' (belongsTo) satu Perusahaan
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    /**
     * Get the business associated with the user (alias).
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'id_perusahaan');
    }

    /**
     * Send the email verification notification.
     * Override untuk menggunakan custom notification
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }
}
