<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Retry;
use App\Exceptions\InsufficientBalanceException;
use App\Jobs\SendNotificationJob;
//use App\Services\NotificationService;

class TransferService
{
    /* protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    } */

    /**
     * Realiza a transferência de valores entre usuários.
     *
     * @param User $payer
     * @param User $payee
     * @param float $amount
     * @throws \Exception
     */
    public function transfer(User $payer, User $payee, float $amount)
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
        $response = Http::get('https://util.devi.tools/api/v1/authorize');

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
        /* $message = "Você recebeu uma transferência de R$ {$amount} de {$payer->full_name}.";
        $notificationSent = Retry::times(3)->catch(function () {
            return false;
        })->run(function () use ($payee, $message) {
            return $this->notificationService->sendNotification($payee->email, $message);
        });

        if (!$notificationSent) {
            throw new \Exception('Falha ao enviar notificação ao recebedor após múltiplas tentativas.');
        } */

        SendNotificationJob::dispatch($payee);

    }
}
