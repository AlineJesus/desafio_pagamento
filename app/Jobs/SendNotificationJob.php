<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O endereço de e-mail do destinatário
     *
     * @var string
     */
    protected $email;

    /**
     * O assunto do e-mail
     *
     * @var string
     */
    protected $subject;

    /**
     * A mensagem de notificação
     *
     * @var string
     */
    protected $message;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct(
        string $email,
        string $subject,
        string $formattedAmount,
        string $payerName
    ) {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = sprintf(
            'You have received a payment of R$ %s out of %s.',
            $formattedAmount,
            $payerName
        );
    }

    /**
     * Executa o job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Envia a notificação para o serviço externo
            $response = Http::post('https://util.devi.tools/api/v1/notify', [
                'email' => $this->email,
                'message' => $this->message,
            ]);

            if ($response->successful()) {
                // Envia o e-mail
                Mail::raw($this->message, function ($mail) {
                    $mail->to($this->email)
                        ->subject($this->subject);
                });

                Log::info("Notificação enviada para: {$this->email}");
            } else {
                Log::warning("Serviço de notificação retornou erro para: {$this->email}", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                $this->release(60); // Tenta novamente após 60 segundos
            }
        } catch (\Exception $e) {
            Log::error("Falha ao enviar notificação para: {$this->email}", [
                'error' => $e->getMessage(),
            ]);
            $this->release(60); // Tenta novamente após 60 segundos
        }
    }

    /**
     * Lida com a falha do job.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("Job de notificação falhou para: {$this->email}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
