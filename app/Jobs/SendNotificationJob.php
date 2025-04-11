<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The recipient email address
     *
     * @var string
     */
    protected $email;

    /**
     * The notification message
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $message
     */
    public function __construct(string $email, string $message = 'You have received a payment')
    {
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post('https://util.devi.tools/api/v1/notify', [
                'email' => $this->email,
                'message' => $this->message
            ]);

            if ($response->successful()) {
                Log::info("Notification successfully sent to: {$this->email}");
            } else {
                Log::warning("Notification service returned error for: {$this->email}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                // Requeue the job to try again later
                $this->release(60); // Retry after 60 seconds
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification to: {$this->email}", [
                'error' => $e->getMessage()
            ]);
            $this->release(60); // Retry after 60 seconds
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("Notification job failed for: {$this->email}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}