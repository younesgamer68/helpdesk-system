<flux:dropdown position="bottom" align="start" class="mx-3">
    <flux:button variant="ghost"
        class="h-10 w-full flex items-center justify-start rounded-lg transition-all duration-200 hover:translate-x-1 no-underline !text-zinc-400 hover:!bg-zinc-900 hover:!text-white !p-0 min-w-0"
        data-test="sidebar-menu-button">
        <div class="flex items-center w-full">
            <div class="w-10 flex items-center justify-center shrink-0">
                @if (auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}"
                        class="h-7 w-7 rounded-full object-cover border border-zinc-700">
                @else
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-zinc-800 text-xs font-semibold text-zinc-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                @endif
            </div>
            <span class="sidebar-label truncate text-sm font-medium">{{ auth()->user()->name }}</span>
        </div>
    </flux:button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            @if (auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}"
                    class="h-8 w-8 rounded-full object-cover border border-zinc-200 dark:border-zinc-700">
            @else
                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
            @endif
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer" data-test="logout-button">
                {{ __('Log Out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
