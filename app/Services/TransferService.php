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
            throw new \Exception('Não pode transferir para si mesmo');
        }

        if ($payer->type !== 'common') {
            throw new \Exception('Lojistas não podem fazer transferências');
        }

        // Verifica se o usuário possui saldo suficiente
        if ($payer->balance < $amount) {
            throw ValidationException::withMessages([
                'value' => 'Insufficient balance to make the transfer.',
            ]);
        }

        // Consulta o serviço autorizador externo
        $response = Http::get(config('services.authorizer.url'));

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
        SendNotificationJob::dispatch($payee->email, 'Payment received notification');
        /* SendNotificationJob::dispatch($payer->email, 'Payment received notification')
            ->onQueue('notifications'); */
    }
}
