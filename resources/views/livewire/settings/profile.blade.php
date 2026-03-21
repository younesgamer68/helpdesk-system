<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile Settings') }}</flux:heading>

    <x-app.settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Avatar --}}
            <div>
                <flux:label>{{ __('Avatar') }}</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}"
                            class="w-16 h-16 rounded-full object-cover border-2 border-zinc-200 dark:border-zinc-700">
                    @elseif (Auth::user()->avatar)
                        <img src="{{ Storage::url(Auth::user()->avatar) }}"
                            class="w-16 h-16 rounded-full object-cover border-2 border-zinc-200 dark:border-zinc-700">
                    @else
                        <div
                            class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center text-xl font-bold text-zinc-500 dark:text-zinc-400 border-2 border-zinc-200 dark:border-zinc-700">
                            {{ Auth::user()->initials() }}
                        </div>
                    @endif
                    <div>
                        <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                            class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300 dark:hover:file:bg-zinc-600" />
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus
                autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer"
                                wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-ui.action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-ui.action-message>
            </div>
        </form>

        @if (Auth::user()->isOperator())
            {{-- Availability Toggle --}}
            <div class="my-6 w-full rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Availability') }}
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Toggle whether you are available to receive new ticket assignments.') }}</p>
                    </div>
                    <flux:switch wire:click="toggleAvailability" :checked="Auth::user()->is_available" />
                </div>
            </div>

            {{-- Specialities --}}
            <div class="my-6 w-full rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-1">{{ __('Specialities') }}</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                    {{ __('Select the categories you specialize in. This determines which unassigned tickets you can see.') }}
                </p>

                <div class="space-y-2 mb-4">
                    @foreach ($this->categories as $category)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}"
                                class="w-4 h-4 bg-zinc-50 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-emerald-500 rounded focus:ring-emerald-500">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>

                <flux:button wire:click="updateSpecialties" variant="primary" size="sm">
                    {{ __('Save Specialities') }}</flux:button>
            </div>
        @endif

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
        </x-settings.layout>
</section>
