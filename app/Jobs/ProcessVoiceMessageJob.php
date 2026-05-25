<?php

namespace App\Jobs;

use App\Ai\Agents\TranscriptionAgent;
use App\Models\ChatMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Audio;

class ProcessVoiceMessageJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly string $fileId,
        public readonly ?string $chatName = null,
    ) {}

    public function handle(): void
    {
        $token = config('telegram.bot_token');

        try {
            // 1. Get file path from Telegram
            $response = Http::get("https://api.telegram.org/bot{$token}/getFile", [
                'file_id' => $this->fileId,
            ]);

            if (! $response->successful()) {
                throw new \Exception('Telegram getFile failed: '.$response->body());
            }

            $filePath = $response->json('result.file_path');
            $downloadUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

            // 2. Download file
            $audioContent = Http::get($downloadUrl)->body();
            $tempPath = 'temp/voice_'.uniqid().'.ogg';
            Storage::disk('local')->put($tempPath, $audioContent);
            $fullPath = Storage::disk('local')->path($tempPath);

            // 3. Transcribe via Gemini
            $agent = new TranscriptionAgent;
            $transcriptionResponse = $agent->prompt(
                'Ushbu audio xabarni matnga o\'giring.',
                attachments: [
                    Audio::fromPath($fullPath),
                ]
            );

            $text = trim($transcriptionResponse->text);

            // Log what the bot heard
            Log::channel('telegram')->info("Voice message transcribed for chat {$this->chatId}: \"{$text}\"");

            // Clean up temp file            Storage::disk('local')->delete($tempPath);

            if (empty($text)) {
                $text = '[Ovozli xabarda hech nima tushunilmadi]';
            }

            // 4. Update placeholder message
            $placeholder = ChatMessage::where('chat_id', $this->chatId)
                ->where('role', 'user')
                ->where('content', '🎤 [Ovozli xabar kutilmoqda...]')
                ->orderByDesc('id')
                ->first();

            if ($placeholder) {
                $placeholder->update([
                    'content' => "🎤 [Ovozli]: {$text}",
                ]);
            } else {
                ChatMessage::create([
                    'chat_id' => $this->chatId,
                    'role' => 'user',
                    'content' => "🎤 [Ovozli]: {$text}",
                ]);
            }

            // 5. Trigger AI reply via debounce logic
            $this->debounceAiReply("🎤 [Ovozli xabarning matni]: {$text}");

        } catch (\Throwable $e) {
            Log::channel('telegram')->error('ProcessVoiceMessageJob failed', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage(),
            ]);

            // Update placeholder with error
            ChatMessage::where('chat_id', $this->chatId)
                ->where('content', '🎤 [Ovozli xabar kutilmoqda...]')
                ->orderByDesc('id')
                ->limit(1)
                ->update(['content' => '🎤 [Ovozli xabarni o\'qib bo\'lmadi ❌]']);
        }
    }

    private function debounceAiReply(string $text): void
    {
        $cacheKey = "telegram_pending_{$this->chatId}";
        $lockKey = "telegram_job_{$this->chatId}";
        $delay = config('telegram.debounce_seconds', 8);

        $messages = Cache::get($cacheKey, []);
        $messages[] = $text;
        Cache::put($cacheKey, $messages, $delay + 5);

        if (! Cache::has($lockKey)) {
            Cache::put($lockKey, true, $delay);
            ProcessBusinessMessagesJob::dispatch($this->chatId, $this->connectionId, $cacheKey, $this->chatName)
                ->delay(now()->addSeconds($delay));
        }
    }
}
