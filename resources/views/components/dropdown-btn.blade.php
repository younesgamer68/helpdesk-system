<div x-data="{ open: false }" class="relative w-full">
    <!-- BUTTON -->
    <button @click="open = !open" @click.outside="open = false"
        class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition text-sm flex items-center justify-center gap-2"
        type="button">

        
        {{ $title }}

    </button>

    <!-- DROPDOWN -->
    <div x-show="open" x-transition
        class="absolute z-50 mt-2 w-full bg-zinc-900 border border-zinc-700 rounded-lg overflow-hidden">
       {{ $slot }}
    </div>
</div>
