<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Notification Preferences') }}</flux:heading>

    <x-app.settings.layout :heading="__('Notifications')" :subheading="__('Choose which notifications you want to receive')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <flux:fieldset>
                <flux:legend>{{ __('Email & In-App Notifications') }}</flux:legend>

                <div class="space-y-4">
                    <flux:switch wire:model="preferences.ticket_assigned" label="{{ __('Ticket Assigned') }}"
                        description="{{ __('When a ticket is assigned to you.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.ticket_reassigned" label="{{ __('Ticket Reassigned') }}"
                        description="{{ __('When a ticket you were handling is reassigned.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.client_replied" label="{{ __('Client Replied') }}"
                        description="{{ __('When a client replies to a ticket you are assigned to.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.status_changed" label="{{ __('Status Changed') }}"
                        description="{{ __('When a ticket status is updated.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.internal_note" label="{{ __('Internal Notes') }}"
                        description="{{ __('When an internal note is added to a ticket.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.ticket_submitted" label="{{ __('New Ticket Submitted') }}"
                        description="{{ __('When a new ticket is submitted by a customer.') }}" />
                    <flux:separator variant="subtle" />

                    <flux:switch wire:model="preferences.team_assigned" label="{{ __('Team Assignment') }}"
                        description="{{ __('When you are added to or removed from a team.') }}" />
                </div>
            </flux:fieldset>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

                <x-ui.action-message class="me-3" on="notification-preferences-saved">
                    {{ __('Saved.') }}
                </x-ui.action-message>
            </div>
        </form>
    </x-app.settings.layout>
</section>
