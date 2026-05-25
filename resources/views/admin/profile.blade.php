@extends('admin.layout')

@section('title', 'Profil Sozlamalari')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Profil Sozlamalari</h1>
        <p class="text-sm text-slate-500 mt-1">Shaxsiy ma'lumotlaringiz va kirish parolini boshqaring.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Ma'lumotlarni tahrirlash</h2>
        </div>
        
        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                {{-- Name --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Ism</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                    @error('name') <p class="mt-1 text-xs text-red-500 ml-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Email (Login uchun)</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                    <p class="mt-1.5 text-[10px] text-slate-400 ml-1 italic">Telegramsiz kirishda ushbu emaildan foydalanasiz.</p>
                    @error('email') <p class="mt-1 text-xs text-red-500 ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="h-px bg-slate-100 my-2"></div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1 flex justify-between items-center">
                        Yangi Parol
                        <button type="button" @click="show = !show" class="text-[10px] text-indigo-500 hover:text-indigo-700 normal-case font-bold tracking-normal transition">
                            <span x-show="!show">Ko'rsat</span>
                            <span x-show="show" x-cloak>Yashirish</span>
                        </button>
                    </label>
                    <input :type="show ? 'text' : 'password'" name="password" placeholder="O'zgartirmaslik uchun bo'sh qoldiring"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                    @error('password') <p class="mt-1 text-xs text-red-500 ml-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password Confirmation --}}
                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1 flex justify-between items-center">
                        Parolni tasdiqlash
                        <button type="button" @click="show = !show" class="text-[10px] text-indigo-500 hover:text-indigo-700 normal-case font-bold tracking-normal transition">
                            <span x-show="!show">Ko'rsat</span>
                            <span x-show="show" x-cloak>Yashirish</span>
                        </button>
                    </label>
                    <input :type="show ? 'text' : 'password'" name="password_confirmation" placeholder="Yangi parolni qayta kiriting"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto px-10 py-3.5 rounded-2xl gradient-btn text-white font-bold shadow-lg shadow-indigo-100 hover:shadow-indigo-200 hover:scale-[1.01] active:scale-[0.99] transition">
                    O'zgarishlarni saqlash
                </button>
            </div>
        </form>
    </div>

    {{-- Info Card --}}
    <div class="mt-8 p-6 rounded-3xl bg-indigo-50 border border-indigo-100 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-500 shadow-sm shrink-0 border border-indigo-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-indigo-900">Xavfsizlik haqida</h4>
            <p class="text-xs text-indigo-700/70 mt-1 leading-relaxed">Parol o'rnatganingizdan so'ng, Telegram ishlamay qolgan holatlarda ham login/parol orqali admin panelga kirishingiz mumkin bo'ladi. Parolni kamida 8 ta belgidan iborat qiling.</p>
        </div>
    </div>
</div>
@endsection
