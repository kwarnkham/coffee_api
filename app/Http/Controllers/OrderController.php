<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
        return response()->json(['data' => Order::query()->paginate(request()->per_page ?? 20)]);
    }
}
