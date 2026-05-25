@extends('admin.layout')

@section('title', 'Personalar')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Personalar</h1>
    <p class="text-sm text-slate-400 mt-1">Bot uchun muloqot personallari va ularga xos ko'rsatmalar</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-8 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Barcha personalar</h2>
        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $personas->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($personas as $persona)
            <div class="px-6 py-4 flex flex-col gap-2" x-data="{ 
                editing: false, 
                name: '{{ addslashes($persona->name) }}', 
                prompt: {{ json_encode($persona->prompt_instruction) }} 
            }">
                <div class="flex items-center justify-between gap-4">
                    <template x-if="!editing">
                        <h3 class="font-bold text-slate-800">{{ $persona->name }}</h3>
                    </template>
                    <template x-if="editing">
                        <input type="text" x-model="name" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    </template>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="editing = !editing" x-text="editing ? 'Bekor qilish' : 'Tahrirlash'"
                            class="text-xs font-semibold text-indigo-400 hover:text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 px-3 py-1.5 rounded-xl transition">
                        </button>
                        
                        <form x-show="!editing" method="POST" action="{{ route('admin.personas.destroy', $persona) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 px-3 py-1.5 rounded-xl transition">
                                O'chirish
                            </button>
                        </form>

                        <form x-show="editing" method="POST" action="{{ route('admin.personas.update', $persona) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" :value="name">
                            <input type="hidden" name="prompt_instruction" :value="prompt">
                            <button type="submit" class="text-xs font-semibold text-emerald-500 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 px-3 py-1.5 rounded-xl transition">
                                Saqlash
                            </button>
                        </form>
                    </div>
                </div>
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                    <template x-if="!editing">
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $persona->prompt_instruction }}</p>
                    </template>
                    <template x-if="editing">
                        <textarea x-model="prompt" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"></textarea>
                    </template>
                </div>

                {{-- History Section --}}
                @if($persona->histories->isNotEmpty())
                    <div x-data="{ showHistory: false }" class="mt-2">
                        <button @click="showHistory = !showHistory" class="text-[10px] font-bold text-slate-400 hover:text-indigo-500 uppercase tracking-widest flex items-center gap-1 transition">
                            <svg class="w-3 h-3" :class="showHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            O'zgarishlar tarixi ({{ $persona->histories->count() }})
                        </button>
                        
                        <div x-show="showHistory" x-cloak x-transition class="mt-3 space-y-3 pl-4 border-l-2 border-slate-100">
                            @foreach($persona->histories as $history)
                                <div class="relative pb-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-mono text-slate-400">{{ $history->created_at->format('d.m.Y H:i') }}</span>
                                        @if($history->source_chat_id)
                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-medium">Chat: {{ $history->source_chat_id }}</span>
                                        @endif
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-500 line-clamp-2 hover:line-clamp-none transition-all cursor-help" title="Eski holat">
                                        {{ $history->old_instruction }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-slate-400">Hozircha personalar yo'q</p>
            </div>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">Yangi persona qo'shish</h2>
    </div>
    <div class="p-6">
        <form method="POST" action="{{ route('admin.personas.store') }}" class="space-y-4">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Nomi</label>
                    <input type="text" name="name" placeholder="Masalan: Yaqin Do'st" required
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Ko'rsatma (System Prompt)</label>
                    <textarea name="prompt_instruction" rows="4" placeholder="AI shu inson bilan qanday gaplashishi haqida ko'rsatma..." required
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"></textarea>
                    @error('prompt_instruction')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="gradient-btn text-white rounded-xl px-6 py-2.5 text-sm font-semibold transition shadow-md shadow-indigo-200 hover:shadow-indigo-300 hover:scale-[1.02] active:scale-[0.98]">
                    Qo'shish
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
