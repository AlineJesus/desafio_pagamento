<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'data' => $user
        ], 201);
    }
}
