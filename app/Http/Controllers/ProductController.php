<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;

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
}
