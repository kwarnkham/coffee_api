<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Topping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function store()
    {
        $data = request()->validate([
            'user_id' => ['required', 'exists:users,id'],
            'products' => ['required', 'array'],
            'products.*' => ['required', 'array'],
            'products.*.id' => ['required', 'numeric'],
            'products.*.quantity' => ['required', 'numeric'],
            'products.*.discount' => ['numeric'],
            'products.*.toppings' => ['array'],
            'products.*.toppings.*' => ['numeric'],
        ]);

        $products = Product::query()
            ->whereIn('id', array_map(
                fn ($val) => $val['id'],
                $data['products']
            ))->get();

        abort_if(
            $products->count() != count(array_unique(
                array_map(fn ($val) => $val['id'], $data['products'])
            )),
            ResponseStatus::BAD_REQUEST->value,
            'Products are not valid'
        );

        $products->each(function ($product) use ($data) {
            abort_if(
                $product->stock < array_reduce(
                    array_filter($data['products'], fn ($val) => $val['id'] == $product->id),
                    fn ($carry, $val) => $val['quantity'] + $carry,
                    0
                ),
                ResponseStatus::BAD_REQUEST->value,
                "Quantity cannot be greater than stock($product->name)."
            );

            abort_if(
                $product->price < (array_filter($data['products'], fn ($val) => $val['id'] == $product->id)[0]['discount'] ?? 0),
                ResponseStatus::BAD_REQUEST->value,
                "Discount is not ok($product->name , $product->price)"
            );
        });

        foreach ($data['products'] as $dataProduct) {
            if (array_key_exists('toppings', $dataProduct)) {
                $toppings = Topping::query()->whereIn('id', $dataProduct['toppings'])->get();
                abort_if(
                    $toppings->count() != count($dataProduct['toppings']),
                    ResponseStatus::BAD_REQUEST->value,
                    "Toppings are not valid. " . $dataProduct['id']
                );
            }
        }
        $order = DB::transaction(function () use ($data, $products) {
            $order = Order::query()->create([
                'user_id' => $data['user_id']
            ]);

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

            foreach ($productsData as $productData) {
                $order->products()->attach($productData['id'], [
                    'name' => $productData['name'],
                    'price' => $productData['price'],
                    'discount' => $productData['discount'],
                    'quantity' => $productData['quantity'],
                ]);
            }

            $orderProducts = $order->products->map(fn ($val) => $val->pivot);
            $orderProducts->each(function ($orderProduct) {
                $product = Product::query()->find($orderProduct->product_id);
                $product->update(['stock' => $product->stock - $orderProduct->quantity]);
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

            return $order->fresh(['products.pivot.toppings']);
        });

        return response()->json(['order' => $order]);
    }
}
