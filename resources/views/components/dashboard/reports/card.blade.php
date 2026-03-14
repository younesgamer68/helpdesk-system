@props(['title' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 ' . $class]) }}>
    @if ($title)
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
