<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Exceptions\InsufficientBalanceException;
use App\Jobs\SendNotificationJob;

class TransferService
{
    /**
     * Realiza a transferência de valores entre usuários.
     *
     * @param User $payer
     * @param User $payee
     * @param float $amount
     * @throws \Exception
     * @return void
     */
    public function transfer(User $payer, User $payee, float $amount): void
    {
        // Verifica se o pagador é um lojista
        if ($payer->type === 'shopkeeper') {
            throw new \Exception('Lojistas não podem enviar dinheiro.');
        }

        // Verifica se o usuário possui saldo suficiente
        if ($payer->balance < $amount) {
            throw new InsufficientBalanceException();
        }

        // Consulta o serviço autorizador externo
        $response = Http::get('https://util.devi.tools/api/v2/authorize');

        if (!$response->ok() || !$response->json('data.authorization')) {
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
        SendNotificationJob::dispatch($payer->email, 'Payment received notification')
        ->onQueue('notifications');
    }
}
