<div>
    @if (!$is_enabled)
        <div class="mb-6 rounded-xl border-2 border-red-500 bg-red-100 px-5 py-4 text-red-900 shadow-lg">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.82 21h16.36a2 2 0 001.71-3.14l-8.18-14a2 2 0 00-3.42 0z" />
                </svg>
                <div>
                    <p class="text-base font-extrabold tracking-wide">SLA monitoring is OFF.</p>
                    <p class="mt-1 text-sm font-semibold">No SLA countdowns or breach notifications will fire until
                        monitoring is enabled.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 mb-8 text-zinc-200">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">SLA Configuration</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage Service Level Agreement response times based on ticket
                    priority.
                </p>
            </div>
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <span class="text-sm font-medium text-zinc-300">Enable SLA Monitoring</span>
                    <div class="relative">
                        <input type="checkbox" wire:model="is_enabled" class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-zinc-700 bg-opacity-50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500 duration-300">
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <form wire:submit="save">
            <div class="mb-6 bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                <div class="mb-3">
                    <h3 class="font-medium text-zinc-200">Company Timezone</h3>
                    <p class="text-xs text-zinc-500 mt-1">Used for SLA deadlines display and operational reporting.</p>
                </div>
                <select wire:model="timezone"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                    @foreach ($this->timezones as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('timezone')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Low Priority --}}
                <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <h3 class="font-medium text-zinc-200">Low Priority</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="low_minutes" min="1"
                            class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                        <span class="text-sm text-zinc-400">minutes</span>
                    </div>
                    @error('low_minutes')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-zinc-500 mt-2">e.g. 1440 = 24 hours</p>
                </div>

                {{-- Medium Priority --}}
                <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <h3 class="font-medium text-zinc-200">Medium Priority</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="medium_minutes" min="1"
                            class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                        <span class="text-sm text-zinc-400">minutes</span>
                    </div>
                    @error('medium_minutes')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-zinc-500 mt-2">e.g. 480 = 8 hours</p>
                </div>

                {{-- High Priority --}}
                <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                        <h3 class="font-medium text-zinc-200">High Priority</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="high_minutes" min="1"
                            class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                        <span class="text-sm text-zinc-400">minutes</span>
                    </div>
                    @error('high_minutes')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-zinc-500 mt-2">e.g. 120 = 2 hours</p>
                </div>

                {{-- Urgent Priority --}}
                <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <h3 class="font-medium text-zinc-200">Urgent Priority</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="urgent_minutes" min="1"
                            class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                        <span class="text-sm text-zinc-400">minutes</span>
                    </div>
                    @error('urgent_minutes')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-zinc-500 mt-2">e.g. 30 = 30 minutes</p>
                </div>
            </div>

            {{-- Ticket Lifecycle Section --}}
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-zinc-200 mb-1">Ticket Lifecycle</h2>
                <p class="text-sm text-zinc-400 mb-6">Configure automatic ticket closure, reopen windows, and cleanup
                    schedules.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Warning Hours --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Closure Warning</h3>
                        <p class="text-xs text-zinc-500 mb-3">Hours before closure to send customer warning</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="warning_hours" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">hours</span>
                        </div>
                        @error('warning_hours')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 24 hours</p>
                    </div>

                    {{-- Auto Close Hours --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Auto-Close After</h3>
                        <p class="text-xs text-zinc-500 mb-3">Hours after resolution before ticket is auto-closed</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="auto_close_hours" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">hours</span>
                        </div>
                        @error('auto_close_hours')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 48 hours</p>
                    </div>

                    {{-- Reopen Hours --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Reopen Window</h3>
                        <p class="text-xs text-zinc-500 mb-3">Hours after resolution customer can reopen ticket</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="reopen_hours" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">hours</span>
                        </div>
                        @error('reopen_hours')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 48 hours</p>
                    </div>

                    {{-- Linked Ticket Days --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Follow-up Ticket Window</h3>
                        <p class="text-xs text-zinc-500 mb-3">Days after closure customer can create a linked ticket
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="linked_ticket_days" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">days</span>
                        </div>
                        @error('linked_ticket_days')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 7 days</p>
                    </div>

                    {{-- Soft Delete Days --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Archive After</h3>
                        <p class="text-xs text-zinc-500 mb-3">Days after closure before ticket is archived
                            (soft-deleted)</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="soft_delete_days" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">days</span>
                        </div>
                        @error('soft_delete_days')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 30 days</p>
                    </div>

                    {{-- Hard Delete Days --}}
                    <div class="bg-zinc-800/50 p-4 rounded-lg border border-zinc-800">
                        <h3 class="font-medium text-zinc-200 mb-1">Permanently Delete After</h3>
                        <p class="text-xs text-zinc-500 mb-3">Days after archiving before ticket is permanently deleted
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="hard_delete_days" min="1"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-zinc-200 focus:outline-none focus:border-teal-500">
                            <span class="text-sm text-zinc-400 whitespace-nowrap">days</span>
                        </div>
                        @error('hard_delete_days')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-zinc-500 mt-2">Default: 90 days</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
