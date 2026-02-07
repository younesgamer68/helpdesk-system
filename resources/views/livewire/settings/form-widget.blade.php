<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Form Widget') }}</flux:heading>

    <x-settings.layout :heading="__('Create Form Widget')" max-width="max-w-full" :subheading="__('form widget creation')">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <x-flash-message />
            <div class="space-y-8 col-span-2">
                {{-- Widget Status Toggle --}}
                <div
                    class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ __('Widget Status') }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $is_active ? __('Your widget is active and accepting submissions') : __('Your widget is currently disabled') }}
                        </div>
                    </div>
                    <button wire:click="toggleActive"
                        class="px-4 py-2 rounded {{ $is_active ? 'bg-blue-600 text-white' : 'border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $is_active ? __('Active') : __('Inactive') }}
                    </button>
                </div>

                {{-- Appearance Settings --}}
                <div
                    class="bg-white grid grid-cols-2 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold">{{ __('Appearance') }}</h2>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Primary Color --}}
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-1">
                                    <label class="block text-sm font-medium mb-2">{{ __('Primary Color') }}</label>
                                    <input wire:model.live="primary_color" type="color"
                                        class="h-10 w-full rounded border border-gray-300 dark:border-zinc-700 cursor-pointer">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium mb-2">&nbsp;</label>
                                    <input wire:model.blur="primary_color" type="text" placeholder="#14b8a6"
                                        class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white">
                                </div>
                            </div>

                            {{-- Form Title --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">{{ __('Form Title') }}</label>
                                <input wire:model.blur="form_title" type="text" placeholder="Submit a Support Ticket"
                                    class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white">
                                @error('form_title')
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Welcome Message --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    {{ __('Welcome Message') }}
                                    <span class="text-sm text-gray-500">({{ __('Optional') }})</span>
                                </label>
                                <textarea wire:model.blur="welcome_message" rows="3" placeholder="We're here to help! Fill out the form below..."
                                    class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white"></textarea>
                                @error('welcome_message')
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Success Message --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">{{ __('Success Message') }}</label>
                                <textarea wire:model.blur="success_message" rows="2"
                                    class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white"></textarea>
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
                                    class="w-4 h-4 rounded border-gray-300 dark:border-zinc-700">
                                <span class="text-sm">{{ __('Require phone number') }}</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="show_category"
                                    class="w-4 h-4 rounded border-gray-300 dark:border-zinc-700">
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
                                    <select wire:model.live="default_assigned_to"
                                        class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white">
                                        <option value="">{{ __('Unassigned') }}</option>
                                        @foreach ($this->agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_assigned_to')
                                        <div class="text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Default Status --}}
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium">{{ __('Default Status') }}</label>
                                        <select wire:model.live="default_status"
                                            class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white">
                                            <option value="pending">{{ __('Pending') }}</option>
                                            <option value="open">{{ __('Open') }}</option>
                                        </select>
                                        @error('default_status')
                                            <div class="text-sm text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Default Priority --}}
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium">{{ __('Default Priority') }}</label>
                                        <select wire:model.live="default_priority"
                                            class="w-full rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white">
                                            <option value="low">{{ __('Low') }}</option>
                                            <option value="medium">{{ __('Medium') }}</option>
                                            <option value="high">{{ __('High') }}</option>
                                            <option value="urgent">{{ __('Urgent') }}</option>
                                        </select>
                                        @error('default_priority')
                                            <div class="text-sm text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end px-5">
                                <button wire:click="save" type="button"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    {{ __('Save Settings') }}
                                </button>

                                <x-action-message on="widget-updated">
                                    {{ __('Saved.') }}
                                </x-action-message>
                            </div>
                        </div>
                    </div>






                </div>




            </div>
            <div class="space-y-8">
                {{-- Widget Links & Embed Code --}}
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                        <h2 class="text-lg font-semibold">{{ __('Integration') }}</h2>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Direct Link --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ __('Direct Link') }}</label>
                            <div class="flex gap-2">
                                <input readonly value="{{ $widgetSetting->widget_url }}"
                                    class="flex-1 rounded border border-gray-300 dark:border-zinc-700 px-3 py-2 font-mono text-sm bg-gray-50 dark:bg-zinc-800">
                                <button wire:click="copyToClipboard('{{ $widgetSetting->widget_url }}', 'direct')"
                                    type="button"
                                    class="border border-gray-300 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800 px-4 py-2 rounded transition-colors">
                                    {{ $copiedKey === 'direct' ? __('Copied!') : __('Copy') }}
                                </button>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                {{ __('Share this link directly with customers or add it to your website') }}
                            </div>
                        </div>

                        {{-- Iframe Embed Code --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ __('Embed Code (iFrame)') }}</label>
                            <div class="relative">
                                <pre
                                    class="bg-gray-100 dark:bg-zinc-950 border border-gray-300 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto"><code>{{ $widgetSetting->iframe_code }}</code></pre>
                                <button wire:click="copyToClipboard(@js($widgetSetting->iframe_code), 'iframe')"
                                    type="button"
                                    class="absolute top-2 right-2 border border-gray-300 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800 px-3 py-1 rounded text-sm transition-colors bg-white dark:bg-zinc-900">
                                    {{ $copiedKey === 'iframe' ? __('Copied!') : __('Copy') }}
                                </button>

                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
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
                                <button wire:click="regenerateKey" type="button"
                                    wire:confirm="Are you sure? This will break your current embed code!"
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
    </x-settings.layout>
</section>
