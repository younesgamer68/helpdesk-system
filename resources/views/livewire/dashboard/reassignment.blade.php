<div
    x-data="{ open: false }"
    class="relative w-full"
>
    <!-- BUTTON -->
    <button
        @click="open = !open"
        @click.outside="open = false"
        class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition text-sm flex items-center justify-center gap-2"
        type="button"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4" />
        </svg>

        Assign / Reassign

    </button>

    <!-- DROPDOWN -->
    <div
        x-show="open"
        x-transition
        class="absolute z-50 mt-2 w-full bg-zinc-900 border border-zinc-700 rounded-lg overflow-hidden"
    >
        @foreach ($agents as $agent)
            <button
                wire:click="assign({{ $agent->id }})"
                wire:confirm="Are you sure you want to reassign the ticket to {{ $agent->name }}"
                @click="open = false"
                class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-800 transition"
            >
                {{$agent->name === Auth::user()->name ? $agent->name . ' (You)' :$agent->name }}
            </button>
        @endforeach
    </div>
</div>
