<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
        $payer  = User::findOrFail($request->payer()); // Usando o método payer()
        $payee  = User::findOrFail($request->payee()); // Usando o método payee()
        $amount = $request->value(); // Usando o método value()

        try {
            $this->transferService->transfer($payer, $payee, $amount);
            Log::info('Transferência realizada com sucesso');
            return response()->json(['message' => 'Transferência realizada com sucesso.'], 200);
        } catch (\Exception $e) {
            Log::error('Erro na transferência', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
