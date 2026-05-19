<?php

namespace App\Http\Controllers;

use App\Models\BusinessConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        if (! $connection || ! $connection->can_reply || ! $connection->is_enabled) {
            return;
        }

        $isFromOwner = isset($message['from']['id']) && $message['from']['id'] === $connection->telegram_user_id;
        $chatId = $message['chat']['id'];
        $messageId = $message['message_id'];
        $text = $message['text'] ?? '';

        if ($isFromOwner) {
            if (str_starts_with($text, '/')) {
                $command = strtolower(explode(' ', ltrim(explode('@', $text)[0], '/'))[0]);
                $reply = config("telegram.commands.{$command}");

                if ($reply !== null) {
                    $this->deleteBusinessMessages($chatId, [$messageId], $connectionId);
                    $this->sendMessage($chatId, $reply, $connectionId);
                }
            }

            return;
        }

        $this->streamAiReply($chatId, $connectionId, $text);
    }

    private function streamAiReply(int|string $chatId, string $connectionId, string $userMessage): void
    {
        $draftId = random_int(1, 2_147_483_647);
        $accumulated = '';
        $lastSent = 0.0;

        // Show "Thinking..." placeholder immediately
        $this->sendMessageDraft($chatId, $draftId, '');

        $geminiKey = config('ai.providers.gemini.key');
        $model = 'gemini-flash-lite-latest';
        $instructions = trim((string) config('telegram.ai_instructions'));

        $payload = json_encode([
            'system_instruction' => ['parts' => [['text' => $instructions]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $userMessage]]]],
        ]);

        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?key={$geminiKey}&alt=sse");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$accumulated, &$lastSent, $chatId, $draftId) {
                foreach (explode("\n", $data) as $line) {
                    if (str_starts_with($line, 'data: ')) {
                        $chunk = json_decode(substr($line, 6), true);
                        $text = $chunk['candidates'][0]['content']['parts'][0]['text'] ?? '';
                        if ($text !== '') {
                            $accumulated .= $text;
                        }
                    }
                }

                $now = microtime(true);
                if ($accumulated !== '' && ($now - $lastSent) >= 0.6) {
                    $this->sendMessageDraft($chatId, $draftId, $accumulated);
                    $lastSent = $now;
                }

                return strlen($data);
            },
        ]);

        curl_exec($ch);
        curl_close($ch);

        // Persist final message
        $finalText = $accumulated !== ''
            ? $accumulated
            : config('telegram.fallback_reply', 'Xabaringiz qabul qilindi. Tez orada javob beraman! ✅');

        $this->sendMessage($chatId, $finalText, $connectionId);
    }

    private function sendMessageDraft(int|string $chatId, int $draftId, string $text): void
    {
        $token = config('telegram.bot_token');

        Http::post("https://api.telegram.org/bot{$token}/sendMessageDraft", [
            'chat_id' => $chatId,
            'draft_id' => $draftId,
            'text' => $text,
        ]);
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
