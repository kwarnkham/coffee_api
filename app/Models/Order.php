<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class)->using(OrderProduct::class)
            ->withTimestamps()
            ->withPivot([
                'name', 'price', 'discount', 'quantity', 'id'
            ]);
    }
}
