<?php

namespace App\Telegram;

use App\Models\BotSetting;
use App\Models\BusinessConnection;
use App\Models\ChatLanguage;
use App\Models\TelegramCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BotAdmin
{
    private string $token;

    public function __construct(private readonly int|string $chatId)
    {
        $this->token = config('telegram.bot_token');
    }

    public function handle(string $text): void
    {
        $waitingKey = "bot_admin_waiting_{$this->chatId}";
        $waiting = Cache::get($waitingKey);

        if ($waiting && ! str_starts_with($text, '/')) {
            Cache::forget($waitingKey);
            $this->handleWaitingInput($waiting, $text);

            return;
        }

        if (! str_starts_with($text, '/')) {
            return;
        }

        $parts = explode(' ', trim($text), 2);
        $command = strtolower(ltrim(explode('@', $parts[0])[0], '/'));
        $args = trim($parts[1] ?? '');

        match ($command) {
            'start', 'status' => $this->showStatus(),
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
            'prompt' => $this->startWaiting('ai_prompt', "✍️ Yangi <b>AI instructions</b> yuboring:\n\n<i>Bekor qilish: /cancel</i>"),
            'meprompt' => $this->startWaiting('me_prompt', "🎭 Yangi <b>Me Mode instructions</b> yuboring:\n\n<i>Bekor qilish: /cancel</i>"),
            'cancel' => $this->cancelWaiting(),
            default => null,
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

        $this->send(
            "📊 <b>Bot holati</b>\n\n"
            .($aiEnabled ? '🟢' : '🔴').' AI: '.($aiEnabled ? 'Yoqiq' : "O'chiq")."\n"
            .($meModeGlobal ? '🟢' : '⚫').' Global Me Mode: '.($meModeGlobal ? 'Yoqiq' : "O'chiq")."\n"
            .($hoursEnabled ? '🟢' : '⚫').' Ish vaqti: '.($hoursEnabled ? "{$hoursStart}–{$hoursEnd} ({$timezone})" : "O'chiq")."\n"
            ."⏱ Debounce: {$debounce}s\n"
            ."📝 Buyruqlar: {$cmdCount} ta\n"
            ."🔗 Ulanishlar: {$connCount} ta faol\n\n"
            ."<b>Buyruqlar:</b>\n"
            ."/ai — AI on/off\n"
            ."/memode — Global Me Mode\n"
            ."/hours — Ish vaqti\n"
            ."/cmds — Buyruqlar ro'yxati\n"
            ."/addcmd — Buyruq qo'shish\n"
            ."/delcmd — Buyruq o'chirish\n"
            ."/prompt — AI instructions\n"
            ."/meprompt — Me Mode instructions\n"
            ."/fallback — Fallback javob\n"
            ."/debounce — Debounce soniyasi\n"
            ."/connections — Business ulanishlar\n"
            ."/langlist — Chat tillari\n"
            ."/langset {id} {kod} — Til belgilash\n"
            ."/langreset {id} — Avtoga qaytarish\n"
            .'/address {id} siz|sen — Murojat shakli'
        );
    }

    private function toggleAi(): void
    {
        $current = BotSetting::get('ai_enabled', '1') === '1';
        BotSetting::set('ai_enabled', $current ? '0' : '1');
        $this->send($current ? "🔴 AI <b>o'chirildi</b>" : '🟢 AI <b>yoqildi</b>');
    }

    private function toggleGlobalMeMode(): void
    {
        $current = BotSetting::get('me_mode_global', '0') === '1';
        BotSetting::set('me_mode_global', $current ? '0' : '1');
        $this->send($current
            ? "⚫ Global Me Mode <b>o'chirildi</b>"
            : "🟢 Global Me Mode <b>yoqildi</b> — barcha chatlarda siz bo'lib yozadi.");
    }

    private function handleHours(string $args): void
    {
        if ($args === 'on') {
            BotSetting::set('working_hours_enabled', '1');
            $this->send('🟢 Ish vaqti <b>yoqildi</b>');

            return;
        }

        if ($args === 'off') {
            BotSetting::set('working_hours_enabled', '0');
            $this->send("⚫ Ish vaqti <b>o'chirildi</b>");

            return;
        }

        $parts = explode(' ', $args);
        if (count($parts) >= 2
            && preg_match('/^\d{2}:\d{2}$/', $parts[0])
            && preg_match('/^\d{2}:\d{2}$/', $parts[1])
        ) {
            BotSetting::set('working_hours_start', $parts[0]);
            BotSetting::set('working_hours_end', $parts[1]);
            $this->send("⏰ Ish vaqti: <b>{$parts[0]}–{$parts[1]}</b>");

            return;
        }

        $enabled = BotSetting::get('working_hours_enabled', '0') === '1';
        $start = BotSetting::get('working_hours_start', '09:00');
        $end = BotSetting::get('working_hours_end', '18:00');
        $tz = BotSetting::get('working_hours_timezone', 'Asia/Tashkent');
        $msg = BotSetting::get('working_hours_message', '');

        $this->send(
            "⏰ <b>Ish vaqti</b>\n\n"
            .'Holat: '.($enabled ? '🟢 Yoqiq' : "⚫ O'chiq")."\n"
            ."Vaqt: {$start}–{$end}\n"
            ."Timezone: {$tz}\n"
            ."Tashqari javob: {$msg}\n\n"
            ."<i>/hours on|off\n/hours 09:00 18:00</i>"
        );
    }

    private function setFallback(string $text): void
    {
        if (empty($text)) {
            $current = BotSetting::get('fallback_reply', '');
            $this->send("💬 Hozirgi fallback:\n<i>{$current}</i>\n\n<i>/fallback Yangi matn</i>");

            return;
        }

        BotSetting::set('fallback_reply', $text);
        $this->send("💬 Fallback saqlandi:\n<i>{$text}</i>");
    }

    private function setDebounce(string $args): void
    {
        if (! is_numeric($args) || (int) $args < 1 || (int) $args > 30) {
            $current = BotSetting::get('debounce_seconds', '3');
            $this->send("⏱ Hozirgi debounce: <b>{$current}s</b>\n\n<i>/debounce 5</i>");

            return;
        }

        BotSetting::set('debounce_seconds', (string) (int) $args);
        $this->send("⏱ Debounce: <b>{$args}s</b>");
    }

    private function listCommands(): void
    {
        $commands = TelegramCommand::orderBy('command')->get();

        if ($commands->isEmpty()) {
            $this->send("📝 Buyruqlar yo'q.\n\n<i>/addcmd salom Salom! 👋</i>");

            return;
        }

        $list = $commands->map(fn ($c) => "/{$c->command} — {$c->reply}")->implode("\n");
        $this->send("📝 <b>Buyruqlar ({$commands->count()} ta)</b>\n\n{$list}\n\n<i>O'chirish: /delcmd buyruq_nomi</i>");
    }

    private function addCommand(string $args): void
    {
        $parts = explode(' ', $args, 2);

        if (count($parts) < 2 || empty($parts[0]) || empty($parts[1])) {
            $this->send("❌ Format: /addcmd <b>buyruq</b> javob matni\n\nMisol: /addcmd salom Salom! 👋");

            return;
        }

        $command = strtolower(preg_replace('/[^a-z0-9_]/', '', $parts[0]));
        $reply = $parts[1];

        TelegramCommand::updateOrCreate(['command' => $command], ['reply' => $reply]);
        $this->send("✅ <b>/{$command}</b> qo'shildi:\n<i>{$reply}</i>");
    }

    private function deleteCommand(string $args): void
    {
        $command = strtolower(trim($args));

        if (empty($command)) {
            $this->send('❌ Format: /delcmd <b>buyruq_nomi</b>');

            return;
        }

        $deleted = TelegramCommand::where('command', $command)->delete();
        $this->send($deleted ? "✅ <b>/{$command}</b> o'chirildi" : "⚠️ /{$command} topilmadi");
    }

    private function listConnections(): void
    {
        $connections = BusinessConnection::all();

        if ($connections->isEmpty()) {
            $this->send("🔗 Business ulanishlar yo'q.");

            return;
        }

        $list = $connections->map(fn ($c) => ($c->is_enabled ? '🟢' : '🔴')
            ." <code>{$c->connection_id}</code>\n"
            .'   Javob huquqi: '.($c->can_reply ? 'ha' : "yo'q")
        )->implode("\n\n");

        $this->send("🔗 <b>Business Ulanishlar</b>\n\n{$list}");
    }

    private function listChatLanguages(): void
    {
        $langs = ChatLanguage::orderByDesc('updated_at')->get();

        if ($langs->isEmpty()) {
            $this->send('🌐 Hali hech qanday chat tili aniqlanmagan.');

            return;
        }

        $list = $langs->map(fn ($l) => ($l->is_manual ? '✎' : '⟳')
            ." <b>{$l->chat_name}</b> (<code>{$l->chat_id}</code>)"
            ."\n   → {$l->language_name} [{$l->language_code}]"
        )->implode("\n\n");

        $this->send("🌐 <b>Chat Tillari</b>\n\n{$list}\n\n<i>/langset {chat_id} kk\n/langreset {chat_id}</i>");
    }

    private function setChatLanguage(string $args): void
    {
        $parts = explode(' ', trim($args), 2);

        if (count($parts) < 2 || ! is_numeric($parts[0])) {
            $this->send("❌ Format: /langset <b>chat_id</b> <b>kod</b>\n\nKodlar: uz, kk, ru, en, tr, ar");

            return;
        }

        $chatId = (int) $parts[0];
        $code = strtolower(trim($parts[1]));

        $names = ['uz' => "O'zbek", 'kk' => 'Qazaq', 'ru' => 'Русский', 'en' => 'English', 'tr' => 'Türkçe', 'ar' => 'العربية'];

        if (! isset($names[$code])) {
            $this->send("❌ Noma'lum til kodi: <code>{$code}</code>\n\nMavjud: ".implode(', ', array_keys($names)));

            return;
        }

        ChatLanguage::setForChat($chatId, $code, $names[$code], true);
        $this->send("✅ <code>{$chatId}</code> → <b>{$names[$code]}</b> [{$code}] (qo'lda)");
    }

    private function resetChatLanguage(string $args): void
    {
        $chatId = (int) trim($args);

        if (! $chatId) {
            $this->send('❌ Format: /langreset <b>chat_id</b>');

            return;
        }

        ChatLanguage::where('chat_id', $chatId)->update(['is_manual' => false]);
        $this->send("⟳ <code>{$chatId}</code> avtomatik aniqlanishga qaytarildi.");
    }

    private function setAddressForm(string $args): void
    {
        $parts = explode(' ', trim($args), 2);

        if (count($parts) < 2 || ! is_numeric($parts[0]) || ! in_array($parts[1], ['siz', 'sen'])) {
            $this->send('❌ Format: /address <b>chat_id</b> <b>siz|sen</b>');

            return;
        }

        $chatId = (int) $parts[0];
        $form = $parts[1];

        ChatLanguage::where('chat_id', $chatId)->update(['address_form' => $form]);
        $this->send("✅ <code>{$chatId}</code> → murojat shakli: <b>{$form}</b>");
    }

    private function startWaiting(string $type, string $prompt): void
    {
        Cache::put("bot_admin_waiting_{$this->chatId}", $type, now()->addMinutes(10));
        $this->send($prompt);
    }

    private function cancelWaiting(): void
    {
        Cache::forget("bot_admin_waiting_{$this->chatId}");
        $this->send('❌ Bekor qilindi.');
    }

    private function saveAiPrompt(string $text): void
    {
        BotSetting::set('ai_instructions', $text);
        $this->send('✅ AI instructions saqlandi.');
    }

    private function saveMePrompt(string $text): void
    {
        BotSetting::set('me_mode_instructions', $text);
        $this->send('✅ Me Mode instructions saqlandi.');
    }

    private function send(string $text): void
    {
        Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
