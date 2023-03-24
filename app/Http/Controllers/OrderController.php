<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use LDAP\Result;

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
            'products.*.foc' => ['boolean'],
            'products.*.toppings' => ['array'],
            'products.*.toppings.*' => ['numeric'],
            'note' => ['']
        ]);

        $products = Product::validateAndFresh($data);

        $order = DB::transaction(function () use ($data, $products) {
            $order = Order::query()->create([
                'user_id' => $data['user_id'],
                'note' => $data['note'] ?? ''
            ]);

            $order->syncProductsAndToppings($data, $products);

            return $order->fresh(['products.pivot.toppings']);
        });

        return response()->json(['order' => $order]);
    }

    public function show(Order $order)
    {
        return response()->json(['order' => $order->load(['products.pivot.toppings'])]);
    }

    public function update(Order $order)
    {
        $data = request()->validate([
            'user_id' => ['required', 'exists:users,id'],
            'products' => ['required', 'array'],
            'products.*' => ['required', 'array'],
            'products.*.id' => ['required', 'numeric'],
            'products.*.quantity' => ['required', 'numeric'],
            'products.*.discount' => ['numeric'],
            'products.*.foc' => ['boolean'],
            'products.*.toppings' => ['array'],
            'products.*.toppings.*' => ['numeric'],
            'note' => [''],
            'status' => ['required', 'in:' . implode(',', OrderStatus::all())]
        ]);

        $products = Product::validateAndFresh($data, $order);

        $order->checkStatus($data['status']);

        abort_if(
            in_array($data['status'], [
                OrderStatus::COMPLETED->value,
                OrderStatus::CANCELED->value,
                OrderStatus::PAID->value
            ]) && !request()->user()->hasRole('admin'),
            ResponseStatus::UNAUTHORIZED->value,
            'You are not an admin'
        );

        $order = DB::transaction(function () use ($data, $products, $order) {
            $updateData = ['status' => $data['status']];
            if (array_key_exists('note', $data)) $updateData['note'] = $data['note'];
            $order->update($updateData);

            $order->syncProductsAndToppings($data, $products);

            return $order->fresh(['products.pivot.toppings']);
        });

        return response()->json(['order' => $order]);
    }

    public function index()
    {
        $filters = request()->validate([
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
            'summery' => ['sometimes', 'boolean']
        ]);
        $query = Order::query()->latest('id')->filter($filters);
        $summery = 0;
        if (array_key_exists('summery', $filters)) {
            $summery = $query->with(['products'])->get()->sum(fn ($order) => $order->products->sum(fn ($val) => $val->pivot->price * $val->pivot->quantity));
        }
        return response()->json([
            'data' => $query->paginate(request()->per_page ?? 20),
            'summery' => $summery
        ]);
    }
}
