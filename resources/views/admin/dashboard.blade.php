@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

{{-- Page header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard</h1>
    <p class="text-sm text-slate-400 mt-1">Telegram bot holati va asosiy ko'rsatkichlar</p>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <a href="{{ route('admin.connections.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Barchasi →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $connectionsCount }}</p>
        <p class="text-sm text-slate-400 mt-1">Ulanishlar</p>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <a href="{{ route('admin.chats.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition">Barchasi →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $chatsCount }}</p>
        <p class="text-sm text-slate-400 mt-1">Chatlar</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
            <a href="{{ route('admin.commands.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition">Barchasi →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $commandsCount }}</p>
        <p class="text-sm text-slate-400 mt-1">Buyruqlar</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl {{ $aiEnabled ? 'bg-emerald-50' : 'bg-slate-100' }} flex items-center justify-center">
                <svg class="w-6 h-6 {{ $aiEnabled ? 'text-emerald-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800 transition">Sozlamalar →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $aiEnabled ? 'Yoqiq' : 'O\'chiq' }}</p>
        <p class="text-sm text-slate-400 mt-1">AI Holati</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Recent Activity or Quick Info can go here --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Bot Holati</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Ish vaqti rejimi</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $workingHoursEnabled ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                        {{ $workingHoursEnabled ? 'Faol' : 'Faol emas' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">AI Javoblar</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $aiEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                        {{ $aiEnabled ? 'Yoqilgan' : 'O\'chirilgan' }}
                    </span>
                </div>
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Bot hozirda {{ $connectionsCount }} ta business ulanish orqali xizmat ko'rsatmoqda. 
                        Oxirgi marta {{ $chatsCount }} ta chat bilan aloqada bo'lingan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
