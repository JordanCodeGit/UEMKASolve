<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [ 'user_id', 'category_id', 'amount', 'type', 'notes', 'transaction_date', ];

    // Relasi: Transaction ini dimiliki oleh satu User
    public function user() {
        return $this->belongsTo(User::class);
    }
    // Relasi: Transaction ini termasuk dalam satu Category
    public function category() {
        return $this->belongsTo(Category::class);
    }
}
