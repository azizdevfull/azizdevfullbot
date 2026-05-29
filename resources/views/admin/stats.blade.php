@extends('admin.layout')

@section('title', 'Statistika')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Statistika</h1>
    <p class="text-sm text-slate-400 mt-1">Bot faolligi va samaradorlik ko'rsatkichlari</p>
</div>

{{-- Global Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
        <p class="text-sm font-medium text-slate-400">Jami xabarlar</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalMessages) }}</p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
        <p class="text-sm font-medium text-emerald-500">AI javoblari</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalAiReplies) }}</p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
        <p class="text-sm font-medium text-indigo-500">Sizning javoblaringiz</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalManualReplies) }}</p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
        <p class="text-sm font-medium text-amber-500">Ovozli xabarlar</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalVoiceMessages) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    {{-- Chart: Weekly Activity --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-6">Oxirgi 7 kunlik faollik</h3>
        <div class="h-64">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>

    {{-- Language Distribution --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-6">Tillar kesimida</h3>
        <div class="space-y-4">
            @foreach($langStats as $lang)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-slate-600">{{ strtoupper($lang->language_code) }}</span>
                    <span class="text-slate-400">{{ $lang->count }} ta chat</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($lang->count / max($langStats->pluck('count')->toArray()) * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Today's Detailed Stats --}}
<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-semibold text-slate-800">Bugungi batafsil statistika</h3>
        <span class="text-xs font-medium text-slate-400 bg-slate-50 px-2 py-1 rounded-md">{{ now()->format('d.m.Y') }}</span>
    </div>
    <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $todayStats['total'] }}</p>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">Jami xabar</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-emerald-500">{{ $todayStats['ai'] }}</p>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">AI javob</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-indigo-500">{{ $todayStats['user'] }}</p>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">User xabari</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $todayStats['voice'] }}</p>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">Ovozli xabar</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')->map(fn($d) => date('d-M', strtotime($d)))) !!},
            datasets: [{
                label: 'Xabarlar soni',
                data: {!! json_encode($dailyStats->pluck('count')) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                }
            }
        }
    });
</script>
@endsection
