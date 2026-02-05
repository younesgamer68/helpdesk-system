<x-dropdown-btn>
    <x-slot:title>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Change Priority
    </x-slot:title>

    @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
        <button wire:click="changePriority('{{ $priority }}')"
            wire:confirm="Are you sure you want to change priority for this ticket to {{ $priority }}"
            @click="open = false" class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-800 transition">
            {{ $priority }}
        </button>
    @endforeach

</x-dropdown-btn>
