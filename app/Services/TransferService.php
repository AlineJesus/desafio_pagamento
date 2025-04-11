<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Jobs\SendNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransferService
{
    /**
     * Realiza a transferência de valores entre usuários.
     *
     * @throws \Exception
     */
    public function transfer(User $payer, User $payee, float $amount): void
    {
        // Verifica se o pagador é um lojista
        if ($payer->type === 'shopkeeper') {
            throw new \Exception('Lojistas não podem enviar dinheiro.');
        }

        // Verifica se o usuário possui saldo suficiente
        if ($payer->balance < $amount) {
            throw new InsufficientBalanceException;
        }

        // Consulta o serviço autorizador externo
        $response = Http::get('https://util.devi.tools/api/v2/authorize');

        // Loga a resposta do serviço autorizador
        Log::info('Resposta do serviço autorizador:', $response->json());

        // Verifica se a resposta foi bem-sucedida e se a autorização é verdadeira
        if (! $response->ok() || ! ($response->json('data')['authorization'] ?? false)) {
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
