<?php

use App\Models\BusinessConnection;
use App\Models\ChatLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['telegram.webhook_secret' => null]);
    Http::fake();

    BusinessConnection::create([
        'connection_id' => 'conn-1',
        'telegram_user_id' => 111,
        'user_chat_id' => 111,
        'can_reply' => false,
        'is_enabled' => true,
    ]);
});

/**
 * @return array<string, mixed>
 */
function businessMessage(int $fromId, string $firstName, ?string $lastName = null): array
{
    return [
        'business_message' => [
            'business_connection_id' => 'conn-1',
            'message_id' => 1,
            'from' => ['id' => $fromId],
            'chat' => array_filter(['id' => 222, 'first_name' => $firstName, 'last_name' => $lastName]),
            'text' => 'Salom',
        ],
    ];
}

test('chat name is saved from an incoming message even when AI does not reply', function () {
    $this->postJson(route('telegram.webhook'), businessMessage(222, 'Ali', 'Valiyev'))->assertSuccessful();

    expect(ChatLanguage::forChat(222)->chat_name)->toBe('Ali Valiyev');
});

test('chat name is saved from an owner message', function () {
    $this->postJson(route('telegram.webhook'), businessMessage(111, 'Ali'))->assertSuccessful();

    expect(ChatLanguage::forChat(222)->chat_name)->toBe('Ali');
});

test('chat name is updated when it changes', function () {
    ChatLanguage::create(['chat_id' => 222, 'chat_name' => 'Old Name', 'language_code' => 'ru']);

    $this->postJson(route('telegram.webhook'), businessMessage(222, 'New'))->assertSuccessful();

    $chatLanguage = ChatLanguage::forChat(222);

    expect($chatLanguage->chat_name)->toBe('New')
        ->and($chatLanguage->language_code)->toBe('ru');
});
