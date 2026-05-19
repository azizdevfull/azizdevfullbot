<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('telegram:set-webhook {url? : Webhook URL (default: APP_URL/telegram/webhook)}')]
#[Description('Register webhook URL with Telegram')]
class SetTelegramWebhook extends Command
{
    public function handle(): int
    {
        $token = config('telegram.bot_token');

        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN not set in .env');

            return self::FAILURE;
        }

        $url = $this->argument('url') ?? url('/telegram/webhook');
        $secret = config('telegram.webhook_secret');

        $payload = ['url' => $url];

        if ($secret) {
            $payload['secret_token'] = $secret;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", $payload);
        $result = $response->json();

        if ($result['ok'] ?? false) {
            $this->info("Webhook set: {$url}");

            return self::SUCCESS;
        }

        $this->error('Failed: '.($result['description'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
