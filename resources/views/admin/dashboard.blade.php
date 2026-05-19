@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
{{-- Business Connections --}}
<section class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Business Ulanishlar</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($connections as $connection)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">ID: {{ $connection->connection_id }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Telegram user: {{ $connection->telegram_user_id }}
                        &nbsp;·&nbsp;
                        Javob bera oladi: {{ $connection->can_reply ? 'Ha' : 'Yo\'q' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.connections.toggle', $connection) }}">
                    @csrf
                    <button type="submit"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg border transition
                            {{ $connection->is_enabled
                                ? 'border-red-200 text-red-600 hover:bg-red-50'
                                : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                        {{ $connection->is_enabled ? 'O\'chirish' : 'Yoqish' }}
                    </button>
                </form>
            </div>
        @empty
            <p class="px-6 py-4 text-sm text-gray-500">Hech qanday ulanish yo'q.</p>
        @endforelse
    </div>
</section>

{{-- Commands --}}
<section class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Buyruqlar</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($commands as $cmd)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <code class="text-sm font-mono text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">/{{ $cmd->command }}</code>
                    <p class="text-xs text-gray-600 mt-1 truncate">{{ $cmd->reply }}</p>
                </div>
                <form method="POST" action="{{ route('admin.commands.destroy', $cmd) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition shrink-0">O'chirish</button>
                </form>
            </div>
        @empty
            <p class="px-6 py-4 text-sm text-gray-500">Buyruqlar yo'q.</p>
        @endforelse
    </div>
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
        <form method="POST" action="{{ route('admin.commands.store') }}" class="flex gap-3 flex-wrap">
            @csrf
            <div class="flex-1 min-w-32">
                <input type="text" name="command" placeholder="Buyruq (masalan: hello)" pattern="[a-z0-9_]+"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @error('command') class="border-red-400" @enderror>
                @error('command')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex-[2] min-w-48">
                <input type="text" name="reply" placeholder="Javob matni"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('reply')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition">
                Qo'shish
            </button>
        </form>
    </div>
</section>

{{-- Settings --}}
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    {{-- Working Hours --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Ish Vaqti</h2>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="working_hours_enabled" value="1"
                    {{ $settings['working_hours_enabled'] === '1' ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600">
                <span class="text-sm text-gray-600">Yoqilgan</span>
            </label>
        </div>
        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Boshlanish vaqti</label>
                <input type="time" name="working_hours_start" value="{{ $settings['working_hours_start'] }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tugash vaqti</label>
                <input type="time" name="working_hours_end" value="{{ $settings['working_hours_end'] }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Timezone</label>
                <select name="working_hours_timezone"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach (['Asia/Tashkent', 'Europe/Moscow', 'Europe/London', 'America/New_York', 'Asia/Dubai', 'Asia/Almaty'] as $tz)
                        <option value="{{ $tz }}" {{ $settings['working_hours_timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Ish vaqtidan tashqari javob</label>
                <input type="text" name="working_hours_message" value="{{ $settings['working_hours_message'] }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </section>

    {{-- General Settings --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Bot Sozlamalari</h2>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Fallback javob (AI ishlamasa)</label>
                <input type="text" name="fallback_reply" value="{{ $settings['fallback_reply'] }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('fallback_reply')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Debounce (soniya)</label>
                <input type="number" name="debounce_seconds" value="{{ $settings['debounce_seconds'] }}" min="1" max="30"
                    class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('debounce_seconds')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">AI Ko'rsatmalar</label>
                <textarea name="ai_instructions" rows="8"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['ai_instructions'] }}</textarea>
                @error('ai_instructions')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-2.5 text-sm font-medium transition">
            Saqlash
        </button>
    </div>
</form>
@endsection
