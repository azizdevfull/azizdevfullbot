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

        $this->sendMessage($chatId, config('telegram.auto_reply_text'), $connectionId);
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
