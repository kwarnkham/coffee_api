<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Purchase $purchase) {
            $purchasable = $purchase->purchasable;
            if ($purchasable instanceof Item) {
                $purchasable->stock += $purchase->quantity;
                $purchasable->save();
            }
        });

        static::updated(function (Purchase $purchase) {
            if ($purchase->status == PurchaseStatus::CANCELED->value) {
                $purchasable = $purchase->purchasable;
                if ($purchasable instanceof Item) {
                    $purchasable->stock -= $purchase->quantity;
                    $purchasable->save();
                }
            }
        });
    }

    public function purchasable()
    {
        return $this->morphTo();
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['from'] ?? null,
            fn (Builder $query, $from) => $query->where(function (Builder $query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
        );

        $query->when(
            $filters['to'] ?? null,
            fn (Builder $query, $to) => $query->where(function (Builder $query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            })
        );
    }
}
