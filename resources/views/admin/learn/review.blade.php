@extends('admin.layout')

@section('title', 'Persona Review')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Persona Review</h1>
            <p class="text-sm text-slate-500 mt-1">Chat tahlili asosida taklif qilingan yangilanishlarni ko'rib chiqing.</p>
        </div>
        <a href="{{ route('admin.chats.show', $chatId) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Chatga qaytish
        </a>
    </div>

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-red-800 text-sm font-medium shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8">
        {{-- Diff Section --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">O'zgarishlar tahlili</h2>
            </div>
            <div class="p-6 space-y-8">
                {{-- Additions --}}
                @if(!empty($pending['additions']))
                <div>
                    <h3 class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Yangi qo'shilganlar
                    </h3>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($pending['additions'] as $item)
                        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-emerald-50/30 border border-emerald-100 text-sm text-emerald-900 leading-relaxed">
                            <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            {{ $item }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Removals --}}
                @if(!empty($pending['removals']))
                <div>
                    <h3 class="text-xs font-bold text-red-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Olib tashlanganlar
                    </h3>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($pending['removals'] as $item)
                        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-red-50/30 border border-red-100 text-sm text-red-900 leading-relaxed opacity-70">
                            <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                            </div>
                            <span class="line-through">{{ $item }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Changes --}}
                @if(!empty($pending['changes']))
                <div>
                    <h3 class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        O'zgartirilganlar
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($pending['changes'] as $change)
                        <div class="p-4 rounded-2xl bg-amber-50/20 border border-amber-100 relative">
                            <div class="text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Eskisi:</div>
                            <div class="text-sm text-slate-500 mb-4 line-through italic">{{ $change['from'] }}</div>
                            <div class="text-[11px] font-bold text-amber-600 mb-2 uppercase tracking-wide">Yangisi:</div>
                            <div class="text-sm text-amber-900 font-medium leading-relaxed">{{ $change['to'] }}</div>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-10">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M11 11.5c0-.828-.672-1.5-1.5-1.5s-1.5.672-1.5 1.5.672 1.5 1.5 1.5 1.5-.672 1.5-1.5zm3.5 0c0-.828-.672-1.5-1.5-1.5s-1.5.672-1.5 1.5.672 1.5 1.5 1.5 1.5-.672 1.5-1.5zm1.5 3.5c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-5 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5z"/></svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(empty($pending['additions']) && empty($pending['removals']) && empty($pending['changes']))
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-slate-400 text-sm">Hech qanday tarkibiy o'zgarish aniqlanmadi, faqat umumiy uslub biroz sayqallandi.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Final Instruction Edit --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Yangi Yo'riqnoma (System Prompt)</h2>
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-wider">Tahrirlash mumkin</span>
            </div>
            <form action="{{ route('admin.learn.save', $chatId) }}" method="POST" class="p-6">
                @csrf
                <textarea name="new_instruction" rows="14" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 p-5 text-sm font-mono text-slate-700 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition" required>{{ $pending['new_instruction'] }}</textarea>
                
                <div class="mt-8">
                    <button type="submit" class="w-full sm:w-auto px-10 py-4 rounded-2xl gradient-btn text-white font-bold shadow-lg shadow-indigo-100 hover:shadow-indigo-200 hover:scale-[1.01] active:scale-[0.99] transition">
                        Tasdiqlash va Saqlash
                    </button>
                </div>
            </form>
        </div>

        {{-- Reject Section --}}
        <div class="bg-slate-200/30 rounded-3xl border border-slate-200/60 p-10 text-center" x-data="{ showReject: false }">
            <div x-show="!showReject" x-transition>
                <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 border border-slate-100">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Natija sizga yoqmadimi?</h3>
                <p class="text-sm text-slate-500 mb-6 max-w-sm mx-auto">AI ga xatolarini ayting (masalan: "juda rasmiy bo'lib ketibdi") va u qaytadan tahlil qiladi.</p>
                <button @click="showReject = true" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-red-500 hover:bg-red-50 hover:border-red-100 transition shadow-sm">
                    Rad etish va qayta ishlash
                </button>
            </div>
            
            <div x-show="showReject" x-cloak x-transition>
                <form action="{{ route('admin.learn.reject', $chatId) }}" method="POST" class="max-w-xl mx-auto">
                    @csrf
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Nega rad etmoqchisiz?</label>
                    <textarea name="reason" rows="4" class="w-full rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition mb-6 shadow-sm" placeholder="Masalan: Meni uslubimda emojilar kamroq bo'lishi kerak, gapni qisqaroq yozishga harakat qil..."></textarea>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <button type="button" @click="showReject = false" class="text-sm font-bold text-slate-400 hover:text-slate-600 transition order-2 sm:order-1">Bekor qilish</button>
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition shadow-lg shadow-red-100 order-1 sm:order-2">
                            AI ga qayta yuborish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
