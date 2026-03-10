{{-- Loading Overlay — shown when switching theme or language --}}
<div x-show="$store.ui.loading"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex items-center justify-center"
    :class="$store.ui.darkMode ? 'bg-black/60' : 'bg-white/60'"
    style="display:none; backdrop-filter: blur(4px);">
    <div class="flex flex-col items-center gap-3">
        <svg class="loading-spinner h-10 w-10" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'" viewBox="0 0 50 50">
            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"
                stroke-linecap="round" stroke-dasharray="90 150" stroke-dashoffset="0" />
        </svg>
    </div>
</div>

<style>
    @keyframes loading-spin {
        to { transform: rotate(360deg); }
    }
    .loading-spinner {
        animation: loading-spin 0.8s linear infinite;
    }
</style>
