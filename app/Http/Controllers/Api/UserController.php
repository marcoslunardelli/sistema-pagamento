<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // <- base correto
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(CreateUserRequest $req)
    {
        $data = $req->validated();
        $data["password"] = Hash::make($data["password"]);
        $user = User::create($data);

        return response()->json($user->makeHidden(["password"]), 201);
    }

    public function show(int $id)
    {
        $user = User::findOrFail($id)->makeHidden(["password"]);
        return response()->json($user);
    }
}
