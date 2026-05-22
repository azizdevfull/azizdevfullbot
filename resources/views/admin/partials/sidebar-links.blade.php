<li>
    <a href="{{ route('admin.dashboard') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        Dashboard
    </a>
</li>
<li>
    <a href="{{ route('admin.connections.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.connections.*') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.connections.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m1.5-.545l1.5.545m-1.5-.545v9.273m-15 0L10.75 21" />
        </svg>
        Ulanishlar
    </a>
</li>
<li>
    <a href="{{ route('admin.commands.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.commands.*') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.commands.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
        </svg>
        Buyruqlar
    </a>
</li>
<li>
    <a href="{{ route('admin.chats.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.chats.*') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.chats.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
        </svg>
        Chatlar
    </a>
</li>
<li>
    <a href="{{ route('admin.personas.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.personas.*') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.personas.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
        </svg>
        Personalar
    </a>
</li>
<li>
    <a href="{{ route('admin.settings.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition {{ request()->routeIs('admin.settings.*') ? 'bg-slate-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-600' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Sozlamalar
    </a>
</li>
