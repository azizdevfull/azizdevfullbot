<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends the same message to a chat many times.
 *
 * Each job sends one chunk, then re-dispatches itself for the remainder, so a run
 * of any size never hits worker timeouts and survives worker restarts.
 */
class SendRepeatedMessageJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string $connectionId,
        public readonly int $count,
        public readonly string $message,
        public readonly string $runId,
        public readonly int $sent = 0,
    ) {}

    public static function stopKey(int|string $chatId): string
    {
        return "repeat_stop_{$chatId}";
    }

    public static function runKey(int|string $chatId): string
    {
        return "repeat_run_{$chatId}";
    }

    public static function progressKey(int|string $chatId): string
    {
        return "repeat_progress_{$chatId}";
    }

    /**
     * Start a new run for the chat, superseding any run already in progress.
     */
    public static function start(int|string $chatId, string $connectionId, int $count, string $message): void
    {
        $runId = (string) str()->uuid();

        Cache::forget(static::stopKey($chatId));
        Cache::forever(static::runKey($chatId), $runId);

        static::dispatch($chatId, $connectionId, $count, $message, $runId);
    }

    public function handle(): void
    {
        $token = config('telegram.bot_token');
        $chunkSize = max(1, (int) config('telegram.repeat.chunk_size', 50));
        $delayMicroseconds = max(0, (int) config('telegram.repeat.delay_ms', 1000)) * 1000;

        $sent = $this->sent;
        $chunkEnd = min($this->count, $sent + $chunkSize);

        while ($sent < $chunkEnd) {
            if (! $this->isActive()) {
                Log::channel('telegram')->info("Repeat stopped for chat {$this->chatId} after {$sent}/{$this->count}");
                $this->finish();

                return;
            }

            try {
                $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'business_connection_id' => $this->connectionId,
                    'chat_id' => $this->chatId,
                    'text' => $this->message,
                ]);
            } catch (\Throwable $e) {
                Log::channel('telegram')->error("Repeat request failed at {$sent}: ".$e->getMessage());
                $this->continueFrom($sent, 5);

                return;
            }

            if ($response->status() === 429) {
                $retryAfter = (int) $response->json('parameters.retry_after', 5);
                $this->continueFrom($sent, $retryAfter + 1);

                return;
            }

            if (! $response->successful()) {
                Log::channel('telegram')->error("Repeat aborted at {$sent}/{$this->count}: ".$response->body());
                $this->finish();

                return;
            }

            $sent++;

            if ($sent < $this->count) {
                usleep($delayMicroseconds);
            }
        }

        if ($sent >= $this->count) {
            $this->finish();

            return;
        }

        $this->continueFrom($sent);
    }

    private function isActive(): bool
    {
        return ! Cache::get(static::stopKey($this->chatId))
            && Cache::get(static::runKey($this->chatId)) === $this->runId;
    }

    private function continueFrom(int $sent, int $delaySeconds = 0): void
    {
        Cache::put(static::progressKey($this->chatId), "{$sent}/{$this->count}", now()->addDay());

        $next = new static($this->chatId, $this->connectionId, $this->count, $this->message, $this->runId, $sent);

        dispatch($next->delay($delaySeconds > 0 ? now()->addSeconds($delaySeconds) : null));
    }

    private function finish(): void
    {
        Cache::forget(static::progressKey($this->chatId));

        if (Cache::get(static::runKey($this->chatId)) === $this->runId) {
            Cache::forget(static::runKey($this->chatId));
        }
    }
}
