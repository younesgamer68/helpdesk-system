<div x-data="{ open: false }" class="relative w-full">
    <!-- BUTTON -->
    <button @click="open = !open" @click.outside="open = false" type="button"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white rounded-lg transition text-sm flex items-center justify-center gap-2']) }}>


        {{ $title }}

    </button>

    <!-- DROPDOWN -->
    <div x-show="open" x-transition
        class="absolute z-50 bottom-full mb-2 w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-lg max-h-64 overflow-y-auto">
        {{ $slot }}
    </div>
</div>
