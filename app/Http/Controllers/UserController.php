<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Lista todos os usuarios
     */
    public function index(): JsonResponse
    {
        $users = User::select('full_name', 'email', 'document', 'type', 'balance', 'id')->get();

        return response()->json([
            'message' => 'Success!',
            'data' => $users,
        ], 200);
    }

    /**
     * Armazena um novo usuário.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $userData = $request->validated();

        $user = User::create([
            'full_name' => $userData['full_name'],
            'email' => $userData['email'],
            'document' => $userData['document'],
            'password' => bcrypt((string) $userData['password']),
            'balance' => $userData['balance'],
        ]);

        return response()->json([
            'message' => 'User created successfully!',
            'data' => [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'document' => $user->document,
                'type' => $user->type,
                'balance' => $user->balance,
                'id' => $user->id,
            ],
        ], 201);
    }

    /**
     * Atualizar saldo do usuario common
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        // Verifica se o usuário é do tipo 'common'
        if ($user->type !== 'common') {
            return response()->json([
                'error' => 'Only regular users can update balance.',
            ], 403);
        }

        // Atualiza o saldo
        $user->increment('balance', $request->balance);

        return response()->json([
            'message' => 'Balance updated successfully!',
            'balance' => $user->balance,
        ], 200);
    }
}
