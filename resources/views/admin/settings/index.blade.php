@extends('admin.layout')

@section('title', 'Sozlamalar')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sozlamalar</h1>
    <p class="text-sm text-slate-400 mt-1">Bot xulq-atvori, AI va chat tillari sozlamalari</p>
</div>

{{-- Chat Languages --}}
<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-8 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            </div>
            <h2 class="font-semibold text-slate-800">Chat Tillari</h2>
        </div>
        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $chatLanguages->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($chatLanguages as $chatId => $lang)
            <div class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-700">{{ $lang->chat_name ?? 'Noma\'lum' }}</p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">
                            chat_id: {{ $lang->chat_id }}
                            <span class="mx-1 text-slate-300">·</span>
                            {{ $lang->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <form method="POST" action="{{ route('admin.chats.status.update', $lang->chat_id) }}" class="flex items-center gap-3 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        @csrf
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">AI</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="ai_enabled" value="1" onchange="this.form.submit()"
                                    {{ $lang->ai_enabled ? 'checked' : '' }}
                                    class="sr-only">
                                <div class="toggle-track relative w-9 h-5 bg-slate-200 rounded-full transition-colors duration-200">
                                    <div class="toggle-thumb absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                                </div>
                            </label>
                        </div>
                        <div class="w-px h-3 bg-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Learn</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="learning_enabled" value="1" onchange="this.form.submit()"
                                    {{ $lang->learning_enabled ? 'checked' : '' }}
                                    class="sr-only">
                                <div class="toggle-track relative w-9 h-5 bg-slate-200 rounded-full transition-colors duration-200">
                                    <div class="toggle-thumb absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                                </div>
                            </label>
                        </div>
                    </form>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $lang->is_manual ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                        {{ $lang->is_manual ? '✎ Qo\'lda' : '⟳ Avtomatik' }}
                        <span class="font-bold">{{ $lang->language_name }}</span>
                        <code class="opacity-70">[{{ $lang->language_code }}]</code>
                    </span>
                    <form method="POST" action="{{ route('admin.chats.language.set', $lang->chat_id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="language_code" class="text-xs rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-400 transition">
                            @foreach ([
                                'uz' => "O'zbek",
                                'kk' => 'Qazaq',
                                'ru' => 'Русский',
                                'en' => 'English',
                                'tr' => 'Türkçe',
                                'ar' => 'العربية',
                            ] as $code => $name)
                                <option value="{{ $code }}" {{ $lang->language_code === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="language_name" id="lang_name_{{ $lang->chat_id }}">
                        <button type="submit" onclick="document.getElementById('lang_name_{{ $lang->chat_id }}').value = this.closest('form').querySelector('select').selectedOptions[0].text"
                            class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-amber-200 text-amber-600 bg-amber-50 hover:bg-amber-100 transition">
                            Saqlash
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.chats.address.set', $lang->chat_id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="address_form" class="text-xs rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                            <option value="siz" {{ ($lang->address_form ?? 'siz') === 'siz' ? 'selected' : '' }}>Siz</option>
                            <option value="sen" {{ ($lang->address_form ?? 'siz') === 'sen' ? 'selected' : '' }}>Sen</option>
                        </select>
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-indigo-200 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                            Saqlash
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.chats.persona.set', $lang->chat_id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="persona_id" class="text-xs rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-400 transition">
                            <option value="">Persona: Oddiy</option>
                            @foreach ($personas as $persona)
                                <option value="{{ $persona->id }}" {{ ($lang->persona_id ?? '') == $persona->id ? 'selected' : '' }}>{{ $persona->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-purple-200 text-purple-600 bg-purple-50 hover:bg-purple-100 transition">
                            Saqlash
                        </button>
                    </form>
                    @if ($lang->is_manual)
                        <form method="POST" action="{{ route('admin.chats.language.reset', $lang->chat_id) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 text-slate-500 bg-slate-50 hover:bg-slate-100 transition">
                                Avtoga
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-10 text-center text-slate-400 text-sm">
                Chat tillari aniqlanmagan
            </div>
        @endforelse
    </div>
</div>

{{-- Settings Form --}}
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    {{-- Working Hours --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-8 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800">Ish Vaqti</h2>
                    <p class="text-xs text-slate-400">Ish vaqtidan tashqari avtomatik javob rejimi</p>
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
        <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
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
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tashqari javob xabari</label>
                <input type="text" name="working_hours_message" value="{{ $settings['working_hours_message'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
            </div>
        </div>
    </div>

    {{-- Bot & AI Settings --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-8 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800">AI & Bot Sozlamalari</h2>
                    <p class="text-xs text-slate-400">Gemini AI tizimi va botning umumiy xulq-atvori</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">AI</span>
                    <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ai_enabled" value="1"
                            {{ $settings['ai_enabled'] === '1' ? 'checked' : '' }}
                            class="sr-only">
                        <div class="toggle-track relative w-11 h-6 bg-slate-200 rounded-full transition-colors duration-200">
                            <div class="toggle-thumb absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                        </div>
                    </label>
                </div>
                <div class="flex items-center gap-3 border-l border-slate-100 pl-6">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Learn Mode</span>
                    <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="learning_enabled" value="1"
                            {{ $settings['learning_enabled'] === '1' ? 'checked' : '' }}
                            class="sr-only">
                        <div class="toggle-track relative w-11 h-6 bg-slate-200 rounded-full transition-colors duration-200">
                            <div class="toggle-thumb absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Fallback javob</label>
                    <input type="text" name="fallback_reply" value="{{ $settings['fallback_reply'] }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
                    <p class="mt-1 text-[10px] text-slate-400">AI xato berganda yoki javob topolmaganda ishlatiladi</p>
                    @error('fallback_reply')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Debounce (soniya)</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="debounce_seconds" value="{{ $settings['debounce_seconds'] }}" min="1" max="30"
                            class="w-24 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition">
                        <span class="text-xs text-slate-400">ketma-ket kelgan xabarlarni bitta deb hisoblash vaqti</span>
                    </div>
                    @error('debounce_seconds')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">AI Ko'rsatmalar (System prompt)</label>
                <textarea name="ai_instructions" rows="10"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-700 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition resize-none">{{ $settings['ai_instructions'] }}</textarea>
                @error('ai_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                    Me Mode Ko'rsatmalar
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase">/memode</span>
                </label>
                <p class="text-xs text-slate-400 mb-3">Siz nomingizdan javob berishi uchun maxsus ko'rsatmalar.</p>
                <textarea name="me_mode_instructions" rows="8"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-700 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white transition resize-none">{{ $settings['me_mode_instructions'] }}</textarea>
                @error('me_mode_instructions')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end pb-12">
        <button type="submit" class="gradient-btn text-white rounded-2xl px-10 py-3.5 text-sm font-bold transition shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 hover:scale-[1.02] active:scale-[0.98]">
            Barcha sozlamalarni saqlash
        </button>
    </div>
</form>

@endsection
