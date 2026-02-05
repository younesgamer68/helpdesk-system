<x-dropdown-btn>
 
    <x-slot:title>
         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Assign / Reassign
    </x-slot:title>


    @foreach ($agents as $agent)
        <button wire:click="assign({{ $agent->id }})"
            wire:confirm="Are you sure you want to reassign the ticket to {{ $agent->name }}" @click="open = false"
            class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-800 transition">
            {{ $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}
        </button>
    @endforeach

</x-dropdown-btn>
