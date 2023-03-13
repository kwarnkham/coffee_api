<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Item extends Model
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

    public function consumes()
    {
        return $this->hasMany(Consume::class);
    }

    public function latestConsume()
    {
        return $this->hasOne(Consume::class)
            ->latestOfMany();
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['search'] ?? null,
            fn (Builder $query, $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
        );

        $query->when(
            $filters['limit'] ?? null,
            fn (Builder $query, $limit) => $query->take($limit)
        );
    }
}
