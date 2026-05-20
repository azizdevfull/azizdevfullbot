@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

{{-- Page header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard</h1>
    <p class="text-sm text-slate-400 mt-1">Telegram bot sozlamalari va boshqaruvi</p>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm card-hover">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $connections->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Ulanishlar</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm card-hover">
        <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $commands->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Buyruqlar</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm card-hover">
        <div class="w-9 h-9 rounded-xl {{ $settings['ai_enabled'] === '1' ? 'bg-emerald-50' : 'bg-slate-100' }} flex items-center justify-center mb-3">
            <svg class="w-5 h-5 {{ $settings['ai_enabled'] === '1' ? 'text-emerald-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $settings['ai_enabled'] === '1' ? 'Yoqiq' : 'O\'chiq' }}</p>
        <p class="text-xs text-slate-400 mt-0.5">AI holati</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm card-hover">
        <div class="w-9 h-9 rounded-xl {{ $settings['working_hours_enabled'] === '1' ? 'bg-amber-50' : 'bg-slate-100' }} flex items-center justify-center mb-3">
            <svg class="w-5 h-5 {{ $settings['working_hours_enabled'] === '1' ? 'text-amber-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $settings['working_hours_enabled'] === '1' ? 'Yoqiq' : 'O\'chiq' }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Ish vaqti</p>
    </div>
</div>

{{-- Business Connections --}}
<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-6 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            </div>
            <h2 class="font-semibold text-slate-800">Business Ulanishlar</h2>
        </div>
        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $connections->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($connections as $connection)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-2 h-2 rounded-full shrink-0 {{ $connection->is_enabled ? 'bg-emerald-400' : 'bg-slate-300' }} shadow-sm"></div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate font-mono">{{ $connection->connection_id }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            User ID: {{ $connection->telegram_user_id }}
                            <span class="mx-1 text-slate-300">·</span>
                            {{ $connection->can_reply ? '✓ Javob bera oladi' : '✗ Javob bera olmaydi' }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.connections.toggle', $connection) }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-4 py-1.5 rounded-xl border transition
                        {{ $connection->is_enabled
                            ? 'border-red-200 text-red-500 bg-red-50 hover:bg-red-100'
                            : 'border-emerald-200 text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }}">
                        {{ $connection->is_enabled ? 'O\'chirish' : 'Yoqish' }}
                    </button>
                </form>
            </div>
        @empty
            <div class="px-6 py-10 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </div>
                <p class="text-sm text-slate-400">Hech qanday ulanish yo'q</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Commands --}}
<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-6 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
            <h2 class="font-semibold text-slate-800">Buyruqlar</h2>
        </div>
        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $commands->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($commands as $cmd)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <code class="shrink-0 text-xs font-mono font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-lg">/{{ $cmd->command }}</code>
                    <p class="text-sm text-slate-500 truncate">{{ $cmd->reply }}</p>
                </div>
                <form method="POST" action="{{ route('admin.commands.destroy', $cmd) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 px-3 py-1.5 rounded-xl transition">
                        O'chirish
                    </button>
                </form>
            </div>
        @empty
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-slate-400">Buyruqlar yo'q</p>
            </div>
        @endforelse
    </div>
    <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Yangi buyruq qo'shish</p>
        <form method="POST" action="{{ route('admin.commands.store') }}" class="flex gap-3 flex-wrap items-start">
            @csrf
            <div class="min-w-36">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">/</span>
                    <input type="text" name="command" placeholder="hello" pattern="[a-z0-9_]+"
                        class="w-full rounded-xl border border-slate-200 bg-white pl-7 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                </div>
                @error('command')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="flex-1 min-w-48">
                <input type="text" name="reply" placeholder="Javob matni..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                @error('reply')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="gradient-btn text-white rounded-xl px-5 py-2.5 text-sm font-semibold transition shadow-md shadow-indigo-200 hover:shadow-indigo-300 hover:scale-[1.02] active:scale-[0.98]">
                Qo'shish
            </button>
        </form>
    </div>
</div>

{{-- Settings Form --}}
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    {{-- Working Hours --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800">Ish Vaqti</h2>
                    <p class="text-xs text-slate-400">Ish vaqtidan tashqari avtomatik javob</p>
                </div>
            </div>
            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="working_hours_enabled" value="1"
                    {{ $settings['working_hours_enabled'] === '1' ? 'checked' : '' }}
                    class="sr-only">
                <div class="toggle-track relative w-11 h-6 bg-slate-200 rounded-full transition-colors duration-200">
                    <div class="toggle-thumb absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                </div>
            </label>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Boshlanish</label>
                <input type="time" name="working_hours_start" value="{{ $settings['working_hours_start'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tugash</label>
                <input type="time" name="working_hours_end" value="{{ $settings['working_hours_end'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Timezone</label>
                <select name="working_hours_timezone"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
                    @foreach (['Asia/Tashkent', 'Europe/Moscow', 'Europe/London', 'America/New_York', 'Asia/Dubai', 'Asia/Almaty'] as $tz)
                        <option value="{{ $tz }}" {{ $settings['working_hours_timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tashqari javob</label>
                <input type="text" name="working_hours_message" value="{{ $settings['working_hours_message'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
            </div>
        </div>
    </div>

    {{-- Bot & AI Settings --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800">AI & Bot Sozlamalari</h2>
                    <p class="text-xs text-slate-400">Gemini AI va bot xulq-atvori</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500">AI</span>
                <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="ai_enabled" value="1"
                        {{ $settings['ai_enabled'] === '1' ? 'checked' : '' }}
                        class="sr-only">
                    <div class="toggle-track relative w-11 h-6 bg-slate-200 rounded-full transition-colors duration-200">
                        <div class="toggle-thumb absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                    </div>
                </label>
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Fallback javob</label>
                    <input type="text" name="fallback_reply" value="{{ $settings['fallback_reply'] }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
                    @error('fallback_reply')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Debounce (soniya)</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="debounce_seconds" value="{{ $settings['debounce_seconds'] }}" min="1" max="30"
                            class="w-28 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
                        <span class="text-xs text-slate-400">ketma-ket xabarlarni birlashtirish vaqti</span>
                    </div>
                    @error('debounce_seconds')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">AI Ko'rsatmalar (System prompt)</label>
                <div class="relative">
                    <textarea name="ai_instructions" rows="9"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-700 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition resize-none">{{ $settings['ai_instructions'] }}</textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-slate-300 font-mono pointer-events-none">system prompt</div>
                </div>
                @error('ai_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                    Me Mode Ko'rsatmalar
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">/memode</span>
                </label>
                <p class="text-xs text-slate-400 mb-2">Chatda <code class="bg-slate-100 px-1 rounded">/memode</code> yozganingizda AI shu ko'rsatmalar bilan siz bo'lib yozadi.</p>
                <div class="relative">
                    <textarea name="me_mode_instructions" rows="7"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-700 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition resize-none">{{ $settings['me_mode_instructions'] }}</textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-slate-300 font-mono pointer-events-none">me mode</div>
                </div>
                @error('me_mode_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end pb-8">
        <button type="submit" class="gradient-btn text-white rounded-2xl px-8 py-3 text-sm font-bold transition shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 hover:scale-[1.02] active:scale-[0.98]">
            Saqlash
        </button>
    </div>
</form>

@endsection
