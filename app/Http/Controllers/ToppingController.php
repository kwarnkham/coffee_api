<?php

namespace App\Http\Controllers;

use App\Enums\ToppingStatus;
use App\Models\Topping;

class ToppingController extends Controller
{
    public function index()
    {
        return response()->json([
            'toppings' => Topping::query()->where('status', ToppingStatus::ENABLED->value)->get()
        ]);
    }
}
