<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [ 'user_id', 'name', 'logo_path', ];

    // Relasi: Business ini dimiliki oleh satu User
    public function user() {
        return $this->belongsTo(User::class);
    }
}
