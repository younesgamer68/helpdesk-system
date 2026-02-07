<?php

namespace App\Livewire\Settings;

use App\Models\WidgetSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class FormWidget extends Component
{
    public $widgetSetting;

    // Appearance
    public $primary_color = '#14b8a6';
    public $form_title = 'Submit a Support Ticket';
    public $welcome_message = '';
    public $success_message = 'Thank you! Please check your email to verify your ticket.';

    // Form fields
    public $require_phone = false;
    public $show_category = true;

    // Defaults
    public $default_assigned_to = '';
    public $default_status = 'pending';
    public $default_priority = 'medium';

    // UI
    public $is_active = true;
    public ?string $copiedKey = null;


    public function mount()
    {
        $this->widgetSetting = WidgetSetting::firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'primary_color' => $this->primary_color,
                'form_title' => $this->form_title,
                'success_message' => $this->success_message,
                'default_status' => $this->default_status,
                'default_priority' => $this->default_priority,
            ]
        );

        // Load existing settings
        $this->fill([
            'primary_color' => $this->widgetSetting->primary_color,
            'form_title' => $this->widgetSetting->form_title,
            'welcome_message' => $this->widgetSetting->welcome_message,
            'success_message' => $this->widgetSetting->success_message,
            'require_phone' => $this->widgetSetting->require_phone,
            'show_category' => $this->widgetSetting->show_category,
            'default_assigned_to' => $this->widgetSetting->default_assigned_to,
            'default_status' => $this->widgetSetting->default_status,
            'default_priority' => $this->widgetSetting->default_priority,
            'is_active' => $this->widgetSetting->is_active,
        ]);
    }

    #[Computed]
    public function agents()
    {
        return Auth::user()->company->user()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Auth::user()->company->categories()
            ->select('id', 'name')
            ->get();
    }

    public function save()
    {
        $this->validate([
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'form_title' => 'required|string|max:100',
            'welcome_message' => 'nullable|string|max:500',
            'success_message' => 'required|string|max:500',
            'require_phone' => 'boolean',
            'show_category' => 'boolean',
            'default_assigned_to' => 'nullable|exists:users,id',
            'default_status' => 'required|in:pending,open',
            'default_priority' => 'required|in:low,medium,high,urgent',
            'is_active' => 'boolean',
        ]);

        $this->widgetSetting->update([
            'primary_color' => $this->primary_color,
            'form_title' => $this->form_title,
            'welcome_message' => $this->welcome_message,
            'success_message' => $this->success_message,
            'require_phone' => $this->require_phone,
            'show_category' => $this->show_category,
            'default_assigned_to' => $this->default_assigned_to ?: null,
            'default_status' => $this->default_status,
            'default_priority' => $this->default_priority,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('show-toast', message: 'Widget settings saved successfully!', type: 'success');
    }

    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        $this->widgetSetting->update(['is_active' => $this->is_active]);

        $status = $this->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', message: "Widget {$status} successfully!", type: 'success');
    }

    public function regenerateKey()
    {
        $this->widgetSetting->update([
            'widget_key' => WidgetSetting::generateUniqueKey()
        ]);

        $this->widgetSetting->refresh();
        $this->dispatch('show-toast', message: 'Widget key regenerated! Please update your embed code.', type: 'warning');
    }


    public function copyToClipboard(string $text, string $key)
    {
        $this->copiedKey = $key;

        $this->dispatch('copy-to-clipboard', text: $text);
        $this->dispatch(
            'show-toast',
            message: 'Copied to clipboard!',
            type: 'success'
        );
        $this->js('setTimeout(() => $wire.set("copiedKey", null), 2000)');
    }


    public function render()
    {
        return view('livewire.settings.form-widget');
    }
}
