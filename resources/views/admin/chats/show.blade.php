@extends('admin.layout')

@section('title', ($language->chat_name ?? 'Chat') . ' – Tarixi')

@section('content')

{{-- Page header --}}
<div class="mb-6 flex items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.chats.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-800 hover:border-slate-300 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ $language->chat_name ?? 'Noma\'lum chat' }}</h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $chatId }}</p>
        </div>
    </div>
    @if ($language)
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $language->is_manual ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                {{ $language->language_name }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">
                {{ $language->address_form ?? 'siz' }}
            </span>
        </div>
    @endif
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
                    <div class="max-w-[85%] sm:max-w-[70%]">
                        <div class="flex items-end gap-2 {{ $msg->role === 'user' ? 'flex-row' : 'flex-row-reverse' }}">
                            <div class="relative px-4 py-3 rounded-2xl text-sm shadow-sm transition-all duration-200
                                {{ $msg->role === 'user'
                                    ? 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'
                                    : 'bg-indigo-600 text-white rounded-br-none shadow-indigo-100' }}">
                                <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                            </div>
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
