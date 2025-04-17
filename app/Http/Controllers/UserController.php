<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Armazena um novo usuário.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'data' => $user,
        ], 201);
    }

    /**
     * Atualizar saldo do usuario common
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        // Verifica se o usuário é do tipo 'common'
        if ($user->type !== 'common') {
            return response()->json([
                'error' => 'Apenas usuários comuns podem atualizar o saldo.',
            ], 403);
        }

        // Atualiza o saldo
        $user->increment('balance', $request->balance);

        return response()->json([
            'message' => 'Saldo atualizado com sucesso!',
            'balance' => $user->balance,
        ], 200);
    }
}
