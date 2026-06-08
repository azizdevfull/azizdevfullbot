<?php

namespace App\Telegram;

use App\Models\BotSetting;
use App\Models\BusinessConnection;
use App\Models\ChatLanguage;
use App\Models\ChatMessage;
use App\Models\TelegramCommand;
use App\Services\TuyaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BotAdmin
{
    private string $token;

    public function __construct(
        private readonly int|string $chatId,
        private readonly ?int $messageId = null
    ) {
        $this->token = config('telegram.bot_token');
    }

    public function handle(string $text, bool $isCallback = false): void
    {
        $waitingKey = "bot_admin_waiting_{$this->chatId}";
        $waiting = Cache::get($waitingKey);

        if ($waiting && ! str_starts_with($text, '/') && ! $isCallback) {
            Cache::forget($waitingKey);
            $this->handleWaitingInput($waiting, $text);

            return;
        }

        if (! str_starts_with($text, '/') && ! $isCallback) {
            return;
        }

        $parts = explode(' ', trim($text), 2);
        $command = strtolower(ltrim(explode('@', $parts[0])[0], '/'));
        $args = trim($parts[1] ?? '');

        match ($command) {
            'start', 'status', 'menu' => $this->showStatus(),
            'ai' => $this->toggleAi(),
            'memode' => $this->toggleGlobalMeMode(),
            'hours' => $this->handleHours($args),
            'fallback' => $this->setFallback($args),
            'debounce' => $this->setDebounce($args),
            'cmds', 'commands' => $this->listCommands(),
            'addcmd' => $this->addCommand($args),
            'delcmd' => $this->deleteCommand($args),
            'connections' => $this->listConnections(),
            'langlist' => $this->listChatLanguages(),
            'langset' => $this->setChatLanguage($args),
            'langreset' => $this->resetChatLanguage($args),
            'address' => $this->setAddressForm($args),
            'chat' => $this->showChatSettings($args),
            'chatai' => $this->toggleChatAi($args),
            'chatlearn' => $this->toggleChatLearning($args),
            'stats' => $this->showStats(),
            'prompt' => $this->startWaiting('ai_prompt', "✍️ Yangi <b>AI instructions</b> yuboring:\n\n<i>Bekor qilish: /cancel</i>"),
            'meprompt' => $this->startWaiting('me_prompt', "🎭 Yangi <b>Me Mode instructions</b> yuboring:\n\n<i>Bekor qilish: /cancel</i>"),
            'cancel' => $this->cancelWaiting(),
            'smarthome', 'home' => $this->showSmartHome(),
            default => str_starts_with($command, 'smarthome_') ? $this->handleSmartHomeCallback($command) : null,
        };
    }

    private function handleWaitingInput(string $type, string $text): void
    {
        match ($type) {
            'ai_prompt' => $this->saveAiPrompt($text),
            'me_prompt' => $this->saveMePrompt($text),
            default => null,
        };
    }

    private function showStatus(): void
    {
        $aiEnabled = BotSetting::get('ai_enabled', '1') === '1';
        $meModeGlobal = BotSetting::get('me_mode_global', '0') === '1';
        $hoursEnabled = BotSetting::get('working_hours_enabled', '0') === '1';
        $hoursStart = BotSetting::get('working_hours_start', '09:00');
        $hoursEnd = BotSetting::get('working_hours_end', '18:00');
        $timezone = BotSetting::get('working_hours_timezone', 'Asia/Tashkent');
        $debounce = BotSetting::get('debounce_seconds', '3');
        $cmdCount = TelegramCommand::count();
        $connCount = BusinessConnection::where('is_enabled', true)->count();

        $buttons = [
            [
                ['text' => ($aiEnabled ? '🔴 AI Off' : '🟢 AI On'), 'callback_data' => 'ai'],
                ['text' => ($meModeGlobal ? '⚫ Me Off' : '🟢 Me On'), 'callback_data' => 'memode'],
            ],
            [
                ['text' => '⏰ Ish vaqti', 'callback_data' => 'hours'],
                ['text' => '⏱ Debounce', 'callback_data' => 'debounce'],
            ],
            [
                ['text' => '📊 Statistika', 'callback_data' => 'stats'],
                ['text' => '📝 Buyruqlar', 'callback_data' => 'cmds'],
            ],
            [
                ['text' => '🔗 Ulanishlar', 'callback_data' => 'connections'],
                ['text' => '🌐 Tillari', 'callback_data' => 'langlist'],
            ],
            [
                ['text' => '✍️ AI Prompt', 'callback_data' => 'prompt'],
                ['text' => '🎭 Me Prompt', 'callback_data' => 'meprompt'],
            ],
            [
                ['text' => '🏠 Smart Home', 'callback_data' => 'smarthome'],
            ],
            [
                ['text' => '🔄 Yangilash', 'callback_data' => 'status'],
            ],
        ];

        $this->send(
            "📊 <b>Bot holati</b>\n\n"
            .($aiEnabled ? '🟢' : '🔴').' AI: '.($aiEnabled ? 'Yoqiq' : "O'chiq")."\n"
            .($meModeGlobal ? '🟢' : '⚫').' Global Me Mode: '.($meModeGlobal ? 'Yoqiq' : "O'chiq")."\n"
            .($hoursEnabled ? '🟢' : '⚫').' Ish vaqti: '.($hoursEnabled ? "{$hoursStart}–{$hoursEnd} ({$timezone})" : "O'chiq")."\n"
            ."⏱ Debounce: {$debounce}s\n"
            ."📝 Buyruqlar: {$cmdCount} ta\n"
            ."🔗 Ulanishlar: {$connCount} ta faol",
            ['inline_keyboard' => $buttons]
        );
    }

    private function showStats(): void
    {
        $today = now()->startOfDay();

        $aiReplies = ChatMessage::where('role', 'assistant')
            ->where('is_manual', false)
            ->where('created_at', '>=', $today)
            ->count();

        $manualReplies = ChatMessage::where('role', 'assistant')
            ->where('is_manual', true)
            ->where('created_at', '>=', $today)
            ->count();

        $userMessages = ChatMessage::where('role', 'user')
            ->where('created_at', '>=', $today)
            ->count();

        $voiceMessages = ChatMessage::where('role', 'user')
            ->where('content', 'like', '%🎤 [Ovozli xabar%')
            ->where('created_at', '>=', $today)
            ->count();

        $topLangs = ChatLanguage::select('language_code', DB::raw('count(*) as count'))
            ->groupBy('language_code')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        $langStats = $topLangs->map(fn ($l) => strtoupper($l->language_code).": {$l->count} ta")->implode("\n");

        $buttons = [
            [['text' => '🔄 Yangilash', 'callback_data' => 'stats']],
            [['text' => '🔙 Orqaga', 'callback_data' => 'status']],
        ];

        $this->send(
            "📊 <b>Bugungi statistika</b>\n\n"
            ."🤖 AI javoblari: <b>{$aiReplies}</b>\n"
            ."👤 Sizning javoblaringiz: <b>{$manualReplies}</b>\n"
            ."📩 Foydalanuvchi xabarlari: <b>{$userMessages}</b>\n"
            ."🎤 Ovozli xabarlar: <b>{$voiceMessages}</b>\n\n"
            ."🌐 <b>Top tillar:</b>\n".($langStats ?: "Ma'lumot yo'q"),
            ['inline_keyboard' => $buttons]
        );
    }

    private function toggleAi(): void
    {
        $current = BotSetting::get('ai_enabled', '1') === '1';
        BotSetting::set('ai_enabled', $current ? '0' : '1');
        $this->showStatus();
    }

    private function toggleGlobalMeMode(): void
    {
        $current = BotSetting::get('me_mode_global', '0') === '1';
        BotSetting::set('me_mode_global', $current ? '0' : '1');
        $this->showStatus();
    }

    private function handleHours(string $args): void
    {
        if ($args === 'on') {
            BotSetting::set('working_hours_enabled', '1');
            $this->handleHours('');

            return;
        }

        if ($args === 'off') {
            BotSetting::set('working_hours_enabled', '0');
            $this->handleHours('');

            return;
        }

        $parts = explode(' ', $args);
        if (count($parts) >= 2
            && preg_match('/^\d{2}:\d{2}$/', $parts[0])
            && preg_match('/^\d{2}:\d{2}$/', $parts[1])
        ) {
            BotSetting::set('working_hours_start', $parts[0]);
            BotSetting::set('working_hours_end', $parts[1]);
            $this->handleHours('');

            return;
        }

        $enabled = BotSetting::get('working_hours_enabled', '0') === '1';
        $start = BotSetting::get('working_hours_start', '09:00');
        $end = BotSetting::get('working_hours_end', '18:00');
        $tz = BotSetting::get('working_hours_timezone', 'Asia/Tashkent');
        $msg = BotSetting::get('working_hours_message', '');

        $buttons = [
            [
                ['text' => ($enabled ? '⚫ O\'chirish' : '🟢 Yoqish'), 'callback_data' => 'hours '.($enabled ? 'off' : 'on')],
            ],
            [
                ['text' => '🔙 Orqaga', 'callback_data' => 'status'],
            ],
        ];

        $this->send(
            "⏰ <b>Ish vaqti</b>\n\n"
            .'Holat: '.($enabled ? '🟢 Yoqiq' : "⚫ O'chiq")."\n"
            ."Vaqt: {$start}–{$end}\n"
            ."Timezone: {$tz}\n"
            ."Tashqari javob: {$msg}\n\n"
            ."<i>Vaqtni o'zgartirish: /hours 09:00 18:00</i>",
            ['inline_keyboard' => $buttons]
        );
    }

    private function setFallback(string $text): void
    {
        if (empty($text)) {
            $current = BotSetting::get('fallback_reply', '');
            $this->send(
                "💬 Hozirgi fallback:\n<i>{$current}</i>\n\n<i>O'zgartirish: /fallback Yangi matn</i>",
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]]]
            );

            return;
        }

        BotSetting::set('fallback_reply', $text);
        $this->send(
            "💬 Fallback saqlandi:\n<i>{$text}</i>",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]]]
        );
    }

    private function setDebounce(string $args): void
    {
        if (! is_numeric($args) || (int) $args < 1 || (int) $args > 30) {
            $current = BotSetting::get('debounce_seconds', '3');
            $this->send(
                "⏱ Hozirgi debounce: <b>{$current}s</b>\n\n<i>O'zgartirish: /debounce 5</i>",
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]]]
            );

            return;
        }

        BotSetting::set('debounce_seconds', (string) (int) $args);
        $this->send(
            "⏱ Debounce: <b>{$args}s</b>",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]]]
        );
    }

    private function listCommands(): void
    {
        $commands = TelegramCommand::orderBy('command')->get();

        $buttons = [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]];

        if ($commands->isEmpty()) {
            $this->send(
                "📝 Buyruqlar yo'q.\n\n<i>Qo'shish: /addcmd buyruq javob</i>",
                ['inline_keyboard' => $buttons]
            );

            return;
        }

        $list = $commands->map(fn ($c) => "/{$c->command} — {$c->reply}")->implode("\n");
        $this->send(
            "📝 <b>Buyruqlar ({$commands->count()} ta)</b>\n\n{$list}\n\n<i>O'chirish: /delcmd nom</i>",
            ['inline_keyboard' => $buttons]
        );
    }

    private function addCommand(string $args): void
    {
        $parts = explode(' ', $args, 2);

        if (count($parts) < 2 || empty($parts[0]) || empty($parts[1])) {
            $this->send(
                '❌ Format: /addcmd <b>buyruq</b> javob matni',
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'cmds']]]]
            );

            return;
        }

        $command = strtolower(preg_replace('/[^a-z0-9_]/', '', $parts[0]));
        $reply = $parts[1];

        TelegramCommand::updateOrCreate(['command' => $command], ['reply' => $reply]);
        $this->send(
            "✅ <b>/{$command}</b> qo'shildi:\n<i>{$reply}</i>",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'cmds']]]]
        );
    }

    private function deleteCommand(string $args): void
    {
        $command = strtolower(trim($args));

        if (empty($command)) {
            $this->send(
                '❌ Format: /delcmd <b>buyruq_nomi</b>',
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'cmds']]]]
            );

            return;
        }

        $deleted = TelegramCommand::where('command', $command)->delete();
        $this->send(
            $deleted ? "✅ <b>/{$command}</b> o'chirildi" : "⚠️ /{$command} topilmadi",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'cmds']]]]
        );
    }

    private function listConnections(): void
    {
        $connections = BusinessConnection::all();

        $buttons = [[['text' => '🔙 Orqaga', 'callback_data' => 'status']]];

        if ($connections->isEmpty()) {
            $this->send("🔗 Business ulanishlar yo'q.", ['inline_keyboard' => $buttons]);

            return;
        }

        $list = $connections->map(fn ($c) => ($c->is_enabled ? '🟢' : '🔴')
            ." <code>{$c->connection_id}</code>\n"
            .'   Javob huquqi: '.($c->can_reply ? 'ha' : "yo'q")
        )->implode("\n\n");

        $this->send("🔗 <b>Business Ulanishlar</b>\n\n{$list}", ['inline_keyboard' => $buttons]);
    }

    private function listChatLanguages(): void
    {
        $langs = ChatLanguage::orderByDesc('updated_at')->limit(15)->get();

        $buttons = [];
        foreach ($langs as $lang) {
            $name = $lang->chat_name ?: "Chat {$lang->chat_id}";
            $buttons[] = [['text' => "👤 {$name}", 'callback_data' => "chat {$lang->chat_id}"]];
        }

        $buttons[] = [['text' => '🔙 Orqaga', 'callback_data' => 'status']];

        if ($langs->isEmpty()) {
            $this->send('🌐 Hali hech qanday chat tili aniqlanmagan.', ['inline_keyboard' => [['text' => '🔙 Orqaga', 'callback_data' => 'status']]]);

            return;
        }

        $this->send(
            "🌐 <b>Chatlar ro'yxati (oxirgi 15)</b>\n\nBatafsil sozlash uchun chat ustiga bosing:",
            ['inline_keyboard' => $buttons]
        );
    }

    private function showChatSettings(string $args): void
    {
        $chatId = (int) trim($args);
        $chat = ChatLanguage::forChat($chatId);

        if (! $chat) {
            $this->send('⚠️ Chat topilmadi.', ['inline_keyboard' => [[['text' => '🔙 Ro\'yxatga', 'callback_data' => 'langlist']]]]);

            return;
        }

        $aiEnabled = $chat->ai_enabled ?? true;
        $learningEnabled = $chat->learning_enabled ?? true;

        $buttons = [
            [
                ['text' => ($aiEnabled ? '🔴 Chat AI Off' : '🟢 Chat AI On'), 'callback_data' => "chatai {$chatId}"],
                ['text' => ($learningEnabled ? '⚫ Learn Off' : '🟢 Learn On'), 'callback_data' => "chatlearn {$chatId}"],
            ],
            [
                ['text' => '🌐 Til: '.strtoupper($chat->language_code), 'callback_data' => "chat_lang_menu {$chatId}"], // Placeholder
                ['text' => '👤 '.ucfirst($chat->address_form), 'callback_data' => "chat_address_menu {$chatId}"], // Placeholder
            ],
            [
                ['text' => '🔙 Ro\'yxatga', 'callback_data' => 'langlist'],
            ],
        ];

        $this->send(
            "👤 <b>Chat sozlamalari</b>\n\n"
            ."Nomi: <b>{$chat->chat_name}</b>\n"
            ."ID: <code>{$chat->chat_id}</code>\n"
            ."Tili: {$chat->language_name} [{$chat->language_code}]\n"
            ."Murojaat: {$chat->address_form}\n\n"
            .($aiEnabled ? '🟢' : '🔴').' AI Javob: '.($aiEnabled ? 'Yoqiq' : 'O\'chiq')."\n"
            .($learningEnabled ? '🟢' : '⚫')." O'rganish: ".($learningEnabled ? 'Yoqiq' : 'O\'chiq'),
            ['inline_keyboard' => $buttons]
        );
    }

    private function toggleChatAi(string $args): void
    {
        $chatId = (int) trim($args);
        $chat = ChatLanguage::forChat($chatId);
        if ($chat) {
            $chat->update(['ai_enabled' => ! ($chat->ai_enabled ?? true)]);
            $this->showChatSettings((string) $chatId);
        }
    }

    private function toggleChatLearning(string $args): void
    {
        $chatId = (int) trim($args);
        $chat = ChatLanguage::forChat($chatId);
        if ($chat) {
            $chat->update(['learning_enabled' => ! ($chat->learning_enabled ?? true)]);
            $this->showChatSettings((string) $chatId);
        }
    }

    private function setChatLanguage(string $args): void
    {
        $parts = explode(' ', trim($args), 2);

        if (count($parts) < 2 || ! is_numeric($parts[0])) {
            $this->send(
                '❌ Format: /langset <b>chat_id</b> <b>kod</b>',
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
            );

            return;
        }

        $chatId = (int) $parts[0];
        $code = strtolower(trim($parts[1]));

        $names = ['uz' => "O'zbek", 'kk' => 'Qazaq', 'ru' => 'Русский', 'en' => 'English', 'tr' => 'Türkçe', 'ar' => 'العربية'];

        if (! isset($names[$code])) {
            $this->send(
                "❌ Noma'lum til kodi: <code>{$code}</code>",
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
            );

            return;
        }

        ChatLanguage::setForChat($chatId, $code, $names[$code], true);
        $this->send(
            "✅ <code>{$chatId}</code> → <b>{$names[$code]}</b> [{$code}]",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
        );
    }

    private function resetChatLanguage(string $args): void
    {
        $chatId = (int) trim($args);

        if (! $chatId) {
            $this->send(
                '❌ Format: /langreset <b>chat_id</b>',
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
            );

            return;
        }

        ChatLanguage::where('chat_id', $chatId)->update(['is_manual' => false]);
        $this->send(
            "⟳ <code>{$chatId}</code> avtoga qaytarildi.",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
        );
    }

    private function setAddressForm(string $args): void
    {
        $parts = explode(' ', trim($args), 2);

        if (count($parts) < 2 || ! is_numeric($parts[0]) || ! in_array($parts[1], ['siz', 'sen'])) {
            $this->send(
                '❌ Format: /address <b>chat_id</b> <b>siz|sen</b>',
                ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
            );

            return;
        }

        $chatId = (int) $parts[0];
        $form = $parts[1];

        ChatLanguage::where('chat_id', $chatId)->update(['address_form' => $form]);
        $this->send(
            "✅ <code>{$chatId}</code> → <b>{$form}</b>",
            ['inline_keyboard' => [[['text' => '🔙 Orqaga', 'callback_data' => 'langlist']]]]
        );
    }

    private function startWaiting(string $type, string $prompt): void
    {
        Cache::put("bot_admin_waiting_{$this->chatId}", $type, now()->addMinutes(10));
        $this->send($prompt, ['inline_keyboard' => [[['text' => '❌ Bekor qilish', 'callback_data' => 'cancel']]]]);
    }

    private function cancelWaiting(): void
    {
        Cache::forget("bot_admin_waiting_{$this->chatId}");
        $this->send('❌ Bekor qilindi.', ['inline_keyboard' => [[['text' => '🔙 Menyu', 'callback_data' => 'status']]]]);
    }

    private function showSmartHome(): void
    {
        $tuya = app(TuyaService::class);
        $deviceIds = json_decode(BotSetting::get('tuya_device_ids', '[]'), true) ?? [];

        if (empty($deviceIds)) {
            $this->send(
                "🏠 <b>Smart Home</b>\n\n⚠️ Qurilmalar sozlanmagan.\nAdmin paneldan Device ID qo'shing.",
                ['inline_keyboard' => [[['text' => '🔙 Menyu', 'callback_data' => 'status']]]]
            );

            return;
        }

        $lines = [];
        $buttons = [];

        foreach ($deviceIds as $deviceId) {
            $info = $tuya->getDeviceInfo($deviceId);
            $switches = $tuya->getSwitches($deviceId);
            $name = $info['name'] ?? $deviceId;
            $online = $info['online'] ?? null;

            $onlineIcon = $online === true ? '🟢' : ($online === false ? '🔴' : '⚪');
            $lines[] = "{$onlineIcon} <b>{$name}</b>";

            if ($switches === null) {
                $lines[] = '  ⚠️ API xatolik';
            } else {
                foreach ($switches as $switch) {
                    $icon = $switch['value'] ? '💡' : '🌑';
                    $status = $switch['value'] ? 'Yoqiq' : "O'chiq";
                    $lines[] = "  {$icon} {$switch['label']}: {$status}";

                    $btnIcon = $switch['value'] ? '🔴' : '🟢';
                    $btnLabel = $switch['value'] ? "O'chir" : 'Yoq';
                    $callbackData = 'smarthome_'.$deviceId.'_'.$switch['code'];
                    $buttons[] = [['text' => "{$btnIcon} {$name} — {$switch['label']} {$btnLabel}", 'callback_data' => $callbackData]];
                }
            }
        }

        $buttons[] = [['text' => '🔄 Yangilash', 'callback_data' => 'smarthome']];
        $buttons[] = [['text' => '🔙 Menyu', 'callback_data' => 'status']];

        $this->send(
            "🏠 <b>Smart Home</b>\n\n".implode("\n", $lines),
            ['inline_keyboard' => $buttons]
        );
    }

    private function handleSmartHomeCallback(string $command): void
    {
        // Format: smarthome_{deviceId}_{switchCode}
        // switchCode is always "switch_N" — split on last underscore before "switch"
        $withoutPrefix = substr($command, strlen('smarthome_'));

        // Find "switch_" from the right side
        $switchPos = strrpos($withoutPrefix, '_switch_');

        if ($switchPos === false) {
            return;
        }

        $deviceId = substr($withoutPrefix, 0, $switchPos);
        $switchCode = substr($withoutPrefix, $switchPos + 1);

        $tuya = app(TuyaService::class);
        $newState = $tuya->toggleSwitch($deviceId, $switchCode);

        if ($newState === null) {
            $this->send(
                '⚠️ Qurilmaga ulanishda xatolik.',
                ['inline_keyboard' => [[['text' => '🔙 Smart Home', 'callback_data' => 'smarthome']]]]
            );

            return;
        }

        $this->showSmartHome();
    }

    private function saveAiPrompt(string $text): void
    {
        BotSetting::set('ai_instructions', $text);
        $this->send('✅ AI instructions saqlandi.', ['inline_keyboard' => [[['text' => '🔙 Menyu', 'callback_data' => 'status']]]]);
    }

    private function saveMePrompt(string $text): void
    {
        BotSetting::set('me_mode_instructions', $text);
        $this->send('✅ Me Mode instructions saqlandi.', ['inline_keyboard' => [[['text' => '🔙 Menyu', 'callback_data' => 'status']]]]);
    }

    private function send(string $text, ?array $replyMarkup = null): void
    {
        $endpoint = $this->messageId ? 'editMessageText' : 'sendMessage';
        $params = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($this->messageId) {
            $params['message_id'] = $this->messageId;
        }

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        Http::post("https://api.telegram.org/bot{$this->token}/{$endpoint}", $params);
    }
}
