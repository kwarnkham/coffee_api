<?php

namespace App\Models;

use App\Enums\ResponseStatus;
use Arr;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    public static function validateAndFresh($data, Order $order = null): Collection
    {
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

        $products->each(function ($product) use ($data, $order) {
            // if ($order) {
            //     $stock = $product->stock + $order->products()->where('products.id', $product->id)->get()->reduce(fn ($carry, $val) => $carry + $val->pivot->quantity, 0);
            // } else {
            //     $stock = $product->stock;
            // }

            // abort_if(
            //     $stock < array_reduce(
            //         array_filter($data['products'], fn ($val) => $val['id'] == $product->id),
            //         fn ($carry, $val) => $val['quantity'] + $carry,
            //         0
            //     ),
            //     ResponseStatus::BAD_REQUEST->value,
            //     "Quantity cannot be greater than stock($product->name)."
            // );

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

        return $products;
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

        $query->when(
            $filters['status'] ?? null,
            fn (Builder $query, $status) => $query->where(function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
        );
    }
}

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }
}

class EloquentBuilder extends Builder
{
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        if ($name === 'pivot') {
            $relations = array_filter(array_keys($this->eagerLoad), function ($relation) {
                return $relation != 'pivot' && str_contains($relation, 'pivot');
            });

            $pivots = $this->getModel()->newCollection(
                Arr::pluck($models, 'pivot')
            );

            $pivots->load(array_map(function ($relation) {
                return substr($relation, strlen('pivot.'));
            }, $relations));

            return $models;
        }

        return parent::eagerLoadRelation($models, $name, $constraints);
    }
}
