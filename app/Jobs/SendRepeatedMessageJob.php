<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendRepeatedMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly int $count,
        public readonly string $message,
        public readonly string $token
    ) {}

    public function handle(): void
    {
        $stopKey = "repeat_stop_{$this->chatId}";

        for ($i = 1; $i <= $this->count; $i++) {
            // Check if stop signal was sent
            if (Cache::get($stopKey)) {
                Log::channel('telegram')->info("Repeat command stopped manually for chat: {$this->chatId} at iteration {$i}");
                break;
            }

            try {
                $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                    'business_connection_id' => $this->connectionId,
                    'chat_id' => $this->chatId,
                    'text' => $this->message,
                ]);

                if (! $response->successful()) {
                    Log::channel('telegram')->error("Repeat message failed at {$i}: ".$response->body());

                    // If rate limited, wait longer
                    if ($response->status() === 429) {
                        sleep(5);
                    }
                }
            } catch (\Throwable $e) {
                Log::channel('telegram')->error('Repeat message exception: '.$e->getMessage());
            }

            // Small delay to prevent hitting Telegram flood limits too hard
            // 0.5s is usually safe for business connections
            usleep(500000);
        }
    }
}
