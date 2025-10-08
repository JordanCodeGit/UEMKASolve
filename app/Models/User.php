<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [ 'name', 'email', 'password', ];
    protected $hidden = [ 'password', 'remember_token', ];

    // Relasi: Satu User memiliki satu Business
    public function business() {
        return $this->hasOne(Business::class);
    }
    // Relasi: Satu User memiliki banyak Category
    public function categories() {
        return $this->hasMany(Category::class);
    }
    // Relasi: Satu User memiliki banyak Transaction
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
