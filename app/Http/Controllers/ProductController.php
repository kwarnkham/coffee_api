<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $filters = request()->validate([
            'search' => ['sometimes', 'required']
        ]);
        $query = Product::query()->filter($filters)->where('status', ProductStatus::ENABLED->value);
        return response()->json(['data' => $query->paginate(request()->per_page ?? 20)]);
    }

    public function stock(Product $product)
    {
        $data = request()->validate([
            'quantity' => ['numeric', 'required', 'gt:0']
        ]);


        $product->update([
            'stock' => $product->stock + $data['quantity']
        ]);

        return response()->json(['product' => $product]);
    }
}
