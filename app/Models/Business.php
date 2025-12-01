<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Business model (alias for Perusahaan)
 *
 * @property-read Collection<int, Transaction> $transactions
 */
class Business extends Perusahaan
{
    /**
     * Get all transactions for this business.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'business_id');
    }
}
