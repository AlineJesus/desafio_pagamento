<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(): View
    {
        /** @var User $payer */
        $payer = auth()->user();

        return view('dashboard', [
            'shopkeeper' => User::where('type', 'shopkeeper')->get(),
            'type' => $payer->type,
        ]);

    }

    /**
     * Realiza a transferência de valores entre usuários.
     */
    public function transfer(TransferRequest $request): JsonResponse
    {

        $payer = auth()->user(); // Usuário logado
        $payee = User::findOrFail($request->payee()); // Usando o método payee()
        $amount = $request->value(); // Usando o método value()

        try {
            $this->transferService->transfer($payer, $payee, $amount);

            return response()->json(['message' => 'Transfer completed successfully.'], 200);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);

        }
    }
}
