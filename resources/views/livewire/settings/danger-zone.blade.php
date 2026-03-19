<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('Danger Zone')" :subheading="__('Irreversible actions — proceed with caution')">
        <div class="my-6 space-y-6">
            <div
                class="rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/50 dark:bg-red-900/10 p-5 space-y-4">
                <div>
                    <flux:heading class="text-red-700 dark:text-red-400">{{ __('Delete Account') }}</flux:heading>
                    <flux:text size="sm" class="mt-1 text-red-600/80 dark:text-red-400/80">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently removed. This action cannot be undone.') }}
                    </flux:text>
                </div>

                <flux:modal.trigger name="confirm-user-deletion">
                    <flux:button variant="danger">{{ __('Delete my account') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    </x-app.settings.layout>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure?') }}</flux:heading>
                <flux:subheading>
                    {{ __('This will permanently delete your account and all associated data. Enter your password to confirm.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" autocomplete="current-password" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" type="submit">{{ __('Delete account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
