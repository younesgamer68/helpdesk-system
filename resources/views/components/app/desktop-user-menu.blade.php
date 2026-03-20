<flux:dropdown position="bottom" align="start">
    <flux:button variant="ghost"
        class="w-full h-10 !p-0 min-w-0 rounded-lg text-teal-300 hover:bg-teal-800 hover:text-white"
        data-test="sidebar-menu-button">
        <div class="flex items-center w-full">
            <div class="w-10 flex items-center justify-center shrink-0">
                <span
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-teal-700/50 text-xs font-semibold text-teal-200">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <span class="sidebar-label truncate text-sm font-medium">{{ auth()->user()->name }}</span>
        </div>
    </flux:button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
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
