<div>
    <x-slot name="title">
        Welcome to your Helpdesk Setup
    </x-slot>

    <div class="max-w-3xl mx-auto py-12">
        <div class="mb-8 items-start justify-between flex flex-col md:flex-row gap-4">
            <div>
                <flux:heading size="xl" level="1">Setup your Workspace</flux:heading>
                <flux:subheading>Let's get your helpdesk ready for your customers in 5 simple steps.</flux:subheading>
                <button wire:click="skipEntireWizard"
                    class="mt-2 text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors underline decoration-dotted underline-offset-4">
                    Skip setup for now (use defaults)
                </button>
            </div>

            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep >= 1 ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-sm transition-colors">
                    1</div>
                <div
                    class="w-8 h-[2px] {{ $currentStep >= 2 ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800' }} transition-colors">
                </div>
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep >= 2 ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-sm transition-colors">
                    2</div>
                <div
                    class="w-8 h-[2px] {{ $currentStep >= 3 ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800' }} transition-colors">
                </div>
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep >= 3 ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-sm transition-colors">
                    3</div>
                <div
                    class="w-8 h-[2px] {{ $currentStep >= 4 ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800' }} transition-colors">
                </div>
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep >= 4 ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-sm transition-colors">
                    4</div>
                <div
                    class="w-8 h-[2px] {{ $currentStep >= 5 ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800' }} transition-colors">
                </div>
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep >= 5 ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-sm transition-colors">
                    5</div>
            </div>
        </div>

        <flux:card>
            <form wire:submit="nextStep">
                @if ($currentStep === 1)
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Company Profile</flux:heading>
                            <flux:subheading>Set your company's timezone for accurate SLAs and business hours.
                            </flux:subheading>
                        </div>

                        <flux:input wire:model="timezone" label="Timezone"
                            description="Used to calculate precise SLAs and business hours." />

                        <div
                            class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                            <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                            <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next: SLA
                                Configuration</flux:button>
                        </div>
                    </div>
                @elseif($currentStep === 2)
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">SLA Configuration</flux:heading>
                            <flux:subheading>Set target resolution times for different ticket priorities.
                            </flux:subheading>
                        </div>

                        <flux:switch wire:model.live="slaIsEnabled" label="Enable SLA Tracking" />

                        <div x-show="$wire.slaIsEnabled" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <flux:input type="number" wire:model="slaLowMinutes" label="Low Priority (minutes)"
                                min="1" description="Default: 1440 (24h)" />
                            <flux:input type="number" wire:model="slaMediumMinutes" label="Medium Priority (minutes)"
                                min="1" description="Default: 480 (8h)" />
                            <flux:input type="number" wire:model="slaHighMinutes" label="High Priority (minutes)"
                                min="1" description="Default: 120 (2h)" />
                            <flux:input type="number" wire:model="slaUrgentMinutes" label="Urgent Priority (minutes)"
                                min="1" description="Default: 30 (30m)" />
                        </div>

                        <div
                            class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                            <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back</flux:button>
                            <div class="flex gap-2">
                                <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next: Ticket
                                    Categories</flux:button>
                            </div>
                        </div>
                    </div>
                @elseif($currentStep === 3)
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Ticket Categories</flux:heading>
                            <flux:subheading>Define how incoming tickets should be classified and routed.
                            </flux:subheading>
                        </div>

                        <div class="space-y-4">
                            @foreach ($categories as $index => $category)
                                <div
                                    class="flex items-center gap-4 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                    <div class="flex-1">
                                        <flux:input wire:model="categories.{{ $index }}.name"
                                            placeholder="e.g. Technical Support" />
                                    </div>
                                    <flux:button wire:click="removeCategory({{ $index }})" variant="subtle"
                                        icon="trash" class="text-zinc-400 hover:text-red-500" />
                                </div>
                            @endforeach
                        </div>

                        <flux:button wire:click="addCategory" variant="ghost" icon="plus">Add another category
                        </flux:button>

                        <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                            <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back</flux:button>
                            <div class="flex gap-2">
                                <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next: Invite
                                    Team</flux:button>
                            </div>
                        </div>
                    </div>
                @elseif($currentStep === 4)
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Invite Team Members</flux:heading>
                            <flux:subheading>Bring your agents into the workspace (optional). They will receive an email
                                to set their password.</flux:subheading>
                        </div>

                        <div class="space-y-4">
                            @foreach ($invites as $index => $invite)
                                <div
                                    class="flex flex-col md:flex-row items-end gap-4 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                    <div class="flex-1 w-full">
                                        <flux:input wire:model="invites.{{ $index }}.name" label="Name"
                                            placeholder="Jane Doe" />
                                    </div>
                                    <div class="flex-1 w-full">
                                        <flux:input type="email" wire:model="invites.{{ $index }}.email"
                                            label="Email Address" placeholder="jane@example.com" />
                                    </div>
                                    <div class="w-full md:w-40">
                                        <flux:select wire:model="invites.{{ $index }}.role" label="Role">
                                            <flux:select.option value="agent">Agent</flux:select.option>
                                            <flux:select.option value="admin">Admin</flux:select.option>
                                        </flux:select>
                                    </div>
                                    <flux:button wire:click="removeInvite({{ $index }})" variant="subtle"
                                        icon="trash"
                                        class="text-zinc-400 hover:text-red-500 mb-1 w-full md:w-auto" />
                                </div>
                            @endforeach
                        </div>

                        <flux:button wire:click="addInvite" variant="ghost" icon="plus">Add another member
                        </flux:button>

                        <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                            <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back
                            </flux:button>
                            <div class="flex gap-2">
                                <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next:
                                    Widget Setup</flux:button>
                            </div>
                        </div>
                    </div>
                @elseif($currentStep === 5)
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Customer Support Widget</flux:heading>
                            <flux:subheading>Customize the look and feel of the widget your customers will use.
                            </flux:subheading>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <flux:heading size="sm" class="mb-2">Appearance</flux:heading>
                                <flux:select wire:model.live="widgetThemeMode" label="Theme Mode"
                                    description="Choose between Light and Dark mode for your widget.">
                                    <flux:select.option value="dark">Dark Mode</flux:select.option>
                                    <flux:select.option value="light">Light Mode</flux:select.option>
                                </flux:select>
                                <flux:input wire:model.live="widgetFormTitle" label="Form Title"
                                    placeholder="Submit a Support Ticket" />
                                <flux:textarea wire:model.live="widgetWelcomeMessage" label="Welcome Message"
                                    rows="2" placeholder="How can we help you today?" />
                                <flux:textarea wire:model.live="widgetSuccessMessage" label="Success Message"
                                    rows="2" />

                                <flux:separator />

                                <flux:heading size="sm" class="mb-2">Form Fields</flux:heading>
                                <flux:switch wire:model.live="widgetRequirePhone" label="Require phone number" />
                                <flux:switch wire:model.live="widgetShowCategory" label="Show category selector" />
                            </div>

                            <div
                                class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-6 flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-700">
                                <span class="text-sm text-zinc-500 mb-4 uppercase font-semibold tracking-wider">Widget
                                    Preview</span>

                                <div
                                    class="w-full max-w-sm {{ $widgetThemeMode === 'dark' ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-zinc-200' }} rounded-2xl shadow-xl overflow-hidden border">
                                    <div
                                        class="px-6 py-5 {{ $widgetThemeMode === 'dark' ? 'bg-teal-900 border-teal-800/50' : 'bg-teal-600 border-teal-700/50' }} border-b text-white">
                                        <div class="font-semibold text-lg">{{ $widgetFormTitle ?: 'Submit a Support Ticket' }}
                                        </div>
                                        <div class="text-sm text-teal-100 mt-1">
                                            {{ $widgetWelcomeMessage ?: 'How can we help you today?' }}</div>
                                    </div>
                                    <div class="p-6">
                                        <div class="space-y-3">
                                            @if ($widgetShowCategory)
                                                <div
                                                    class="h-10 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-full flex items-center px-3">
                                                    <span class="text-xs text-zinc-400">Select Category...</span>
                                                </div>
                                            @endif
                                            <div class="h-10 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-full"></div>
                                            <div class="h-10 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-full"></div>
                                            @if ($widgetRequirePhone)
                                                <div
                                                    class="h-10 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-full flex items-center px-3">
                                                    <span class="text-xs text-zinc-400">Phone Number *</span>
                                                </div>
                                            @endif
                                            <div class="h-24 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-full"></div>
                                            <div
                                                class="h-10 rounded-lg w-full {{ $widgetThemeMode === 'dark' ? 'bg-teal-500 hover:bg-teal-400' : 'bg-teal-600 hover:bg-teal-700' }} text-white flex items-center justify-center font-medium text-sm mt-4">
                                                Submit Ticket
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                            <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back
                            </flux:button>
                            <div class="flex gap-2">
                                <flux:button wire:click="skipEntireWizard" variant="ghost">Skip & Finish</flux:button>
                                <flux:button wire:click="completeOnboarding" wire:loading.attr="disabled"
                                    variant="primary" icon-trailing="check">Complete Setup</flux:button>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </flux:card>
    </div>
</div>
