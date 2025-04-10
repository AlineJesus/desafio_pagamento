<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Realiza a transferência de valores entre usuários.
     *
     * @param  \App\Http\Requests\TransferRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $payer  = User::findOrFail($request->payer); // Ajustado para "payer"
        $payee  = User::findOrFail($request->payee); // Ajustado para "payee"
        $amount = $request->value; // Ajustado para "value"

        try {
            $this->transferService->transfer($payer, $payee, $amount);
            return response()->json(['message' => 'Transferência realizada com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
