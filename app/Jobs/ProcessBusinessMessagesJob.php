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

    public $tries = 10;

    public $messages = null;

    public $userMessageCreated = false;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly string $cacheKey,
        public readonly ?string $chatName = null,
    ) {}

    public function handle(): void
    {
        if ($this->messages === null) {
            $this->messages = Cache::pull($this->cacheKey);
        }

        if (empty($this->messages)) {
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

        $combined = count($this->messages) === 1
            ? $this->messages[0]
            : 'Foydalanuvchi ketma-ket bir nechta xabar yubordi: "'.implode('" va "', $this->messages).'". Barchasiga bitta tabiiy javob ber.';

        $chatLang = LanguageDetector::detectAndSave($this->chatId, $combined, $this->chatName);
        $chatLang->load('persona');

        $history = ChatMessage::getHistory($this->chatId, 20)
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        try {
            $meModeActive = Cache::get("memode_{$this->chatId}", false)
                || BotSetting::get('me_mode_global', '0') === '1';

            $assistant = new TelegramAssistant(
                meModeEnabled: $meModeActive,
                language: $chatLang->language_name,
                addressForm: $chatLang->address_form ?? 'siz',
                personaInstruction: $chatLang->persona?->prompt_instruction,
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

            $this->sendMessage($token, $replyText);
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('AI reply failed', ['error' => $e->getMessage()]);

            // If Gemini is overloaded, retry after 30 seconds
            if (str_contains($e->getMessage(), 'overloaded') && $this->attempts() < $this->tries) {
                $this->release(30);

                return;
            }

            $adminChatId = config('admin.telegram_chat_id');
            if ($adminChatId) {
                $errorMsg = "⚠️ <b>AI Xatolik:</b>\n\n";
                $errorMsg .= "Chat: <code>{$this->chatId}</code> ({$this->chatName})\n";
                $errorMsg .= "Xato: <code>{$e->getMessage()}</code>\n";
                $errorMsg .= "Urinishlar: {$this->attempts()} / {$this->tries}";

                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $adminChatId,
                    'text' => $errorMsg,
                    'parse_mode' => 'HTML',
                ]);
            }
        }
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
