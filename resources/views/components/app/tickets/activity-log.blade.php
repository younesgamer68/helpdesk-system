@props(['logs'])

<div class="flex flex-col flex-1 min-h-0">
    {{-- Header --}}
    <div class="shrink-0 flex items-center justify-between px-6 py-3.5 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon.clock class="w-4 h-4 text-zinc-500" />
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Activity Log</h2>
            @if ($logs->count() > 0)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                    {{ $logs->count() }}
                </span>
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    <div class="flex-1 overflow-y-auto px-6 py-5">
        @if ($logs->isEmpty())
            <div class="flex flex-col items-center justify-center min-h-[200px] text-center py-12">
                <div class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                    <flux:icon.clock class="w-7 h-7 text-zinc-400" />
                </div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">No activity yet</p>
                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Events will appear here as the ticket progresses</p>
            </div>
        @else
            <div class="relative">
                {{-- Vertical timeline line --}}
                <div class="absolute left-4 top-3 bottom-3 w-px bg-zinc-200 dark:bg-zinc-800"></div>

                <div class="space-y-0">
                    @foreach ($logs as $log)
                        <div class="flex gap-3 relative">
                            {{-- Icon circle --}}
                            <div class="relative shrink-0 w-8 h-8 rounded-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center z-10 shadow-sm">
                                @switch($log->action)
                                    @case('status_changed')
                                        <flux:icon.check-circle class="w-3.5 h-3.5 text-emerald-500" />
                                    @break

                                    @case('priority_changed')
                                        <flux:icon.exclamation-triangle class="w-3.5 h-3.5 text-orange-500" />
                                    @break

                                    @case('assigned')
                                        <flux:icon.user class="w-3.5 h-3.5 text-indigo-500" />
                                    @break

                                    @case('reply_added')
                                        <flux:icon.chat-bubble-left-right class="w-3.5 h-3.5 text-zinc-400" />
                                    @break

                                    @default
                                        <flux:icon.clock class="w-3.5 h-3.5 text-zinc-400" />
                                @endswitch
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 py-1.5 pb-5 min-w-0">
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-snug">{{ $log->description }}</p>
                                <div class="flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                    <span>{{ $log->user->name ?? 'System' }}{{ $log->user_id === auth()->id() ? ' (you)' : '' }}</span>
                                    <span>&middot;</span>
                                    <time>{{ $log->created_at->diffForHumans() }}</time>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
