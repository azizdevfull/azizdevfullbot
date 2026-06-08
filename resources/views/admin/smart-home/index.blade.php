@extends('admin.layout')

@section('title', 'Smart Home')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">🏠 Smart Home</h1>
            <p class="mt-1 text-sm text-slate-500">Tuya qurilmalarni boshqarish</p>
        </div>
        <a href="{{ route('admin.smart-home.index') }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Yangilash
        </a>
    </div>

    {{-- Add device form --}}
    <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Qurilma qo'shish</h2>
        <form action="{{ route('admin.smart-home.devices.add') }}" method="POST" class="flex gap-3">
            @csrf
            <input
                type="text"
                name="device_id"
                placeholder="Device ID (masalan: bfa130909a118bae7bbq7e)"
                class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                required
            >
            <button type="submit" class="gradient-btn text-white rounded-xl px-5 py-2 text-sm font-semibold transition">
                Qo'shish
            </button>
        </form>
        @error('device_id')
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- No devices --}}
    @if(empty($devices))
        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-6 py-12 text-center">
            <div class="text-4xl mb-3">🏠</div>
            <p class="text-slate-500 font-medium">Qurilmalar yo'q.</p>
            <p class="mt-1 text-sm text-slate-400">Device ID qo'shing — switch larni avtomatik topamiz.</p>
        </div>
    @endif

    {{-- Device cards --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($devices as $device)
        <div class="card-hover rounded-2xl bg-white border shadow-sm {{ $device['error'] ? 'border-red-200' : 'border-slate-200' }}">

            {{-- Device header --}}
            <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-slate-100">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-900 truncate">{{ $device['name'] }}</h3>
                        @if($device['online'] === true)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Online
                            </span>
                        @elseif($device['online'] === false)
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Offline
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $device['id'] }}</p>
                </div>
                <form action="{{ route('admin.smart-home.devices.remove', $device['id']) }}" method="POST" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ml-3 text-slate-300 hover:text-red-400 transition" title="O'chirish">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </form>
            </div>

            @if($device['error'])
                <div class="px-6 py-4 text-sm text-red-500">
                    ⚠️ API xatolik. Device ID yoki Tuya credentials ni tekshiring.
                </div>
            @else
                {{-- Switches --}}
                @if(!empty($device['switches']))
                <div class="px-6 py-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Switchlar</p>
                    @foreach($device['switches'] as $switch)
                    <div
                        x-data="{
                            isOn: {{ $switch['value'] ? 'true' : 'false' }},
                            busy: false,
                            async toggle() {
                                this.busy = true;
                                try {
                                    const res = await fetch('/admin/smart-home/{{ $device['id'] }}/toggle', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
                                        },
                                        body: JSON.stringify({ switch_code: '{{ $switch['code'] }}' }),
                                    });
                                    const data = await res.json();
                                    if (res.ok) this.isOn = data.is_on;
                                } finally {
                                    this.busy = false;
                                }
                            }
                        }"
                        class="flex items-center justify-between rounded-xl border px-4 py-3 transition"
                        :class="isOn ? 'border-emerald-200 bg-emerald-50' : 'border-slate-100 bg-slate-50'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-xl" x-text="isOn ? '💡' : '🌑'"></span>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $switch['label'] }}</p>
                                <p class="text-xs font-mono text-slate-400">{{ $switch['code'] }}</p>
                            </div>
                        </div>
                        <button
                            @click="toggle()"
                            :disabled="busy"
                            class="rounded-xl px-4 py-1.5 text-sm font-semibold transition disabled:opacity-50"
                            :class="isOn
                                ? 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                                : 'gradient-btn text-white'"
                            x-text="busy ? '...' : (isOn ? 'O\'chirish' : 'Yoqish')"
                        ></button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-4 text-sm text-slate-400">Switch topilmadi.</div>
                @endif

                {{-- Metrics --}}
                @if(!is_null($device['metrics']['voltage']))
                <div class="px-6 pb-5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Ko'rsatkichlar</p>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @if(!is_null($device['metrics']['voltage']))
                        <div class="rounded-xl bg-blue-50 border border-blue-100 px-3 py-2 text-center">
                            <p class="text-xs text-blue-400 font-medium">Kuchlanish</p>
                            <p class="text-sm font-bold text-blue-700 mt-0.5">{{ $device['metrics']['voltage'] }} V</p>
                        </div>
                        @endif
                        @if(!is_null($device['metrics']['current']))
                        <div class="rounded-xl bg-amber-50 border border-amber-100 px-3 py-2 text-center">
                            <p class="text-xs text-amber-400 font-medium">Tok</p>
                            <p class="text-sm font-bold text-amber-700 mt-0.5">{{ $device['metrics']['current'] }} A</p>
                        </div>
                        @endif
                        @if(!is_null($device['metrics']['power']))
                        <div class="rounded-xl bg-purple-50 border border-purple-100 px-3 py-2 text-center">
                            <p class="text-xs text-purple-400 font-medium">Quvvat</p>
                            <p class="text-sm font-bold text-purple-700 mt-0.5">{{ $device['metrics']['power'] }} W</p>
                        </div>
                        @endif
                        @if(!is_null($device['metrics']['energy']))
                        <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-3 py-2 text-center">
                            <p class="text-xs text-emerald-400 font-medium">Energiya</p>
                            <p class="text-sm font-bold text-emerald-700 mt-0.5">{{ $device['metrics']['energy'] }} kWh</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endif
        </div>
        @endforeach
    </div>

</div>
@endsection
