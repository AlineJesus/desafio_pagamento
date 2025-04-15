<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index()
    {
        $payer = auth()->user();

        return view('dashboard', [
            'shopkeeper' => User::where('type', 'shopkeeper')->get(),
            'type' => $payer->type,
        ]);

    }

    /**
     * Realiza a transferência de valores entre usuários.
     */
    public function transfer(TransferRequest $request)
    {

        $payer = auth()->user(); // Usuário logado
        $payee = User::findOrFail($request->payee()); // Usando o método payee()
        // $amount = $request->input('value');
        $amount = $request->value(); // Usando o método value()

        try {
            $this->transferService->transfer($payer, $payee, $amount);

            return redirect()->route('dashboard')->with('success', 'Transferência realizada!');

        } catch (ValidationException $e) {
            // Captura específica de erros de validação
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
