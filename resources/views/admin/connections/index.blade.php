@extends('admin.layout')

@section('title', 'Business Ulanishlar')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Business Ulanishlar</h1>
    <p class="text-sm text-slate-400 mt-1">Telegram Business API orqali ulangan akkauntlar</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Barcha ulanishlar</h2>
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

@endsection
