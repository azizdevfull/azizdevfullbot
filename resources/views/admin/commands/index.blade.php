@extends('admin.layout')

@section('title', 'Telegram Buyruqlar')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Buyruqlar</h1>
    <p class="text-sm text-slate-400 mt-1">Bot uchun maxsus buyruqlar va ularga avtomatik javoblar</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-8 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Barcha buyruqlar</h2>
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
                <p class="text-sm text-slate-400">Hozircha buyruqlar yo'q</p>
            </div>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">Yangi buyruq qo'shish</h2>
    </div>
    <div class="p-6">
        <form method="POST" action="{{ route('admin.commands.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Buyruq</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">/</span>
                        <input type="text" name="command" placeholder="start" pattern="[a-z0-9_]+" required
                            class="w-full rounded-xl border border-slate-200 bg-white pl-7 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                    </div>
                    @error('command')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Javob matni</label>
                    <input type="text" name="reply" placeholder="Bot beradigan javob matni..." required
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                    @error('reply')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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
