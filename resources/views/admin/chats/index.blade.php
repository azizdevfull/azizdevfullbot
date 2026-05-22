@extends('admin.layout')

@section('title', 'Chatlar')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Chatlar</h1>
    <p class="text-sm text-slate-400 mt-1">Foydalanuvchilar bilan bo'lgan muloqotlar ro'yxati</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="divide-y divide-slate-100">
        @forelse ($chats as $chat)
            @php
                $lang = $languages->get($chat->chat_id);
                $lastMsg = $lastMessages->get($chat->chat_id);
            @endphp
            <a href="{{ route('admin.chats.show', $chat->chat_id) }}" class="block px-6 py-5 hover:bg-slate-50 transition group">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 group-hover:from-indigo-500 group-hover:to-purple-600 group-hover:text-white transition-all duration-300 shadow-inner">
                            <span class="text-lg font-bold">{{ mb_substr($lang->chat_name ?? '?', 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-800 truncate">{{ $lang->chat_name ?? 'Noma\'lum chat' }}</h3>
                                @if ($lang)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $lang->is_manual ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                                        {{ $lang->language_code }}
                                    </span>
                                    @if ($lang->persona)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700">
                                            {{ $lang->persona->name }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 truncate mt-0.5">
                                @if($lastMsg->role === 'assistant')
                                    <span class="text-indigo-500 font-medium">Bot:</span>
                                @endif
                                {{ $lastMsg->content }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-medium text-slate-400">{{ $lastMsg->created_at->diffForHumans() }}</p>
                        <div class="mt-1 flex justify-end">
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-slate-800 font-semibold">Hali chatlar yo'q</h3>
                <p class="text-sm text-slate-400 mt-1">Bot orqali xabarlar kelganda bu yerda ko'rinadi.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $chats->links() }}
</div>

@endsection
