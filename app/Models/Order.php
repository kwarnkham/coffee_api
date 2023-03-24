<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function checkStatus(int $status)
    {
        abort_if(
            in_array($status, [
                OrderStatus::PAID->value,
                OrderStatus::PENDING->value,
                OrderStatus::CANCELED->value
            ]) && $this->status != OrderStatus::PENDING->value,
            ResponseStatus::BAD_REQUEST->value,
            'Order is not pending'
        );

        abort_if(
            $status == OrderStatus::COMPLETED->value && $this->status != OrderStatus::PAID->value,
            ResponseStatus::BAD_REQUEST->value,
            'Can only complete a paid order'
        );
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->using(OrderProduct::class)
            ->withTimestamps()
            ->withPivot([
                'name', 'price', 'discount', 'quantity', 'id', 'foc'
            ]);
    }

    public function syncProductsAndToppings(array $data, Collection $products)
    {
        $productsData = array_map(function ($productData) use ($products) {
            $product = $products->first(fn ($val) => $val->id == $productData['id']);
            return [
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $productData['discount'] ?? 0,
                'quantity' => $productData['quantity'],
                'id' => $product->id,
                'foc' => $productData['foc'] ?? 0
            ];
        }, $data['products']);

        $this->products->each(function ($product) {
            $product->pivot->toppings()->detach();
        });

        $this->products()->detach();

        foreach ($productsData as $productData) {
            $this->products()->attach($productData['id'], [
                'name' => $productData['name'],
                'price' => $productData['price'],
                'discount' => $productData['discount'],
                'quantity' => $productData['quantity'],
                'foc' => $productData['foc'] ?? 0
            ]);
        }

        $orderProducts = $this->fresh(['products'])->products->map(fn ($val) => $val->pivot);

        foreach ($data['products'] as $productData) {
            if (array_key_exists('toppings', $productData)) {
                $orderProduct = $orderProducts->first(fn ($val) => $val->product_id == $productData['id']);
                $toppings = Topping::query()->whereIn('id', $productData['toppings'])->get();
                $toppingsData = array_map(function ($topping_id) use ($toppings) {
                    $topping = $toppings->first(fn ($val) => $val->id == $topping_id);
                    return [
                        'name' => $topping->name,
                        'price' => $topping->price,
                        'quantity' => 1,
                        'id' => $topping->id
                    ];
                }, $productData['toppings']);
                $orderProduct->toppings()->attach(collect($toppingsData)->mapWithKeys(
                    fn ($val) => [
                        $val['id'] => [
                            'name' => $val['name'],
                            'price' => $val['price'],
                            'quantity' => $val['quantity']
                        ]
                    ]
                )->toArray());
            }
        }
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
