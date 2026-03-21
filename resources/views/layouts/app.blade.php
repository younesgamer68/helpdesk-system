<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="animate-enter">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
