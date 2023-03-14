<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    use HasFactory;

    public function purchases(): MorphMany
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    public function latestPurchase()
    {
        return $this->morphOne(Purchase::class, 'purchasable')
            ->latestOfMany()
            ->where('status', PurchaseStatus::NORMAL->value);
    }
}
