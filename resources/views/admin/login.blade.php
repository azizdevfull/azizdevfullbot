<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bot Admin – Kirish</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 w-full max-w-sm p-8">
        <h1 class="text-xl font-semibold text-gray-800 mb-2 text-center">🤖 Bot Admin</h1>

        @if (! $otpSent)
            <p class="text-sm text-gray-500 text-center mb-6">Telegram orqali kirish kodi oling</p>

            @error('otp')
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('admin.otp.request') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2.5 text-sm font-medium transition">
                    Telegram ga kod yuborish
                </button>
            </form>
        @else
            <p class="text-sm text-gray-500 text-center mb-6">Telegram ga 6 raqamli kod yuborildi</p>

            <form method="POST" action="{{ route('admin.otp.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <input
                        type="text"
                        name="otp"
                        inputmode="numeric"
                        maxlength="6"
                        autofocus
                        placeholder="000000"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-center text-2xl tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 @error('otp') border-red-400 @enderror"
                    >
                    @error('otp')
                        <p class="mt-1 text-xs text-red-600 text-center">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2.5 text-sm font-medium transition">
                    Tasdiqlash
                </button>
            </form>

            <form method="POST" action="{{ route('admin.otp.request') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-xs text-gray-400 hover:text-gray-600 transition py-1">
                    Qayta yuborish
                </button>
            </form>
        @endif
    </div>
</body>
</html>
