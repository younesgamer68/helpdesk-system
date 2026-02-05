    <div>
        <p class="text-xs text-zinc-500 mb-2">Assigned agent</p>
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                {{ substr($ticket->user->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <p class="text-sm text-white">{{ $ticket->user->name ?? 'Unassigned' }}</p>
                @if ($ticket->user)
                    <p class="text-xs text-zinc-500">{{ $ticket->user->email }}</p>
                @endif
            </div>
        </div>
    </div>
