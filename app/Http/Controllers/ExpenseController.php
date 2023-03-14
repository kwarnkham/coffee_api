<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function purchase()
    {
        $data = request()->validate([
            'name' => ['required'],
            'amount' => ['required', 'gte:0'],
            'quantity' => ['required', 'gt:0'],
            'note' => ['']
        ]);

        $expense = Expense::query()->where('name', $data['name'])->first();
        if (!$expense) $expense = $expense = Expense::create([
            'name' => $data['name']
        ]);

        $expense->purchases()->create([
            'price' => $data['amount'],
            'quantity' => $data['quantity'],
            'note' => $data['note'] ?? ''
        ]);

        return response()->json(['expense' => $expense->fresh(['latestPurchase'])], ResponseStatus::CREATED->value);
    }

    public function index()
    {
        return response()->json([
            'data' => Expense::query()->with(['latestPurchase'])->paginate(request()->per_page ?? 20)
        ]);
    }
}
