<?php

use App\Jobs\SendRepeatedMessageJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'telegram.bot_token' => 'test-token',
        'telegram.repeat.chunk_size' => 3,
        'telegram.repeat.delay_ms' => 0,
    ]);

    Queue::fake();
    Cache::forever(SendRepeatedMessageJob::runKey(222), 'run-1');
});

function repeatJob(int $count, int $sent = 0, string $runId = 'run-1'): SendRepeatedMessageJob
{
    return new SendRepeatedMessageJob(222, 'conn-1', $count, 'Salom', $runId, $sent);
}

test('it sends one chunk and dispatches the remainder', function () {
    Http::fake(['*' => Http::response(['ok' => true])]);

    repeatJob(10)->handle();

    Http::assertSentCount(3);
    Queue::assertPushed(SendRepeatedMessageJob::class, fn (SendRepeatedMessageJob $job) => $job->sent === 3 && $job->count === 10);
});

test('it finishes without dispatching when the last chunk is sent', function () {
    Http::fake(['*' => Http::response(['ok' => true])]);

    repeatJob(5, 3)->handle();

    Http::assertSentCount(2);
    Queue::assertNothingPushed();
    expect(Cache::get(SendRepeatedMessageJob::runKey(222)))->toBeNull();
});

test('it retries the same message after the telegram retry_after delay', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['ok' => true])
        ->push(['ok' => false, 'error_code' => 429, 'parameters' => ['retry_after' => 7]], 429),
    ]);

    repeatJob(10)->handle();

    Queue::assertPushed(SendRepeatedMessageJob::class, fn (SendRepeatedMessageJob $job) => $job->sent === 1 && $job->delay !== null);
});

test('it stops when the stop flag is set', function () {
    Http::fake();
    Cache::put(SendRepeatedMessageJob::stopKey(222), true);

    repeatJob(10)->handle();

    Http::assertNothingSent();
    Queue::assertNothingPushed();
});

test('a superseded run stops', function () {
    Http::fake();

    repeatJob(10, 0, 'old-run')->handle();

    Http::assertNothingSent();
    Queue::assertNothingPushed();
});

test('it aborts on a non retryable telegram error', function () {
    Http::fake(['*' => Http::response(['ok' => false, 'description' => 'Forbidden'], 403)]);

    repeatJob(10)->handle();

    Http::assertSentCount(1);
    Queue::assertNothingPushed();
});
