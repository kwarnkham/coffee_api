<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    public function syncProductsAndToppings(array $data, Collection $products)
    {
        $productsData = array_map(function ($productData) use ($products) {
            $product = $products->first(fn ($val) => $val->id == $productData['id']);
            return [
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $productData['discount'] ?? 0,
                'quantity' => $productData['quantity'],
                'id' => $product->id
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
            ]);
        }

        $orderProducts = $this->fresh(['products'])->products->map(fn ($val) => $val->pivot);

        $orderProducts->each(function ($orderProduct) {
            $product = Product::query()->find($orderProduct->product_id);
        });

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
}
