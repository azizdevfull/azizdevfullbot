<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBusinessMessagesJob;
use App\Jobs\RefinePersonaJob;
use App\Jobs\SendBusinessMessageJob;
use App\Models\BotSetting;
use App\Models\BusinessConnection;
use App\Models\ChatLanguage;
use App\Models\ChatMessage;
use App\Models\TelegramCommand;
use App\Telegram\BotAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $update = $request->all();

        Log::channel('telegram')->info('Telegram update', $update);

        if (isset($update['business_connection'])) {
            $this->handleBusinessConnection($update['business_connection']);
        } elseif (isset($update['business_message'])) {
            $this->handleBusinessMessage($update['business_message']);
        } elseif (isset($update['message'])) {
            $this->handleDirectMessage($update['message']);
        }

        return response('OK');
    }

    private function handleBusinessConnection(array $data): void
    {
        BusinessConnection::updateOrCreate(
            ['connection_id' => $data['id']],
            [
                'telegram_user_id' => $data['user']['id'],
                'user_chat_id' => $data['user_chat_id'] ?? null,
                'can_reply' => $data['can_reply'] ?? false,
                'is_enabled' => $data['is_enabled'] ?? true,
            ]
        );
    }

    private function handleBusinessMessage(array $message): void
    {
        $connectionId = $message['business_connection_id'] ?? null;

        if (! $connectionId) {
            return;
        }

        $connection = BusinessConnection::where('connection_id', $connectionId)->first();

        if (! $connection) {
            $connection = $this->fetchAndSaveConnection($connectionId);
        }

        if (! $connection || ! $connection->is_enabled) {
            return;
        }

        // Owner identification: Check both telegram_user_id (Business account)
        // and user_chat_id (the bot-facing ID) for safety.
        $isFromOwner = isset($message['from']['id']) && (
            $message['from']['id'] === $connection->telegram_user_id ||
            $message['from']['id'] === (int) $connection->user_chat_id
        );

        $chatId = $message['chat']['id'];
        $messageId = $message['message_id'];
        $text = $message['text'] ?? $message['caption'] ?? '';

        $chatLang = ChatLanguage::where('chat_id', $chatId)->first();
        $globalAi = BotSetting::get('ai_enabled', '1') === '1';
        $perChatAi = $chatLang?->ai_enabled ?? true;
        $isAiEnabled = $globalAi && $perChatAi;

        $globalLearning = BotSetting::get('learning_enabled', '1') === '1';
        $perChatLearning = $chatLang?->learning_enabled ?? true;
        $isLearningEnabled = $globalLearning && $perChatLearning;

        // Logging Logic based on AI and Learn toggles:
        // 1. If both are OFF -> Do not save anything (Complete privacy).
        // 2. If Learn is ON -> Save everything (User, Owner, AI) for learning context.
        // 3. If AI is ON but Learn is OFF -> Save User and AI messages for conversation history, but DO NOT save Owner messages.

        $shouldSaveMessage = false;

        if ($isLearningEnabled) {
            $shouldSaveMessage = true;
        } elseif ($isAiEnabled) {
            // AI is on, Learn is off. Save only if it's NOT from the owner.
            $shouldSaveMessage = ! $isFromOwner;
        }

        if (! empty($text) && $shouldSaveMessage) {
            ChatMessage::create([
                'chat_id' => $chatId,
                'role' => $isFromOwner ? 'assistant' : 'user',
                'content' => $text,
                'is_manual' => $isFromOwner,
            ]);
        }

        if ($isFromOwner) {
            if (str_starts_with($text, '/')) {
                $command = strtolower(explode(' ', ltrim(explode('@', $text)[0], '/'))[0]);

                if ($command === 'memode') {
                    $this->deleteBusinessMessages($chatId, [$messageId], $connectionId);
                    $this->toggleMeMode($chatId, $connection->user_chat_id);

                    return;
                }

                $dbCommand = TelegramCommand::where('command', $command)->first();
                $reply = $dbCommand?->reply ?? config("telegram.commands.{$command}");

                if ($reply !== null) {
                    $this->deleteBusinessMessages($chatId, [$messageId], $connectionId);
                    $this->sendMessage($chatId, $reply, $connectionId);
                }
            } else {
                // Trigger persona refinement occasionally (e.g., every 10 manual messages)
                if (! empty($text)) {
                    if ($isLearningEnabled && $chatLang && $chatLang->persona_id) {
                        $manualCount = ChatMessage::where('is_manual', true)
                            ->whereIn('chat_id', function ($query) use ($chatLang) {
                                $query->select('chat_id')
                                    ->from('chat_languages')
                                    ->where('persona_id', $chatLang->persona_id);
                            })->count();

                        if ($manualCount > 0 && $manualCount % 10 === 0) {
                            RefinePersonaJob::dispatch($chatLang->persona_id);
                        }
                    }
                }
            }

            return;
        }

        if (! $connection->can_reply) {
            return;
        }

        if (empty($text)) {
            // Only send media fallback if AI is enabled globally and locally
            if ($isAiEnabled && (isset($message['voice']) || isset($message['video_note']))) {
                $this->sendMediaReply($chatId, $connectionId);
            }

            // Stay silent for animations (GIFs), stickers, photos, and videos without captions
            // to avoid annoying "I can't watch/listen" messages.
            return;
        }

        if ($isAiEnabled) {
            $chatName = $this->resolveChatName($message['chat'] ?? []);
            $this->debounceAiReply($chatId, $connectionId, $text, $chatName);
        }
    }

    private function handleDirectMessage(array $message): void
    {
        $fromId = $message['from']['id'] ?? null;
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;

        if (! $fromId || ! $chatId || empty($text)) {
            return;
        }

        $ownerConnection = BusinessConnection::whereNotNull('user_chat_id')->first();

        if (! $ownerConnection || $fromId !== $ownerConnection->telegram_user_id) {
            return;
        }

        (new BotAdmin($chatId))->handle($text);
    }

    private function debounceAiReply(int|string $chatId, string $connectionId, string $text, ?string $chatName = null): void
    {
        $cacheKey = "telegram_pending_{$chatId}";
        $lockKey = "telegram_job_{$chatId}";
        $delay = config('telegram.debounce_seconds', 8);

        $messages = Cache::get($cacheKey, []);
        $messages[] = $text;
        Cache::put($cacheKey, $messages, $delay + 5);

        if (! Cache::has($lockKey)) {
            Cache::put($lockKey, true, $delay);
            ProcessBusinessMessagesJob::dispatch($chatId, $connectionId, $cacheKey, $chatName)
                ->delay(now()->addSeconds($delay));
        }
    }

    private function fetchAndSaveConnection(string $connectionId): ?BusinessConnection
    {
        $token = config('telegram.bot_token');

        $response = Http::get("https://api.telegram.org/bot{$token}/getBusinessConnection", [
            'business_connection_id' => $connectionId,
        ]);

        $result = $response->json();

        if (! ($result['ok'] ?? false)) {
            Log::channel('telegram')->warning('getBusinessConnection failed', $result);

            return null;
        }

        $data = $result['result'];

        return BusinessConnection::updateOrCreate(
            ['connection_id' => $data['id']],
            [
                'telegram_user_id' => $data['user']['id'],
                'user_chat_id' => $data['user_chat_id'] ?? null,
                'can_reply' => $data['can_reply'] ?? false,
                'is_enabled' => $data['is_enabled'] ?? true,
            ]
        );
    }

    private function deleteBusinessMessages(int|string $chatId, array $messageIds, string $businessConnectionId): void
    {
        $token = config('telegram.bot_token');

        $response = Http::post("https://api.telegram.org/bot{$token}/deleteBusinessMessages", [
            'chat_id' => $chatId,
            'message_ids' => $messageIds,
            'business_connection_id' => $businessConnectionId,
        ]);

        if (! ($response->json('ok') ?? false)) {
            Log::channel('telegram')->warning('deleteBusinessMessages failed', $response->json());
        }
    }

    private function toggleMeMode(int|string $chatId, int|string|null $ownerChatId): void
    {
        $key = "memode_{$chatId}";
        $isActive = Cache::get($key, false);

        if ($isActive) {
            Cache::forget($key);
            $status = 'Me Mode <b>o\'chirildi</b> ❌';
        } else {
            Cache::put($key, true, now()->addHours(24));
            $status = 'Me Mode <b>yoqildi</b> ✅ — AI endi siz sifatida yozadi.';
        }

        if ($ownerChatId) {
            $token = config('telegram.bot_token');
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $ownerChatId,
                'text' => $status,
                'parse_mode' => 'HTML',
            ]);
        }
    }

    private function sendMediaReply(int|string $chatId, string $connectionId): void
    {
        $chatLang = ChatLanguage::forChat($chatId);
        $langCode = $chatLang?->language_code ?? 'uz';
        $addressForm = $chatLang?->address_form ?? 'siz';

        $replies = config("telegram.media_replies.{$langCode}.{$addressForm}")
            ?? config('telegram.media_replies.uz.siz');

        $text = $replies[array_rand($replies)];

        SendBusinessMessageJob::dispatch($chatId, $connectionId, $text)
            ->delay(now()->addSeconds(3));
    }

    private function resolveChatName(array $chat): ?string
    {
        $parts = array_filter([
            $chat['first_name'] ?? null,
            $chat['last_name'] ?? null,
        ]);

        if ($parts) {
            return implode(' ', $parts);
        }

        return $chat['username'] ?? null;
    }

    private function sendMessage(int|string $chatId, string $text, string $businessConnectionId): void
    {
        $token = config('telegram.bot_token');

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'business_connection_id' => $businessConnectionId,
            'parse_mode' => 'HTML',
        ]);
    }
}
