<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {

        $filters = request()->validate([
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
            'summery' => ['sometimes', 'boolean']
        ]);
        if (array_key_exists('summery', $filters)) {
        }
        $query = Purchase::query()->latest('id')->filter($filters)->with(['purchasable']);
        $summery = 0;
        if (array_key_exists('summery', $filters)) {
            $summery = $query->where('status', PurchaseStatus::NORMAL->value)->sum('price');
        }
        return response()->json([
            'data' => $query->paginate(request()->per_page ?? 20),
            'summery' => $summery
        ]);
    }
}
