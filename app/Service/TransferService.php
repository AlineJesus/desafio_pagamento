<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendNotificationJob;
use App\Exceptions\InsufficientBalanceException;

class TransferService
{
    public function transfer(User $payer, User $payee, float $amount)
    {

         // Verifica se o usuário possui saldo suficiente
        if ($payer->balance < $amount) {
            throw new InsufficientBalanceException();
        }

        if ($payer->type !== 'common') {
            throw new \Exception('Only common users can make transfers.');
        }

        if ($payer->balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        $response = Http::get('https://util.devi.tools/api/v1/authorize');

        if (!$response->ok() || !$response->json('data.authorization')) {
            throw new \Exception('Unauthorized transaction.');
        }

        DB::transaction(function () use ($payer, $payee, $amount) {
            $payer->decrement('balance', $amount);
            $payee->increment('balance', $amount);

            Transaction::create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
            ]);
        });

        SendNotificationJob::dispatch($payee);
    }
}
