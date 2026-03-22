@props(['reply', 'isInternal' => false])

@php
    $user = $reply->user;
    $isCustomer = !$reply->user_id;
    $senderName = $isCustomer ? $reply->customer_name : $user->name;
    $initials = strtoupper(substr($senderName, 0, 1));

    $bgClass = 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700/50';
    $badgeClass = '';
    $badgeText = '';
    $avatarBg = 'bg-blue-500';

    if ($isInternal) {
        $bgClass = 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-500/30';
        $badgeClass = 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        $badgeText = 'Internal Note';
        $avatarBg = 'bg-indigo-600';
    } elseif ($isCustomer) {
        $badgeClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        $badgeText = 'Customer';
        $avatarBg = 'bg-blue-500';
    } else {
        $badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        $badgeText = 'Support Team';
        $avatarBg = 'bg-emerald-600';
    }
@endphp

<div class="flex gap-4">
    <div class="flex-shrink-0">
        <div
            class="w-10 h-10 rounded-full {{ $avatarBg }} flex items-center justify-center text-white font-semibold shadow-sm">
            {{ $initials }}
        </div>
    </div>
    <div class="flex-1">
        <div class="rounded-lg p-4 {{ $bgClass }}">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $senderName }}
                        @if (!$isCustomer && $user && $user->id === auth()->id())
                            <span class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">(you)</span>
                        @elseif (!$isCustomer && $user)
                            <span
                                class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">({{ $user->username ?? $user->email }})</span>
                        @endif
                    </div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $reply->created_at->format('M d, Y g:i A') }}
                    </div>
                </div>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $badgeClass }}">
                    {{ $badgeText }}
                </span>
            </div>
            <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                {!! \Mews\Purifier\Facades\Purifier::clean($reply->message) !!}
            </div>

            @if ($reply->attachments)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($reply->attachments as $attachment)
                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                            class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs text-zinc-600 dark:text-zinc-300 hover:border-emerald-500 transition shadow-sm">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
