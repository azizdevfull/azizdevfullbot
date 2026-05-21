<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendBusinessMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly string $text,
    ) {}

    public function handle(): void
    {
        $token = config('telegram.bot_token');

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'business_connection_id' => $this->connectionId,
        ]);
    }
}
