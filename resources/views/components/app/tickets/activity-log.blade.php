@props([
    'logs'
])

<div
    class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Activity Log</h2>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $logs->count() }} events</span>
        </div>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Timeline of ticket interactions</p>
    </div>
    <div class="p-6">
        <div class="relative space-y-4 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-zinc-300 dark:before:via-zinc-800 before:to-transparent">
            @forelse ($logs as $log)
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                        @switch($log->action)
                            @case('status_changed')
                                <flux:icon.check-circle class="w-4 h-4 text-emerald-500" />
                                @break
                            @case('priority_changed')
                                <flux:icon.exclamation-triangle class="w-4 h-4 text-orange-500" />
                                @break
                            @case('assigned')
                                <flux:icon.user class="w-4 h-4 text-indigo-500" />
                                @break
                            @case('reply_added')
                                <flux:icon.chat-bubble-left-right class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                @break
                            @default
                                <flux:icon.clock class="w-4 h-4 text-zinc-400" />
                        @endswitch
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                {{ $log->user->name ?? 'System' }}
                                @if($log->user_id === auth()->id())
                                    <span class="text-[10px] text-zinc-500 font-normal">(you)</span>
                                @endif
                            </div>
                            <time class="text-[10px] text-zinc-500">{{ $log->created_at->diffForHumans() }}</time>
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $log->description }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-zinc-500 dark:text-zinc-400">No activity logged yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
