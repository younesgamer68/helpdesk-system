<?php

namespace App\Livewire\Onboarding;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Wizard extends Component
{
    public int $currentStep = 1;

    // Step 1: Profile
    public ?string $timezone = 'UTC';

    // Step 2: Categories
    public array $categories = [
        ['name' => 'General Support', 'color' => '#3b82f6'],
        ['name' => 'Billing', 'color' => '#10b981'],
        ['name' => 'Technical', 'color' => '#ef4444'],
    ];

    // Step 3: Invite Team
    public array $invites = [
        ['email' => '', 'name' => '', 'role' => 'operator'],
    ];

    // Step 4: Widget
    public string $widgetThemeMode = 'dark';

    public string $widgetFormTitle = 'Submit a Support Ticket';

    public string $widgetWelcomeMessage = 'How can we help you today?';

    public string $widgetSuccessMessage = 'Your ticket has been created successfully. We will get back to you shortly.';

    public bool $widgetRequirePhone = false;

    public bool $widgetShowCategory = true;

    public function mount()
    {
        $company = auth()->user()->company;
        $this->timezone = $company->timezone ?? 'UTC';
    }

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'timezone' => 'required|string|timezone',
            ]);
        }

        if ($this->currentStep === 2) {
            $this->validate([
                'categories' => 'required|array|min:1',
                'categories.*.name' => 'required|string|max:255',
                'categories.*.color' => 'required|string',
            ]);
        }

        if ($this->currentStep === 3) {
            $this->validate([
                'invites' => 'nullable|array',
                'invites.*.email' => 'required|email',
                'invites.*.name' => 'required|string|max:255',
                'invites.*.role' => 'required|in:operator,admin',
            ]);
        }

        if ($this->currentStep < 5) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function skipStep()
    {
        if ($this->currentStep < 4) {
            $this->currentStep++;
        } else {
            $this->skipEntireWizard();
        }
    }

    public function skipEntireWizard()
    {
        $company = auth()->user()->company;

        // Save defaults for currently reachable data
        $company->update([
            'timezone' => $this->timezone ?? 'UTC',
            'onboarding_completed_at' => now(),
        ]);

        // Default categories if none exist
        if ($company->categories()->count() === 0) {
            foreach ($this->categories as $categoryData) {
                if (! empty($categoryData['name'])) {
                    $company->categories()->create([
                        'name' => $categoryData['name'],
                        'color' => $categoryData['color'] ?? '#3b82f6',
                    ]);
                }
            }
        }

        // Default widget settings if none exist
        if (! $company->widgetSettings()->exists()) {
            $company->widgetSettings()->create([
                'widget_key' => \Illuminate\Support\Str::random(32),
                'theme_mode' => $this->widgetThemeMode,
                'form_title' => $this->widgetFormTitle,
                'welcome_message' => $this->widgetWelcomeMessage,
                'success_message' => $this->widgetSuccessMessage,
                'require_phone' => $this->widgetRequirePhone,
                'show_category' => $this->widgetShowCategory,
                'is_active' => true,
            ]);
        }

        return redirect()->route('tickets', ['company' => $company->slug]);
    }

    public function addCategory()
    {
        $this->categories[] = ['name' => '', 'color' => '#3b82f6'];
    }

    public function removeCategory($index)
    {
        if (count($this->categories) > 1) {
            unset($this->categories[$index]);
            $this->categories = array_values($this->categories); // Re-index array
        }
    }

    public function addInvite()
    {
        $this->invites[] = ['email' => '', 'name' => '', 'role' => 'operator'];
    }

    public function removeInvite($index)
    {
        unset($this->invites[$index]);
        $this->invites = array_values($this->invites); // Re-index array
    }

    public function completeOnboarding()
    {
        $this->validate([
            'widgetThemeMode' => 'required|in:dark,light',
            'widgetFormTitle' => 'required|string|max:255',
            'widgetWelcomeMessage' => 'required|string|max:1000',
            'widgetSuccessMessage' => 'required|string|max:1000',
            'widgetRequirePhone' => 'boolean',
            'widgetShowCategory' => 'boolean',
        ]);

        $company = auth()->user()->company;

        // Save Step 1
        $company->update([
            'timezone' => $this->timezone,
            'onboarding_completed_at' => now(),
        ]);

        // Save Step 2
        foreach ($this->categories as $categoryData) {
            $company->categories()->updateOrCreate(
                ['name' => $categoryData['name']],
                ['color' => $categoryData['color']]
            );
        }

        // Save Step 3 (Invites)
        foreach ($this->invites as $inviteData) {
            if (! empty($inviteData['email'])) {
                /** @var \App\Models\User $user */
                $user = $company->user()->create([
                    'name' => $inviteData['name'],
                    'email' => $inviteData['email'],
                    'password' => null,
                    'role' => $inviteData['role'],
                    'email_verified_at' => null, // Require setting up password
                ]);

                $signedUrl = \Illuminate\Support\Facades\URL::signedRoute('invitations.accept', ['user' => $user->id]);
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));
            }
        }

        // Save Step 4 (Widget Setting)
        $company->widgetSettings()->create([
            'widget_key' => \Illuminate\Support\Str::random(32),
            'theme_mode' => $this->widgetThemeMode,
            'form_title' => $this->widgetFormTitle,
            'welcome_message' => $this->widgetWelcomeMessage,
            'success_message' => $this->widgetSuccessMessage,
            'require_phone' => $this->widgetRequirePhone,
            'show_category' => $this->widgetShowCategory,
            'is_active' => true,
        ]);

        return redirect()->route('tickets', ['company' => $company->slug]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.onboarding.wizard');
    }
}
