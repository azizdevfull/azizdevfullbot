<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bot Admin – Kirish</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-bg min-h-screen font-sans antialiased flex items-center justify-center relative overflow-hidden">

    {{-- Background orbs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-20" style="background: radial-gradient(circle, #6366f1, transparent)"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full opacity-20" style="background: radial-gradient(circle, #8b5cf6, transparent)"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-5" style="background: radial-gradient(circle, #a78bfa, transparent)"></div>
    </div>

    <div class="relative w-full max-w-sm px-4" x-data="{ mode: '{{ $otpSent ? 'otp' : (old('email') ? 'password' : 'otp') }}' }">
        <div class="bg-white/10 backdrop-blur-2xl rounded-3xl border border-white/20 shadow-2xl p-8">

            {{-- Logo --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 rounded-2xl mb-4 flex items-center justify-center text-3xl shadow-lg shadow-indigo-500/30" style="background: linear-gradient(135deg, #6366f1, #8b5cf6)">
                    🤖
                </div>
                <h1 class="text-xl font-bold text-white">Bot Admin</h1>
                <p class="text-sm text-white/50 mt-1">@azizdevfullbot</p>
            </div>

            {{-- Tabs --}}
            @if (!$otpSent)
                <div class="flex p-1 bg-white/5 rounded-xl mb-6">
                    <button @click="mode = 'otp'" :class="mode === 'otp' ? 'bg-white/10 text-white' : 'text-white/40'" class="flex-1 py-2 text-xs font-bold rounded-lg transition">Telegram</button>
                    <button @click="mode = 'password'" :class="mode === 'password' ? 'bg-white/10 text-white' : 'text-white/40'" class="flex-1 py-2 text-xs font-bold rounded-lg transition">Parol</button>
                </div>
            @endif

            {{-- OTP Request Mode --}}
            <div x-show="mode === 'otp' && !{{ $otpSent ? 'true' : 'false' }}" x-cloak>
                <p class="text-sm text-white/60 text-center mb-6">Telegram orqali bir martalik kod oling</p>

                @error('otp')
                    <div class="mb-4 rounded-xl bg-red-500/20 border border-red-400/30 px-4 py-3 text-red-300 text-sm text-center">{{ $message }}</div>
                @enderror

                <form method="POST" action="{{ route('admin.otp.request') }}">
                    @csrf
                    <button type="submit" class="gradient-btn w-full text-white rounded-xl px-4 py-3 text-sm font-semibold transition shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] active:scale-[0.98]">
                        Telegram ga kod yuborish
                    </button>
                </form>
            </div>

            {{-- OTP Verify Mode --}}
            <div x-show="mode === 'otp' && {{ $otpSent ? 'true' : 'false' }}" x-cloak>
                <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-400/30 rounded-full px-4 py-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs text-emerald-300 font-medium">Telegram ga yuborildi</span>
                    </div>
                    <p class="text-sm text-white/60">6 raqamli kodni kiriting</p>
                </div>

                <form method="POST" action="{{ route('admin.otp.verify') }}" class="space-y-4">
                    @csrf
                    <div>
                        <input
                            type="text"
                            name="otp"
                            inputmode="numeric"
                            maxlength="6"
                            autofocus
                            placeholder="• • • • • •"
                            class="otp-input w-full rounded-xl bg-white/10 border border-white/20 px-4 py-4 text-center text-2xl font-bold text-white placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition @error('otp') border-red-400/50 @enderror"
                        >
                        @error('otp')
                            <p class="mt-2 text-xs text-red-300 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="gradient-btn w-full text-white rounded-xl px-4 py-3 text-sm font-semibold transition shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] active:scale-[0.98]">
                        Tasdiqlash
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.otp.request') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full text-xs text-white/30 hover:text-white/60 transition py-2">
                        Qayta yuborish
                    </button>
                </form>
            </div>

            {{-- Password Login Mode --}}
            <div x-show="mode === 'password'" x-cloak>
                <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-white/40 uppercase tracking-widest mb-1.5 ml-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-white/40 uppercase tracking-widest mb-1.5 ml-1">Parol</label>
                        <input type="password" name="password" required
                            class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    </div>

                    @if ($errors->has('email'))
                        <p class="text-xs text-red-300 text-center">{{ $errors->first('email') }}</p>
                    @endif

                    <button type="submit" class="gradient-btn w-full text-white rounded-xl px-4 py-3 text-sm font-semibold transition shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] active:scale-[0.98] mt-2">
                        Kirish
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>
