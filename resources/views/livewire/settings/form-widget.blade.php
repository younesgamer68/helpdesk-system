<div class="w-full">
    <div class="mb-5 flex justify-between items-center">
        <h1 class="text-3xl ">Form Widget</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <x-ui.flash-message />
        <div class="space-y-8 col-span-2">
            {{-- Widget Status Toggle --}}
            <div
                class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800">
                <div>
                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Widget Status') }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $is_active ? __('Your widget is active and accepting submissions') : __('Your widget is currently disabled') }}
                    </div>
                </div>
                <button wire:click="toggleActive"
                    class="px-4 py-2 rounded {{ $is_active ? 'bg-emerald-600 text-white' : 'border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                    {{ $is_active ? __('Active') : __('Inactive') }}
                </button>
            </div>

            {{-- Appearance Settings --}}
            <div
                class="bg-white grid grid-cols-2 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                <div>
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                        <h2 class="text-lg font-semibold">{{ __('Appearance') }}</h2>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Theme Mode --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">{{ __('Theme Mode') }}</label>
                            <flux:dropdown>
                                <button type="button" class="w-full flex items-center justify-between rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-0">
                                    <span>
                                        {{ $theme_mode === 'dark' ? __('Dark Mode') : __('Light Mode') }}
                                    </span>
                                    <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <flux:menu class="w-[250px]">
                                    <flux:menu.radio.group wire:model.live="theme_mode">
                                        <flux:menu.radio value="dark" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Dark Mode') }}</flux:menu.radio>
                                        <flux:menu.radio value="light" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Light Mode') }}</flux:menu.radio>
                                    </flux:menu.radio.group>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        {{-- Form Title --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">{{ __('Form Title') }}</label>
                            <input wire:model.blur="form_title" type="text" placeholder="Submit a Support Ticket"
                                class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            @error('form_title')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Welcome Message --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">
                                {{ __('Welcome Message') }}
                                <span class="text-sm text-zinc-500">({{ __('Optional') }})</span>
                            </label>
                            <textarea wire:model.blur="welcome_message" rows="3" placeholder="We're here to help! Fill out the form below..."
                                class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                            @error('welcome_message')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Success Message --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">{{ __('Success Message') }}</label>
                            <textarea wire:model.blur="success_message" rows="2"
                                class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                            @error('success_message')
                                <div class="text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <div>
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                        <h2 class="text-lg font-semibold">{{ __('Form Fields') }}</h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="require_phone"
                                class="w-4 h-4 rounded checked:bg-emerald-600 dark:checked:bg-emerald-600 checked:border-emerald-600 dark:checked:border-emerald-600 border-gray-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-600 accent-emerald-600">
                            <span class="text-sm">{{ __('Require phone number') }}</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="show_category"
                                class="w-4 h-4 rounded checked:bg-emerald-600 dark:checked:bg-emerald-600 checked:border-emerald-600 dark:checked:border-emerald-600 border-gray-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-600 accent-emerald-600">
                            <span class="text-sm">{{ __('Show category selector') }}</span>
                        </label>
                    </div>

                    {{-- Default Ticket Settings --}}
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold">{{ __('Default Ticket Settings') }}</h2>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Default Assignee --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">{{ __('Default Assignee') }}</label>
                                <flux:dropdown>
                                    <button type="button" class="w-full flex items-center justify-between rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-0">
                                        <span>
                                            @php
                                                $selectedAgent = $this->agents->firstWhere('id', $default_assigned_to);
                                            @endphp
                                            {{ $selectedAgent ? $selectedAgent->name : __('Unassigned') }}
                                        </span>
                                        <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                    <flux:menu class="w-[250px] max-h-[300px] overflow-y-auto">
                                        <flux:menu.radio.group wire:model.live="default_assigned_to">
                                            <flux:menu.radio value="" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Unassigned') }}</flux:menu.radio>
                                            @foreach ($this->agents as $agent)
                                                <flux:menu.radio value="{{ $agent->id }}" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ $agent->name }}</flux:menu.radio>
                                            @endforeach
                                        </flux:menu.radio.group>
                                    </flux:menu>
                                </flux:dropdown>
                                @error('default_assigned_to')
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Default Status --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium">{{ __('Default Status') }}</label>
                                    <flux:dropdown>
                                        <button type="button" class="w-full flex items-center justify-between rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-0">
                                            <span>
                                                {{ $default_status === 'open' ? __('Open') : __('Pending') }}
                                            </span>
                                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <flux:menu class="w-[250px]">
                                            <flux:menu.radio.group wire:model.live="default_status">
                                                <flux:menu.radio value="pending" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Pending') }}</flux:menu.radio>
                                                <flux:menu.radio value="open" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Open') }}</flux:menu.radio>
                                            </flux:menu.radio.group>
                                        </flux:menu>
                                    </flux:dropdown>
                                    @error('default_status')
                                        <div class="text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Default Priority --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium">{{ __('Default Priority') }}</label>
                                    <flux:dropdown>
                                        <button type="button" class="w-full flex items-center justify-between rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-0">
                                            <span>
                                                @php
                                                    $priorityLabels = [
                                                        'low' => __('Low'),
                                                        'medium' => __('Medium'),
                                                        'high' => __('High'),
                                                        'urgent' => __('Urgent')
                                                    ];
                                                @endphp
                                                {{ $priorityLabels[$default_priority] ?? __('Low') }}
                                            </span>
                                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <flux:menu class="w-[250px]">
                                            <flux:menu.radio.group wire:model.live="default_priority">
                                                <flux:menu.radio value="low" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Low') }}</flux:menu.radio>
                                                <flux:menu.radio value="medium" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Medium') }}</flux:menu.radio>
                                                <flux:menu.radio value="high" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('High') }}</flux:menu.radio>
                                                <flux:menu.radio value="urgent" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">{{ __('Urgent') }}</flux:menu.radio>
                                            </flux:menu.radio.group>
                                        </flux:menu>
                                    </flux:dropdown>
                                    @error('default_priority')
                                        <div class="text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end px-5">
                            <button wire:click="save" type="button"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                {{ __('Save Settings') }}
                            </button>

                            <x-ui.action-message on="widget-updated">
                                {{ __('Saved.') }}
                            </x-action-message>
                        </div>
                    </div>
                </div>






            </div>




        </div>
        <div class="space-y-8">
            {{-- Widget Links & Embed Code --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold">{{ __('Integration') }}</h2>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Direct Link --}}
                    <div>
                        <label class="block text-sm font-medium mb-2">{{ __('Direct Link') }}</label>
                        <div class="flex gap-2">
                            <input readonly value="{{ $widgetSetting->widget_url }}"
                                class="flex-1 rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 font-mono text-sm bg-zinc-50 dark:bg-zinc-800">
                            <button wire:click="copyToClipboard('{{ $widgetSetting->widget_url }}', 'direct')"
                                type="button"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded transition-colors text-sm font-medium">
                                {{ $copiedKey === 'direct' ? __('Copied!') : __('Copy') }}
                            </button>
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                            {{ __('Share this link directly with customers or add it to your website') }}
                        </div>
                    </div>

                    {{-- Iframe Embed Code --}}
                    <div>
                        <label class="block text-sm font-medium mb-2">{{ __('Embed Code (iFrame)') }}</label>
                        <div class="relative">
                            <pre
                                class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto"><code>{{ $widgetSetting->iframe_code }}</code></pre>
                            <button wire:click="copyToClipboard(@js($widgetSetting->iframe_code), 'iframe')"
                                type="button"
                                class="absolute top-2 right-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm transition-colors font-medium">
                                {{ $copiedKey === 'iframe' ? __('Copied!') : __('Copy') }}
                            </button>

                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                            {{ __('Paste this code into your website HTML to embed the form') }}
                        </div>
                    </div>

                    {{-- Widget Key --}}
                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-amber-900 dark:text-amber-200 mb-1">
                                    {{ __('Widget Key') }}</div>
                                <code
                                    class="text-xs text-amber-700 dark:text-amber-300">{{ $widgetSetting->widget_key }}</code>
                            </div>
                            <button @click="confirmAction($wire, null, 'regenerateKey', 'Are you sure?', 'This will break your current embed code!', 'Yes, regenerate it!')" type="button"
                                class="border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 px-3 py-1 rounded text-sm transition-colors">
                                {{ __('Regenerate') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('copy-to-clipboard', event => {
            console.log('Copying…');

            const text = event.detail.text;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                // Fallback (works on insecure origins & iframes)
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
        });
    </script>

</div>
