<?php

namespace App\Http\Controllers;

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

    public function index()
    {
        $query = User::query()->latest('id');
        return response()->json(['data' => $query->paginate(request()->per_page ?? 20)]);
    }
}
