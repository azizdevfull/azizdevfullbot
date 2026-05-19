<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Models\BusinessConnection;
use App\Models\TelegramCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function login(): View
    {
        return view('admin.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required']);

        if ($request->input('password') !== config('admin.password')) {
            return back()->withErrors(['password' => 'Parol noto\'g\'ri.']);
        }

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
            'connections' => BusinessConnection::all(),
            'commands' => TelegramCommand::orderBy('command')->get(),
            'settings' => [
                'ai_instructions' => BotSetting::get('ai_instructions', config('telegram.ai_instructions')),
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

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ai_instructions' => 'required|string',
            'fallback_reply' => 'required|string',
            'debounce_seconds' => 'required|integer|min:1|max:30',
            'working_hours_enabled' => 'nullable|in:1',
            'working_hours_start' => 'required|date_format:H:i',
            'working_hours_end' => 'required|date_format:H:i',
            'working_hours_timezone' => 'required|timezone',
            'working_hours_message' => 'required|string',
        ]);

        BotSetting::set('ai_instructions', $data['ai_instructions']);
        BotSetting::set('fallback_reply', $data['fallback_reply']);
        BotSetting::set('debounce_seconds', $data['debounce_seconds']);
        BotSetting::set('working_hours_enabled', isset($data['working_hours_enabled']) ? '1' : '0');
        BotSetting::set('working_hours_start', $data['working_hours_start']);
        BotSetting::set('working_hours_end', $data['working_hours_end']);
        BotSetting::set('working_hours_timezone', $data['working_hours_timezone']);
        BotSetting::set('working_hours_message', $data['working_hours_message']);

        return back()->with('success', 'Sozlamalar saqlandi.');
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
}
