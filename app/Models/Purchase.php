<?php

namespace App\Models;

use App\Enums\PurchaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Purchase $purchase) {
            $purchasable = $purchase->purchasable;
            $purchasable->stock += $purchase->quantity;
            $purchasable->save();
        });

        static::updated(function (Purchase $purchase) {
            if ($purchase->status == PurchaseType::CANCELED->value) {
                $purchasable = $purchase->purchasable;
                $purchasable->stock -= $purchase->quantity;
                $purchasable->save();
            }
        });
    }

    public function purchasable()
    {
        return $this->morphTo();
    }
}
