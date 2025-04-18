<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TransferService
{
    /**
     * Realiza a transferência de valores entre usuários.
     *
     * @throws \Exception
     */
    public function transfer(User $payer, User $payee, float $amount): void
    {

        if ($payer->id === $payee->id) {
            throw new \Exception('Cannot transfer to yourself');
        }

        if ($payer->type !== 'common') {
            throw new \Exception('Shopkeepers cannot make transfers');
        }

        // Verifica se o usuário possui saldo suficiente
        if ($payer->balance < $amount) {
            throw ValidationException::withMessages([
                'value' => 'Insufficient balance to make the transfer.',
            ]);
        }

        // Consulta o serviço autorizador externo
        $url = config('services.authorizer.url');

        if (! is_string($url) || empty($url)) {
            throw new \Exception('Invalid authorizer service URL.');
        }

        $response = Http::get($url);

        if (! $response->ok()) {
            throw new \Exception('Failed to query the authorizing service.');
        }

        $responseData = $response->json();

        // Validate response structure
        if (! is_array($responseData)) {
            throw new \Exception('Invalid authorizer service response.');
        }

        if (! isset($responseData['status']) || $responseData['status'] !== 'success') {
            throw new \Exception('Authorizer service returned a non-success status.');
        }

        if (! isset($responseData['data']) || ! is_array($responseData['data'])) {
            throw new \Exception('Invalid authorizer service response format.');
        }

        if (! isset($responseData['data']['authorization']) || $responseData['data']['authorization'] !== true) {
            throw new \Exception('Unauthorized transaction.');
        }

        // Realiza a transferência dentro de uma transação do banco de dados
        DB::transaction(function () use ($payer, $payee, $amount) {
            // Decrementa o saldo do pagador
            $payer->decrement('balance', $amount);

            // Incrementa o saldo do recebedor
            $payee->increment('balance', $amount);

            // Registra a transação
            Transaction::create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
            ]);
        });

        // Envia uma notificação para o recebedor

        $formattedAmount = number_format($amount, 2, ',', '.');
        $subject = 'Payment received notification';

        SendNotificationJob::dispatch($payee->email, $subject, $formattedAmount, $payer->full_name);
    }
}
