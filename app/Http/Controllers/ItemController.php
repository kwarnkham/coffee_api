<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function purchase(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'quantity' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'note' => ['']
        ]);
        $item = Item::query()->where('name', $data['name'])->first();
        if (!$item) $item = $item = Item::create([
            'name' => $data['name']
        ]);

        $item->purchases()->create([
            'price' => $data['price'],
            'quantity' => $data['quantity'],
            'note' => $data['note']
        ]);

        return response()->json(['item' => $item->fresh()], ResponseStatus::CREATED->value);
    }

    public function index()
    {
        $filters = request()->validate([
            'search' => ['sometimes', 'required']
        ]);
        $query = Item::query()->latest('id')->filter($filters);
        return response()->json(['data' => $query->paginate(request()->per_page ?? 20)]);
    }

    public function search()
    {
        $filters = request()->validate([
            'search' => ['sometimes', 'required'],
            'limit' => ['sometimes', 'required', 'numeric'],
        ]);
        $query = Item::query()->filter($filters)->with(['latestPurchase']);

        return response()->json(['items' => $query->get()]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required', Rule::unique('items', 'name')->ignoreModel($item)],
        ]);

        $item->update(['name' => $data['name']]);

        return response()->json(['item' => $item->fresh()]);
    }
}
