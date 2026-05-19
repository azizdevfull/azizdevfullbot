<?php

namespace App\Jobs;

use App\Ai\Agents\TelegramAssistant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessBusinessMessagesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly string $cacheKey,
    ) {}

    public function handle(): void
    {
        $messages = Cache::pull($this->cacheKey);

        if (empty($messages)) {
            return;
        }

        $combined = count($messages) === 1
            ? $messages[0]
            : 'Foydalanuvchi ketma-ket bir nechta xabar yubordi: "'.implode('" va "', $messages).'". Barchasiga bitta tabiiy javob ber.';

        try {
            $response = (new TelegramAssistant)->prompt($combined);
            $replyText = $response->text;
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('AI reply failed', ['error' => $e->getMessage()]);
            $replyText = config('telegram.fallback_reply', 'Xabaringiz qabul qilindi. Tez orada javob beraman! ✅');
        }

        $token = config('telegram.bot_token');

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $replyText,
            'business_connection_id' => $this->connectionId,
            'parse_mode' => 'HTML',
        ]);
    }
}
