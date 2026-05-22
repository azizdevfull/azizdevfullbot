@extends('admin.layout')

@section('title', ($language->chat_name ?? 'Chat') . ' – Tarixi')

@section('content')

<div class="mb-6" x-data="{ showSettings: false }">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.chats.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-800 hover:border-slate-300 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ $language->chat_name ?? 'Noma\'lum chat' }}</h1>
                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $chatId }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if ($language)
                <div class="hidden sm:flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $language->is_manual ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                        {{ $language->language_name }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">
                        {{ $language->address_form ?? 'siz' }}
                    </span>
                </div>
            @endif
            <button @click="showSettings = !showSettings" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4" :class="{ 'rotate-180': showSettings }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Sozlamalar
            </button>
            <form method="POST" action="{{ route('admin.chats.clear', $chatId) }}" onsubmit="return confirm('Barcha xabarlarni o\'chirishni tasdiqlaysizmi?')">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-50 border border-red-100 text-sm font-semibold text-red-600 hover:bg-red-100 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Tozalash
                </button>
            </form>
        </div>
    </div>

    {{-- Chat settings panel --}}
    <div x-show="showSettings" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-4 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm overflow-hidden">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Language settings --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Chat tili</label>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.chats.language.set', $chatId) }}" class="flex-1 flex items-center gap-3">
                        @csrf
                        <select name="language_code" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-400 transition">
                            @foreach ([
                                'uz' => "O'zbek",
                                'kk' => 'Qazaq',
                                'ru' => 'Русский',
                                'en' => 'English',
                                'tr' => 'Türkçe',
                                'ar' => 'العربية',
                            ] as $code => $name)
                                <option value="{{ $code }}" {{ ($language->language_code ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="language_name">
                        <button type="submit" @click="$el.previousElementSibling.value = $el.previousElementSibling.previousElementSibling.selectedOptions[0].text"
                            class="px-4 py-2 rounded-xl bg-teal-50 text-teal-600 border border-teal-200 text-xs font-bold hover:bg-teal-100 transition whitespace-nowrap">
                            Saqlash
                        </button>
                    </form>
                    @if ($language?->is_manual)
                        <form method="POST" action="{{ route('admin.chats.language.reset', $chatId) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-50 text-slate-500 border border-slate-200 text-xs font-bold hover:bg-slate-100 transition whitespace-nowrap">
                                Avto
                            </button>
                        </form>
                    @endif
                </div>
                <p class="mt-2 text-[10px] text-slate-400">Bot foydalanuvchiga aynan shu tilda javob beradi.</p>
            </div>

            {{-- Address form settings --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Murojaat shakli</label>
                <form method="POST" action="{{ route('admin.chats.address.set', $chatId) }}" class="flex items-center gap-3" x-data="{ addressForm: '{{ $language->address_form ?? 'siz' }}' }">
                    @csrf
                    <div class="flex-1 grid grid-cols-2 gap-2 p-1 bg-slate-50 rounded-xl border border-slate-200">
                        <label class="cursor-pointer">
                            <input type="radio" name="address_form" value="siz" class="sr-only" x-model="addressForm">
                            <div class="py-1.5 text-center text-xs font-bold rounded-lg transition" 
                                 :class="addressForm === 'siz' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">Siz</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="address_form" value="sen" class="sr-only" x-model="addressForm">
                            <div class="py-1.5 text-center text-xs font-bold rounded-lg transition" 
                                 :class="addressForm === 'sen' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">Sen</div>
                        </label>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200 text-xs font-bold hover:bg-indigo-100 transition whitespace-nowrap">
                        Saqlash
                    </button>
                </form>
                <p class="mt-2 text-[10px] text-slate-400">AI javob berishda "Siz" yoki "Sen" shaklidan foydalanadi.</p>
            </div>

            {{-- Persona settings --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Persona (Xarakter)</label>
                <form method="POST" action="{{ route('admin.chats.persona.set', $chatId) }}" class="flex items-center gap-3">
                    @csrf
                    <select name="persona_id" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-400 transition">
                        <option value="">Default (Oddiy)</option>
                        @foreach ($personas as $persona)
                            <option value="{{ $persona->id }}" {{ ($language->persona_id ?? '') == $persona->id ? 'selected' : '' }}>{{ $persona->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 text-xs font-bold hover:bg-purple-100 transition whitespace-nowrap">
                        Saqlash
                    </button>
                </form>
                <p class="mt-2 text-[10px] text-slate-400">Suhbatdoshga xos muloqot uslubini belgilash.</p>
            </div>
        </div>
    </div>
</div>

{{-- Chat container --}}
<div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col min-h-[600px] max-h-[800px]">
    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/30 relative" id="message-container">
        {{-- Loading indicator --}}
        <div id="loading-more" class="hidden justify-center py-4">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-100 shadow-sm text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <svg class="animate-spin h-3 w-3 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Yuklanmoqda...
            </div>
        </div>

        <div id="messages-wrapper" class="space-y-6">
            @php $lastDate = null; @endphp
            @foreach ($messages as $msg)
                @php $currentDate = $msg->created_at->format('d.m.Y'); @endphp
                @if ($currentDate !== $lastDate)
                    <div class="date-header relative flex justify-center py-4" data-date="{{ $currentDate }}">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-slate-200/60"></div>
                        </div>
                        <span class="relative px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-white rounded-full border border-slate-100 shadow-sm">
                            {{ $currentDate === now()->format('d.m.Y') ? 'Bugun' : $currentDate }}
                        </span>
                    </div>
                    @php $lastDate = $currentDate; @endphp
                @endif

                <div class="message-item flex {{ $msg->role === 'user' ? 'justify-start' : 'justify-end' }} group" data-id="{{ $msg->id }}">
                    <div class="max-w-[85%] sm:max-w-[70%] relative">
                        <div class="flex items-end gap-2 {{ $msg->role === 'user' ? 'flex-row' : 'flex-row-reverse' }}">
                            <div class="relative px-4 py-3 rounded-2xl text-sm shadow-sm transition-all duration-200
                                {{ $msg->role === 'user'
                                    ? 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'
                                    : 'bg-indigo-600 text-white rounded-br-none shadow-indigo-100' }}">
                                <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                            </div>
                            
                            {{-- Individual message delete button --}}
                            <form method="POST" action="{{ route('admin.messages.destroy', $msg->id) }}" onsubmit="return confirm('Ushbu xabarni o\'chirasizmi?')" 
                                  class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-400 hover:text-red-600 hover:bg-red-100 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>

                            <span class="text-[10px] text-slate-300 font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 mb-1">
                                {{ $msg->created_at->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Info footer --}}
    <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-xs font-medium text-slate-500">Bot faol holatda</span>
        </div>
        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider" id="message-count">
            Oxirgi 50 ta xabar
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('message-container');
    const wrapper = document.getElementById('messages-wrapper');
    const loadingIndicator = document.getElementById('loading-more');
    const chatId = "{{ $chatId }}";
    
    let hasMore = {{ $messages->count() === 50 ? 'true' : 'false' }};
    let isLoading = false;
    let firstId = {{ $messages->first()?->id ?? 0 }};

    // Scroll to bottom on initial load
    container.scrollTop = container.scrollHeight;

    container.addEventListener('scroll', function() {
        if (container.scrollTop < 50 && hasMore && !isLoading) {
            loadMoreMessages();
        }
    });

    async function loadMoreMessages() {
        isLoading = true;
        loadingIndicator.classList.remove('hidden');
        loadingIndicator.classList.add('flex');

        try {
            const response = await fetch(`/admin/chats/${chatId}/messages?before_id=${firstId}`);
            const data = await response.json();

            if (data.messages.length > 0) {
                const oldHeight = wrapper.scrollHeight;
                
                // Track the first date header already present
                let topDateHeader = wrapper.querySelector('.date-header');
                let topDate = topDateHeader ? topDateHeader.dataset.date : null;

                // Create new elements
                const fragment = document.createDocumentFragment();
                let lastAddedDate = null;

                data.messages.forEach(msg => {
                    const msgDate = new Date(msg.created_at).toLocaleDateString('ru-RU').split('.').join('.');
                    
                    // Add date header if date changed
                    if (msgDate !== lastAddedDate) {
                        // If this is the last message in our "new" batch and its date matches the current "top" date,
                        // we'll eventually remove the old top date header.
                        const dateDiv = document.createElement('div');
                        dateDiv.className = 'date-header relative flex justify-center py-4';
                        dateDiv.dataset.date = msgDate;
                        dateDiv.innerHTML = `
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-slate-200/60"></div>
                            </div>
                            <span class="relative px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-white rounded-full border border-slate-100 shadow-sm">
                                ${msgDate}
                            </span>
                        `;
                        fragment.appendChild(dateDiv);
                        lastAddedDate = msgDate;
                    }

                    const msgDiv = document.createElement('div');
                    msgDiv.className = `message-item flex ${msg.role === 'user' ? 'justify-start' : 'justify-end'} group`;
                    msgDiv.dataset.id = msg.id;
                    
                    const time = new Date(msg.created_at).toLocaleTimeString('uz-UZ', {hour: '2-digit', minute: '2-digit'});
                    
                    msgDiv.innerHTML = `
                        <div class="max-w-[85%] sm:max-w-[70%]">
                            <div class="flex items-end gap-2 ${msg.role === 'user' ? 'flex-row' : 'flex-row-reverse'}">
                                <div class="relative px-4 py-3 rounded-2xl text-sm shadow-sm transition-all duration-200
                                    ${msg.role === 'user'
                                        ? 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'
                                        : 'bg-indigo-600 text-white rounded-br-none shadow-indigo-100'}">
                                    <div class="whitespace-pre-wrap break-words">${msg.content}</div>
                                </div>
                                <span class="text-[10px] text-slate-300 font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 mb-1">
                                    ${time}
                                </span>
                            </div>
                        </div>
                    `;
                    fragment.appendChild(msgDiv);
                });

                // If the last added date header matches the existing top date header, remove the existing one
                if (lastAddedDate === topDate && topDateHeader) {
                    topDateHeader.remove();
                }

                wrapper.prepend(fragment);
                
                // Update state
                firstId = data.messages[0].id;
                hasMore = data.has_more;
                
                // Restore scroll position
                container.scrollTop = wrapper.scrollHeight - oldHeight;
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
            loadingIndicator.classList.remove('flex');
        }
    }
});
</script>

@endsection
