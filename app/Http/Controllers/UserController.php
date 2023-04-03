<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store()
    {
        $data = request()->validate([
            'name' => ['required', 'unique:users,name'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'exists:roles,id']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'password' => bcrypt('password')
        ]);

        $user->roles()->attach($data['roles']);

        return response()->json(['user' => $user->load(['roles'])]);
    }

    public function storeCustomer()
    {
        $data = request()->validate([
            'name' => ['required', 'unique:users,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'password' => bcrypt('password')
        ]);

        $user->roles()->attach(Role::where('name', 'customer')->first());

        return response()->json(['user' => $user]);
    }

    public function addCup(User $user)
    {
        $data = request()->validate([
            'quantity' => ['required', 'numeric']
        ]);

        $user->cup += $data['quantity'];
        $user->save();

        return response()->json(['user' => $user]);
    }

    public function redeem(User $user)
    {
        abort_if($user->cup < (($user->redemption * 10) + 10), ResponseStatus::BAD_REQUEST->value, 'No enough cups');
        $user->redemption += 1;
        $user->save();

        return response()->json(['user' => $user]);
    }

    public function index()
    {
        $filters = request()->validate([
            'role' => ['string'],
            'search' => ['']
        ]);
        $query = User::query()->latest('id')->filter($filters);
        return response()->json(['data' => $query->paginate(request()->per_page ?? 20)]);
    }
}
