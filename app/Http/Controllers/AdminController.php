<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Models\BusinessConnection;
use App\Models\ChatLanguage;
use App\Models\ChatMessage;
use App\Models\Persona;
use App\Models\TelegramCommand;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function login(): View
    {
        return view('admin.login', [
            'otpSent' => session()->has('otp_pending'),
        ]);
    }

    public function postLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $request->session()->put('admin_authenticated', true);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Email yoki parol noto\'g\'ri.']);
    }

    public function requestOtp(Request $request): RedirectResponse
    {
        $chatId = config('admin.telegram_chat_id')
            ?? BusinessConnection::first()?->user_chat_id;

        if (! $chatId) {
            return back()->withErrors(['otp' => 'Admin Telegram chat ID sozlanmagan.']);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('admin_otp', $otp, now()->addMinutes(5));

        $token = config('telegram.bot_token');

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => "🔐 Admin panel kirish kodi:\n\n<b>{$otp}</b>\n\n5 daqiqa amal qiladi.",
            'parse_mode' => 'HTML',
        ]);

        $request->session()->put('otp_pending', true);

        return back();
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $storedOtp = Cache::get('admin_otp');

        if (! $storedOtp || $request->input('otp') !== $storedOtp) {
            return back()->withErrors(['otp' => 'Kod noto\'g\'ri yoki muddati o\'tgan.']);
        }

        Cache::forget('admin_otp');
        $request->session()->forget('otp_pending');
        $request->session()->put('admin_authenticated', true);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');

        return redirect()->route('admin.login');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'connectionsCount' => BusinessConnection::count(),
            'chatsCount' => ChatLanguage::count(),
            'commandsCount' => TelegramCommand::count(),
            'aiEnabled' => BotSetting::get('ai_enabled', '1') === '1',
            'workingHoursEnabled' => BotSetting::get('working_hours_enabled', '0') === '1',
        ]);
    }

    public function connections(): View
    {
        return view('admin.connections.index', [
            'connections' => BusinessConnection::all(),
        ]);
    }

    public function commands(): View
    {
        return view('admin.commands.index', [
            'commands' => TelegramCommand::orderBy('command')->get(),
        ]);
    }

    public function settings(): View
    {
        return view('admin.settings.index', [
            'chatLanguages' => ChatLanguage::with('persona')->get()->keyBy('chat_id'),
            'personas' => Persona::all(),
            'settings' => [
                'ai_enabled' => BotSetting::get('ai_enabled', '1'),
                'learning_enabled' => BotSetting::get('learning_enabled', '1'),
                'voice_to_text_enabled' => BotSetting::get('voice_to_text_enabled', '1'),
                'ai_instructions' => BotSetting::get('ai_instructions', config('telegram.ai_instructions')),
                'me_mode_instructions' => BotSetting::get('me_mode_instructions', config('telegram.me_mode_instructions')),
                'fallback_reply' => BotSetting::get('fallback_reply', config('telegram.fallback_reply')),
                'debounce_seconds' => BotSetting::get('debounce_seconds', config('telegram.debounce_seconds', 3)),
                'working_hours_enabled' => BotSetting::get('working_hours_enabled', '0'),
                'working_hours_start' => BotSetting::get('working_hours_start', '09:00'),
                'working_hours_end' => BotSetting::get('working_hours_end', '18:00'),
                'working_hours_timezone' => BotSetting::get('working_hours_timezone', 'Asia/Tashkent'),
                'working_hours_message' => BotSetting::get('working_hours_message', 'Ish vaqtimiz 09:00–18:00. Tez orada javob beraman! ✅'),
            ],
        ]);
    }

    public function chats(): View
    {
        $chats = ChatMessage::query()
            ->select('chat_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->groupBy('chat_id')
            ->orderByDesc('last_message_at')
            ->paginate(15);

        $chatIds = $chats->pluck('chat_id');
        $languages = ChatLanguage::with('persona')->whereIn('chat_id', $chatIds)->get()->keyBy('chat_id');
        $lastMessages = ChatMessage::whereIn('id', function ($query) use ($chatIds) {
            $query->selectRaw('MAX(id)')
                ->from('chat_messages')
                ->whereIn('chat_id', $chatIds)
                ->groupBy('chat_id');
        })->get()->keyBy('chat_id');

        return view('admin.chats.index', [
            'chats' => $chats,
            'languages' => $languages,
            'lastMessages' => $lastMessages,
            'personas' => Persona::all(),
        ]);
    }

    public function chatDetail(int $chatId): View
    {
        $messages = ChatMessage::where('chat_id', $chatId)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse();

        $language = ChatLanguage::with('persona')->where('chat_id', $chatId)->first();

        return view('admin.chats.show', [
            'chatId' => $chatId,
            'messages' => $messages,
            'language' => $language,
            'personas' => Persona::all(),
        ]);
    }

    public function chatMessages(Request $request, int $chatId)
    {
        $beforeId = $request->query('before_id');

        $query = ChatMessage::where('chat_id', $chatId);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages' => $messages->reverse()->values(),
            'has_more' => $messages->count() === 50,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ai_enabled' => 'nullable|in:1',
            'learning_enabled' => 'nullable|in:1',
            'voice_to_text_enabled' => 'nullable|in:1',
            'ai_instructions' => 'required|string',
            'me_mode_instructions' => 'required|string',
            'fallback_reply' => 'required|string',
            'debounce_seconds' => 'required|integer|min:1|max:30',
            'working_hours_enabled' => 'nullable|in:1',
            'working_hours_start' => 'required|date_format:H:i',
            'working_hours_end' => 'required|date_format:H:i',
            'working_hours_timezone' => 'required|timezone',
            'working_hours_message' => 'required|string',
        ]);

        BotSetting::set('ai_enabled', isset($data['ai_enabled']) ? '1' : '0');
        BotSetting::set('learning_enabled', isset($data['learning_enabled']) ? '1' : '0');
        BotSetting::set('voice_to_text_enabled', isset($data['voice_to_text_enabled']) ? '1' : '0');
        BotSetting::set('ai_instructions', $data['ai_instructions']);
        BotSetting::set('me_mode_instructions', $data['me_mode_instructions']);
        BotSetting::set('fallback_reply', $data['fallback_reply']);
        BotSetting::set('debounce_seconds', $data['debounce_seconds']);
        BotSetting::set('working_hours_enabled', isset($data['working_hours_enabled']) ? '1' : '0');
        BotSetting::set('working_hours_start', $data['working_hours_start']);
        BotSetting::set('working_hours_end', $data['working_hours_end']);
        BotSetting::set('working_hours_timezone', $data['working_hours_timezone']);
        BotSetting::set('working_hours_message', $data['working_hours_message']);

        return back()->with('success', 'Sozlamalar saqlandi.');
    }

    public function updateChatStatus(Request $request, int $chatId): RedirectResponse
    {
        $data = $request->validate([
            'ai_enabled' => 'nullable|in:1',
            'learning_enabled' => 'nullable|in:1',
        ]);

        ChatLanguage::where('chat_id', $chatId)->update([
            'ai_enabled' => isset($data['ai_enabled']),
            'learning_enabled' => isset($data['learning_enabled']),
        ]);

        return back()->with('success', 'Chat holati yangilandi.');
    }

    public function toggleConnection(BusinessConnection $connection): RedirectResponse
    {
        $connection->update(['is_enabled' => ! $connection->is_enabled]);

        return back()->with('success', 'Ulanish holati o\'zgartirildi.');
    }

    public function storeCommand(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'command' => ['required', 'string', 'regex:/^[a-z0-9_]+$/'],
            'reply' => 'required|string',
        ]);

        TelegramCommand::updateOrCreate(
            ['command' => $data['command']],
            ['reply' => $data['reply']]
        );

        return back()->with('success', 'Buyruq saqlandi.');
    }

    public function destroyCommand(TelegramCommand $telegramCommand): RedirectResponse
    {
        $telegramCommand->delete();

        return back()->with('success', 'Buyruq o\'chirildi.');
    }

    public function setChatLanguage(Request $request, int $chatId): RedirectResponse
    {
        $data = $request->validate([
            'language_code' => 'required|string|max:10',
            'language_name' => 'required|string|max:50',
        ]);

        $existing = ChatLanguage::forChat($chatId);

        ChatLanguage::setForChat(
            $chatId,
            $data['language_code'],
            $data['language_name'],
            true,
            $existing?->chat_name
        );

        return back()->with('success', 'Chat tili saqlandi.');
    }

    public function resetChatLanguage(int $chatId): RedirectResponse
    {
        ChatLanguage::where('chat_id', $chatId)->update(['is_manual' => false]);

        return back()->with('success', 'Til avtomatik aniqlanishga qaytarildi.');
    }

    public function setAddressForm(Request $request, int $chatId): RedirectResponse
    {
        $data = $request->validate([
            'address_form' => 'required|in:siz,sen',
        ]);

        ChatLanguage::where('chat_id', $chatId)->update(['address_form' => $data['address_form']]);

        return back()->with('success', 'Murojat shakli saqlandi.');
    }

    public function personas(): View
    {
        return view('admin.personas.index', [
            'personas' => Persona::with('histories')->orderBy('name')->get(),
        ]);
    }

    public function storePersona(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_instruction' => 'required|string',
        ]);

        Persona::create($data);

        return back()->with('success', 'Persona saqlandi.');
    }

    public function updatePersona(Request $request, Persona $persona): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_instruction' => 'required|string',
        ]);

        $persona->update($data);

        return back()->with('success', 'Persona yangilandi.');
    }

    public function destroyPersona(Persona $persona): RedirectResponse
    {
        $persona->delete();

        return back()->with('success', 'Persona o\'chirildi.');
    }

    public function profile(): View
    {
        return view('admin.profile', [
            'user' => User::firstOrCreate([], [
                'name' => 'Admin',
                'email' => 'admin@azizdev.uz',
                'password' => 'password', // Will be hashed by model cast
            ]),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = User::firstOrCreate([], [
            'name' => 'Admin',
            'email' => 'admin@azizdev.uz',
            'password' => 'password',
        ]);

        $data = $request->validate(['name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Profil muvaffaqiyatli yangilandi.');
    }

    public function setChatPersona(Request $request, int $chatId): RedirectResponse
    {
        $data = $request->validate([
            'persona_id' => 'nullable|exists:personas,id',
        ]);

        ChatLanguage::where('chat_id', $chatId)->update(['persona_id' => $data['persona_id']]);

        return back()->with('success', 'Chat personasi saqlandi.');
    }

    public function exportChat(Request $request, int $chatId): StreamedResponse
    {
        $language = ChatLanguage::with('persona')->where('chat_id', $chatId)->first();

        if ($request->filled('last_n')) {
            $messages = ChatMessage::where('chat_id', $chatId)
                ->orderByDesc('id')
                ->limit((int) $request->last_n)
                ->get()
                ->reverse()
                ->values();
        } else {
            $query = ChatMessage::where('chat_id', $chatId)->orderBy('id');

            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $messages = $query->get();
        }

        $content = $this->buildExportContent($chatId, $language, $messages);
        $filename = 'chat-export-'.$chatId.'-'.now()->format('Ymd-His').'.txt';

        return response()->streamDownload(fn () => print ($content), $filename, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    /** @param Collection<int, ChatMessage> $messages */
    private function buildExportContent(int $chatId, ?ChatLanguage $language, Collection $messages): string
    {
        $sep = str_repeat('=', 64);
        $personaName = $language?->persona?->name ?? 'Biriktirilmagan';
        $personaInstruction = $language?->persona?->prompt_instruction ?? null;

        $meta = implode("\n", [
            '  Suhbatdosh : '.($language?->chat_name ?? 'Noma\'lum'),
            "  Chat ID    : {$chatId}",
            '  Til        : '.($language?->language_name ?? '—').' ('.($language?->language_code ?? '—').')',
            '  Murojat    : '.($language?->address_form ?? 'siz'),
            "  Persona    : {$personaName}",
            '  Eksport    : '.now()->format('Y-m-d H:i:s'),
            '  Xabarlar   : '.$messages->count().' ta',
        ]);

        $history = '';
        foreach ($messages as $msg) {
            if ($msg->role === 'user') {
                $label = '[USER]    ';
            } elseif ($msg->is_manual) {
                $label = '[AZIZBEK] ';
            } else {
                $label = '[AI]      ';
            }
            $time = $msg->created_at?->format('Y-m-d H:i');
            $history .= "[{$time}] {$label} {$msg->content}\n";
        }

        if (! $history) {
            $history = "(Xabarlar topilmadi)\n";
        }

        $personaSection = $personaInstruction
            ? $personaInstruction
            : '(Persona biriktirilmagan — suhbat tarixini umumiy tahlil qiling)';

        $instruction = $personaInstruction
            ? implode("\n", [
                '1. "[AI]" va "[AZIZBEK]" xabarlarini solishtiring',
                "2. AI noto'g'ri javob bergan joylarni aniqlang",
                "3. Persona qoidalarini shunga mos o'zgartiring",
                '4. FAQAT yangilangan persona matnini qaytaring (boshqa izoh yozmang)',
            ])
            : implode("\n", [
                '1. Suhbat uslubini tahlil qiling',
                '2. Ushbu chat uchun yangi persona yarating',
                '3. FAQAT persona matnini qaytaring (boshqa izoh yozmang)',
            ]);

        return <<<TXT
        {$sep}
        PERSONA YANGILASH UCHUN CHAT EKSPORT
        {$sep}

        VAZIFA:
        Quyidagi suhbat tarixini tahlil qiling.
        "[AI]"      — bot avtomatik yuborganlar (xato bo'lishi mumkin).
        "[AZIZBEK]" — qo'lda yozilgan javoblar (to'g'ri namuna).
        "[USER]"    — suhbatdosh xabarlari.
        AI xatolarini aniqlang, persona qoidalarini shunga mos yangilang.
        Javob faqat yangilangan persona matnidan iborat bo'lsin.

        CHAT MA'LUMOTLARI:
        {$meta}

        {$sep}
        JORIY PERSONA: "{$personaName}"
        {$sep}

        {$personaSection}

        {$sep}
        SUHBAT TARIXI
        {$sep}

        {$history}
        {$sep}
        KO'RSATMA
        {$sep}

        {$instruction}
        TXT;
    }

    public function clearChatMessages(int $chatId): RedirectResponse
    {
        ChatMessage::where('chat_id', $chatId)->delete();

        return redirect()->route('admin.chats.index')->with('success', 'Chat tarixi tozalandi.');
    }

    public function destroyChatMessage(ChatMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Xabar o\'chirildi.');
    }

    public function sendChatMessage(Request $request, int $chatId)
    {
        $request->validate(['message' => 'required|string|max:4096']);

        $text = $request->input('message');
        $token = config('telegram.bot_token');

        $connection = BusinessConnection::where('can_reply', true)
            ->where('is_enabled', true)
            ->first();

        if (! $connection) {
            return response()->json(['error' => 'Faol business connection topilmadi.'], 422);
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'business_connection_id' => $connection->connection_id,
            'parse_mode' => 'HTML',
        ]);

        if (! ($response->json('ok') ?? false)) {
            return response()->json(['error' => 'Telegram xabar yuborishda xatolik.'], 500);
        }

        $message = ChatMessage::create([
            'chat_id' => $chatId,
            'role' => 'assistant',
            'content' => $text,
            'is_manual' => true,
        ]);

        return response()->json([
            'id' => $message->id,
            'content' => $message->content,
            'created_at' => $message->created_at->toISOString(),
        ]);
    }
}
