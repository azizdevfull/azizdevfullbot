@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

{{-- Page header --}}
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard</h1>
        <p class="text-sm text-slate-400 mt-1">Telegram bot holati va asosiy ko'rsatkichlar</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $aiEnabled ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $aiEnabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }} mr-2"></span>
            AI {{ $aiEnabled ? 'Online' : 'Offline' }}
        </span>
    </div>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bugun: {{ $todayMessages }}</span>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $connectionsCount }}</p>
        <p class="text-xs font-medium text-slate-400 mt-1">Ulanishlar</p>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">AI: {{ $todayAiReplies }}</span>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $chatsCount }}</p>
        <p class="text-xs font-medium text-slate-400 mt-1">Chatlar</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $commandsCount }}</p>
        <p class="text-xs font-medium text-slate-400 mt-1">Buyruqlar</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $workingHoursEnabled ? 'Yoqiq' : 'O\'chiq' }}</p>
        <p class="text-xs font-medium text-slate-400 mt-1">Ish vaqti</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Recent Logs Section --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h2 class="font-semibold text-slate-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                So'nggi harakatlar (Logs)
            </h2>
            <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Hammasi →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full {{ $log['level'] === 'ERROR' ? 'bg-red-500' : 'bg-emerald-500' }} mr-3"></span>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 leading-none mb-1">{{ Str::limit($log['message'], 80) }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $log['time'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="px-6 py-10 text-center text-slate-400 text-sm italic">
                            Hozircha hech qanday harakat qayd etilmadi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Settings / Status --}}
    <div class="space-y-6">
        <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200">
            <h3 class="font-bold text-lg mb-2">Tezkor Havolalar</h3>
            <p class="text-indigo-100 text-sm mb-6">Botni boshqarish va tahlil qilish uchun asosiy bo'limlar</p>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.settings.index') }}" class="bg-white/10 hover:bg-white/20 p-3 rounded-xl transition text-center">
                    <p class="text-xs font-bold">Settings</p>
                </a>
                <a href="{{ route('admin.stats.index') }}" class="bg-white/10 hover:bg-white/20 p-3 rounded-xl transition text-center">
                    <p class="text-xs font-bold">Stats</p>
                </a>
                <a href="{{ route('admin.chats.index') }}" class="bg-white/10 hover:bg-white/20 p-3 rounded-xl transition text-center">
                    <p class="text-xs font-bold">Chats</p>
                </a>
                <a href="{{ route('admin.personas.index') }}" class="bg-white/10 hover:bg-white/20 p-3 rounded-xl transition text-center">
                    <p class="text-xs font-bold">Personas</p>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6">
            <h3 class="font-semibold text-slate-800 mb-4 text-sm">Bot Salomatligi</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Database Engine</span>
                    <span class="text-xs font-bold text-slate-700">MySQL 8.0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PHP Version</span>
                    <span class="text-xs font-bold text-slate-700">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Laravel Version</span>
                    <span class="text-xs font-bold text-slate-700">{{ App::version() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
