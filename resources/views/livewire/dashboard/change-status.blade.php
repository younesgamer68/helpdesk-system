<x-dropdown-btn>
    <x-slot:title>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        Change status
    </x-slot:title>

    @foreach (['pending', 'open', 'in progress', 'resolved', 'closed'] as $status)
        <button wire:click="changeStatus('{{ $status }}')"
            wire:confirm="Are you sure you want to change status for this ticket to {{ $status }}"
            @click="open = false" class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-800 transition">
            {{ $status }}
        </button>
    @endforeach

</x-dropdown-btn>
