<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bot Admin – @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <span class="font-semibold text-gray-800 text-lg">🤖 Bot Admin</span>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 transition">Chiqish</button>
        </form>
    </nav>
    <main class="max-w-4xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
