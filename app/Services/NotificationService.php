<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envia uma notificação para o usuário ou lojista.
     */
    public function sendNotification(string $recipient, string $message): bool
    {
        try {
            $response = Http::post('https://util.devi.tools/api/v1/notify', [
                'recipient' => $recipient,
                'message' => $message,
            ]);

            if ($response->ok() && $response->json('success') === true) {
                return true;
            }

            Log::error('Falha ao enviar notificação.', [
                'recipient' => $recipient,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao tentar enviar notificação.', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
