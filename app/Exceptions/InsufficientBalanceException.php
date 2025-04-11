<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InsufficientBalanceException extends Exception
{
    /**
     * Renderiza a exceção em uma resposta JSON.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Insufficient balance to make the transfer.'
        ], 422);
    }
}
