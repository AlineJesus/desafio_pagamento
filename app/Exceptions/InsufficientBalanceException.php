<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function render($request)
    {
        /*
            Saldo insuficiente para realizar a transferência
        */
        return response()->json([
            'error' => 'Insufficient balance to make the transfer.'
        ], 422);
    }
}
