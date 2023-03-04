<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:items,name']
        ]);

        $item = Item::create($data);

        return response()->json(['item' => $item], ResponseStatus::CREATED->value);
    }
}
