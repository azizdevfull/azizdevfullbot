@extends('admin.layout')

@section('title', ($language->chat_name ?? 'Chat') . ' – Tarixi')

@section('content')

{{-- Page header --}}
<div class="mb-6 flex items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.chats.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-800 hover:border-slate-300 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ $language->chat_name ?? 'Noma\'lum chat' }}</h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $chatId }}</p>
        </div>
    </div>
    @if ($language)
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $language->is_manual ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                {{ $language->language_name }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">
                {{ $language->address_form ?? 'siz' }}
            </span>
        </div>
    @endif
</div>

{{-- Chat container --}}
<div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col min-h-[600px] max-h-[800px]">
    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/30" id="message-container">
        @forelse ($messages->groupBy(fn($m) => $m->created_at->format('d.m.Y')) as $date => $dayMessages)
            <div class="relative flex justify-center py-4">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-slate-200/60"></div>
                </div>
                <span class="relative px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-white rounded-full border border-slate-100 shadow-sm">
                    {{ $date === now()->format('d.m.Y') ? 'Bugun' : $date }}
                </span>
            </div>

            @foreach ($dayMessages as $msg)
                <div class="flex {{ $msg->role === 'user' ? 'justify-start' : 'justify-end' }} group">
                    <div class="max-w-[85%] sm:max-w-[70%]">
                        <div class="flex items-end gap-2 {{ $msg->role === 'user' ? 'flex-row' : 'flex-row-reverse' }}">
                            <div class="relative px-4 py-3 rounded-2xl text-sm shadow-sm transition-all duration-200
                                {{ $msg->role === 'user'
                                    ? 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'
                                    : 'bg-indigo-600 text-white rounded-br-none shadow-indigo-100' }}">
                                <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                            </div>
                            <span class="text-[10px] text-slate-300 font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 mb-1">
                                {{ $msg->created_at->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center py-20">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-slate-800 font-semibold">Xabarlar yo'q</h3>
                <p class="text-sm text-slate-400 mt-1">Hali hech qanday muloqot tarixi mavjud emas.</p>
            </div>
        @endforelse
    </div>

    {{-- Info footer --}}
    <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-xs font-medium text-slate-500">Bot faol holatda</span>
        </div>
        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
            Jami {{ $messages->count() }} xabar
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('message-container');
        container.scrollTop = container.scrollHeight;
    });
</script>

@endsection
