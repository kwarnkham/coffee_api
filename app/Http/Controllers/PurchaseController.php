<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Purchase::query()->latest('id')->with(['purchasable'])->paginate(request()->per_page ?? 20)
        ]);
    }
}
