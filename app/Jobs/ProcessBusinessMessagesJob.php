<?php

namespace App\Jobs;

use App\Ai\Agents\TelegramAssistant;
use App\Models\BotSetting;
use App\Models\ChatMessage;
use App\Services\LanguageDetector;
use Carbon\Carbon;
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
        public readonly ?string $chatName = null,
    ) {}

    public function handle(): void
    {
        $messages = Cache::pull($this->cacheKey);

        if (empty($messages)) {
            return;
        }

        $token = config('telegram.bot_token');

        if ($this->isOutsideWorkingHours()) {
            $replyText = BotSetting::get('working_hours_message', config('telegram.fallback_reply'));
            $this->sendMessage($token, $replyText);

            return;
        }

        if (BotSetting::get('ai_enabled', '1') !== '1') {
            return;
        }

        $this->sendTyping($token);

        $combined = count($messages) === 1
            ? $messages[0]
            : 'Foydalanuvchi ketma-ket bir nechta xabar yubordi: "'.implode('" va "', $messages).'". Barchasiga bitta tabiiy javob ber.';

        $chatLang = LanguageDetector::detectAndSave($this->chatId, $combined, $this->chatName);

        $history = ChatMessage::getHistory($this->chatId, 20)
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        ChatMessage::create([
            'chat_id' => $this->chatId,
            'role' => 'user',
            'content' => $combined,
        ]);

        try {
            $meModeActive = Cache::get("memode_{$this->chatId}", false)
                || BotSetting::get('me_mode_global', '0') === '1';

            $assistant = new TelegramAssistant(
                meModeEnabled: $meModeActive,
                language: $chatLang->language_name,
                addressForm: $chatLang->address_form ?? 'siz',
                conversationHistory: $history,
            );

            $response = $assistant->prompt($combined);
            $replyText = $response->text;

            ChatMessage::create([
                'chat_id' => $this->chatId,
                'role' => 'assistant',
                'content' => $replyText,
            ]);

            ChatMessage::cleanupOld($this->chatId, 30);
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('AI reply failed', ['error' => $e->getMessage()]);
            $replyText = BotSetting::get('fallback_reply', config('telegram.fallback_reply', 'Xabaringiz qabul qilindi. Tez orada javob beraman! ✅'));
        }

        $this->sendMessage($token, $replyText);
    }

    private function isOutsideWorkingHours(): bool
    {
        if (BotSetting::get('working_hours_enabled', '0') !== '1') {
            return false;
        }

        $timezone = BotSetting::get('working_hours_timezone', 'Asia/Tashkent');
        $now = Carbon::now($timezone);
        $start = Carbon::createFromTimeString(BotSetting::get('working_hours_start', '09:00'), $timezone);
        $end = Carbon::createFromTimeString(BotSetting::get('working_hours_end', '18:00'), $timezone);

        return ! $now->between($start, $end);
    }

    private function sendTyping(string $token): void
    {
        Http::post("https://api.telegram.org/bot{$token}/sendChatAction", [
            'chat_id' => $this->chatId,
            'action' => 'typing',
            'business_connection_id' => $this->connectionId,
        ]);
    }

    private function sendMessage(string $token, string $text): void
    {
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $text,
            'business_connection_id' => $this->connectionId,
            'parse_mode' => 'HTML',
        ]);
    }
}
