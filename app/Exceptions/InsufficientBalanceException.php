<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientBalanceException extends Exception
{
    /**
     * Renderiza a exceção em uma resposta JSON.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Insufficient balance to make the transfer.',
        ], 422);
    }
}
