<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Validation\Rule;

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

    public function store()
    {
        $data = request()->validate([
            'name' => ['required', 'unique:products,name'],
            'price' => ['required', 'numeric'],
            'description' => [''],
            'status' => ['required', Rule::in([ProductStatus::ENABLED->value, ProductStatus::DISABLED->value])]
        ]);

        $product = Product::create($data);

        return response()->json(['product' => $product]);
    }

    public function update(Product $product)
    {
        $data = request()->validate([
            'name' => ['required', Rule::unique('products', 'name')->ignoreModel($product)],
            'price' => ['required', 'numeric'],
            'description' => [''],
            'status' => ['required', Rule::in([ProductStatus::ENABLED->value, ProductStatus::DISABLED->value])]
        ]);

        $product->update($data);

        return response()->json(['product' => $product]);
    }
}
