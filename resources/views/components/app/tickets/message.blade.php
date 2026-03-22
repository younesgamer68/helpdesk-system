@props(['reply', 'isInternal' => false])

@php
    $user = $reply->user;
    $isCustomer = !$reply->user_id;
    $senderName = $isCustomer ? $reply->customer_name : $user->name;
    $initials = strtoupper(substr($senderName ?? '?', 0, 1));

    if ($isInternal) {
        $avatarBg = 'bg-amber-500';
        $badgeClass = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
        $badgeText = 'Internal Note';
        $bubbleClass = 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/50';
    } elseif ($isCustomer) {
        $avatarBg = 'bg-blue-500';
        $badgeClass = 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
        $badgeText = 'Customer';
        $bubbleClass = 'bg-white dark:bg-zinc-900 border-zinc-300 dark:border-zinc-600';
    } else {
        $avatarBg = 'bg-emerald-600';
        $badgeClass = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        $badgeText = 'Support Team';
        $bubbleClass = 'bg-white dark:bg-zinc-900 border-zinc-300 dark:border-zinc-600';
    }
@endphp

<div class="flex gap-3 group">
    {{-- Avatar --}}
    <div
        class="shrink-0 w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-sm font-bold shadow-sm">
        {{ $initials }}
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        {{-- Meta row --}}
        <div class="flex items-baseline gap-2 mb-1.5">
            <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $senderName }}
                @if (!$isCustomer && $user && $user->id === auth()->id())
                    <span class="text-xs text-zinc-400 dark:text-zinc-500 font-normal">(you)</span>
                @elseif (!$isCustomer && $user)
                    <span
                        class="text-xs text-zinc-400 dark:text-zinc-500 font-normal">{{ $user->username ?? $user->email }}</span>
                @endif
            </span>
            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $badgeText }}</span>
            <span
                class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">{{ $reply->created_at->format('M d, g:i A') }}</span>
        </div>

        {{-- Bubble --}}
        <div class="border rounded-xl px-5 py-4 {{ $bubbleClass }}">
            <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                {!! \Mews\Purifier\Facades\Purifier::clean($reply->message) !!}
            </div>

            @if ($reply->attachments)
                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap gap-2">
                    @foreach ($reply->attachments as $attachment)
                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs text-zinc-600 dark:text-zinc-300 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            {{ $attachment['name'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
