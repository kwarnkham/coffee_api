<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'password' => ['required']
        ]);

        $user = User::query()->where('name', $data['name'])->first();
        abort_unless(
            $user && Hash::check($data['password'], $user->password),
            ResponseStatus::UNAUTHENTICATED->value,
            'Incorrect Info'
        );

        $user->tokens()->delete();
        $token = $user->createToken('');

        return response()->json(['token' => $token->plainTextToken, 'user' => $user]);
    }

    public function changePassword()
    {
        $data = request()->validate([
            'password' => ['required'],
            'new_password' => ['required', 'confirmed']
        ]);
        $user = request()->user();
        abort_unless(Hash::check($data['password'], $user->password), ResponseStatus::UNAUTHORIZED->value, 'Incorrect Password');

        $user->password = bcrypt($data['new_password']);
        $user->save();

        return response()->json(['user' => $user]);
    }

    public function user()
    {
        return response()->json(['user' => request()->user()]);
    }

    public function logout()
    {
        request()->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
