<div x-data="{ completing: false }" x-init="$wire.on('wizard-completed', (data) => {
    completing = true;
    setTimeout(() => window.location.href = data.url, 700);
})">
    <x-slot name="title">
        Setup your Workspace
    </x-slot>
    @teleport('body')
        {{-- Full-screen blurred backdrop with wizard modal --}}
        <div x-show="!completing" x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] bg-black/30 backdrop-blur-sm flex items-center justify-center p-4">
            <div
                class="w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800">
                <div class="p-6 md:p-8">
                    <div class="mb-8 items-start justify-between flex flex-col md:flex-row gap-4">
                        <div>
                            <flux:heading size="xl" level="1">Setup your Workspace</flux:heading>
                            <flux:subheading>Let's get your helpdesk ready for your customers in 6 simple steps.
                            </flux:subheading>
                            <button wire:click="skipEntireWizard"
                                class="mt-2 text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors underline decoration-dotted underline-offset-4">
                                Skip setup for now (use defaults)
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @for ($i = 1; $i <= 6; $i++)
                                <div
                                    class="w-7 h-7 rounded-full flex items-center justify-center {{ $currentStep >= $i ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} font-medium text-xs transition-colors">
                                    {{ $i }}</div>
                                @if ($i < 6)
                                    <div
                                        class="w-5 h-[2px] {{ $currentStep > $i ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800' }} transition-colors">
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>

                    <form wire:submit="nextStep">
                        {{-- Step 1: Company Profile --}}
                        @if ($currentStep === 1)
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Company Profile</flux:heading>
                                    <flux:subheading>Set your company's timezone for accurate SLAs and business hours.
                                    </flux:subheading>
                                </div>

                                <flux:select wire:model="timezone" label="Timezone"
                                    description="Used to calculate precise SLAs and business hours.">
                                    @foreach ($this->timezones as $value => $label)
                                        <flux:select.option value="{{ $value }}">{{ $label }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                <div
                                    class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                                    <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                    <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next: SLA
                                        Configuration</flux:button>
                                </div>
                            </div>

                            {{-- Step 2: SLA Configuration --}}
                        @elseif($currentStep === 2)
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">SLA Configuration</flux:heading>
                                    <flux:subheading>Set target resolution times for different ticket priorities.
                                    </flux:subheading>
                                </div>

                                <flux:switch wire:model.live="slaIsEnabled" label="Enable SLA Tracking" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <div
                                        class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">Low Priority
                                            </h3>
                                        </div>
                                        <flux:input type="number" wire:model="slaLowMinutes" min="1"
                                            suffix="minutes" description="Default: 1440 (24h)" />
                                    </div>
                                    <div
                                        class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">Medium Priority
                                            </h3>
                                        </div>
                                        <flux:input type="number" wire:model="slaMediumMinutes" min="1"
                                            suffix="minutes" description="Default: 480 (8h)" />
                                    </div>
                                    <div
                                        class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">High Priority
                                            </h3>
                                        </div>
                                        <flux:input type="number" wire:model="slaHighMinutes" min="1"
                                            suffix="minutes" description="Default: 120 (2h)" />
                                    </div>
                                    <div
                                        class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">Urgent Priority
                                            </h3>
                                        </div>
                                        <flux:input type="number" wire:model="slaUrgentMinutes" min="1"
                                            suffix="minutes" description="Default: 30 (30m)" />
                                    </div>
                                </div>

                                <div
                                    class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                                    <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back
                                    </flux:button>
                                    <div class="flex gap-2">
                                        <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                        <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next:
                                            Ticket Categories</flux:button>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3: Ticket Categories --}}
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
                                    <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back
                                    </flux:button>
                                    <div class="flex gap-2">
                                        <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                        <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next:
                                            Teams</flux:button>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: Teams --}}
                        @elseif($currentStep === 4)
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Create Teams</flux:heading>
                                    <flux:subheading>Organize your agents into teams for better ticket routing and
                                        management.</flux:subheading>
                                </div>

                                {{-- Existing teams list --}}
                                @if (count($teams) > 0)
                                    <div class="space-y-3">
                                        @foreach ($teams as $index => $team)
                                            <div
                                                class="flex items-center gap-4 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                                <span class="w-4 h-4 rounded-full shrink-0"
                                                    style="background-color: {{ $team['color'] }}"></span>
                                                <span
                                                    class="flex-1 text-sm font-medium text-zinc-900 dark:text-zinc-200">{{ $team['name'] }}</span>
                                                <flux:button wire:click="removeTeam({{ $index }})" variant="subtle"
                                                    icon="trash" class="text-zinc-400 hover:text-red-500" />
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Add new team form --}}
                                <div
                                    class="flex items-end gap-4 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                    <div class="flex-1">
                                        <flux:input wire:model="newTeamName" label="Team Name"
                                            placeholder="e.g. Engineering" />
                                    </div>
                                    <div class="w-24">
                                        <flux:input type="color" wire:model="newTeamColor" label="Color" />
                                    </div>
                                    <flux:button wire:click="addTeam" variant="primary" icon="plus">Add</flux:button>
                                </div>

                                <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800 mt-6">
                                    <flux:button wire:click="previousStep" variant="subtle" icon="arrow-left">Back
                                    </flux:button>
                                    <div class="flex gap-2">
                                        <flux:button wire:click="skipStep" variant="ghost">Skip step</flux:button>
                                        <flux:button type="submit" variant="primary" icon-trailing="arrow-right">Next:
                                            Invite Team</flux:button>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 5: Invite Team Members --}}
                        @elseif($currentStep === 5)
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Invite Team Members</flux:heading>
                                    <flux:subheading>Bring your agents into the workspace (optional). They will receive an
                                        email to set their password.</flux:subheading>
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
                                                <flux:select wire:model="invites.{{ $index }}.role"
                                                    label="Role">
                                                    <flux:select.option value="agent">Agent</flux:select.option>
                                                    <flux:select.option value="admin">Admin</flux:select.option>
                                                </flux:select>
                                            </div>
                                            <div class="w-full md:w-44">
                                                <flux:select wire:model="invites.{{ $index }}.team_id"
                                                    label="Team" :disabled="$this->teamsForWizard->isEmpty()">
                                                    <flux:select.option value="">No team</flux:select.option>
                                                    @foreach ($this->teamsForWizard as $team)
                                                        <flux:select.option value="{{ $team->id }}">
                                                            {{ $team->name }}</flux:select.option>
                                                    @endforeach
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

                            {{-- Step 6: Customer Widget Setup --}}
                        @elseif($currentStep === 6)
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
                                        <flux:switch wire:model.live="widgetShowCategory"
                                            label="Show category selector" />
                                    </div>

                                    <div
                                        class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-6 flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-700">
                                        <span
                                            class="text-sm text-zinc-500 mb-4 uppercase font-semibold tracking-wider">Widget
                                            Preview</span>

                                        <div
                                            class="w-full max-w-sm {{ $widgetThemeMode === 'dark' ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-zinc-200' }} rounded-2xl shadow-xl overflow-hidden border">
                                            <div
                                                class="px-6 py-5 {{ $widgetThemeMode === 'dark' ? 'bg-emerald-900 border-emerald-800/50' : 'bg-emerald-600 border-emerald-700/50' }} border-b text-white">
                                                <div class="font-semibold text-lg">
                                                    {{ $widgetFormTitle ?: 'Submit a Support Ticket' }}
                                                </div>
                                                <div class="text-sm text-emerald-100 mt-1">
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
                                                        class="h-10 rounded-lg w-full {{ $widgetThemeMode === 'dark' ? 'bg-emerald-500 hover:bg-emerald-400' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white flex items-center justify-center font-medium text-sm mt-4">
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
                                        <flux:button wire:click="skipEntireWizard" variant="ghost">Skip & Finish
                                        </flux:button>
                                        <flux:button wire:click="completeOnboarding" wire:loading.attr="disabled"
                                            variant="primary" icon-trailing="check">Complete Setup</flux:button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    @endteleport
</div>
