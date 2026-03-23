<?php

namespace App\Livewire\Onboarding;

use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Wizard extends Component
{
    public int $currentStep = 1;

    // Step 1: Profile
    public ?string $timezone = 'UTC';

    public bool $slaIsEnabled = false;

    public int $slaLowMinutes = 1440;

    public int $slaMediumMinutes = 480;

    public int $slaHighMinutes = 120;

    public int $slaUrgentMinutes = 30;

    // Step 2: Categories
    public array $categories = [
        ['name' => 'General Support'],
        ['name' => 'Billing'],
        ['name' => 'Technical'],
    ];

    // Step 3: Invite Team
    public array $invites = [
        ['email' => '', 'name' => '', 'role' => 'operator', 'team_id' => ''],
    ];

    // Step 4: Widget
    public string $widgetThemeMode = 'dark';

    public string $widgetFormTitle = 'Submit a Support Ticket';

    public string $widgetWelcomeMessage = 'How can we help you today?';

    public string $widgetSuccessMessage = 'Your ticket has been created successfully. We will get back to you shortly.';

    public bool $widgetRequirePhone = false;

    public bool $widgetShowCategory = true;

    public function mount(): void
    {
        $company = Auth::user()->company;
        $this->timezone = $company->timezone ?? 'UTC';

        $slaPolicy = SlaPolicy::query()->where('company_id', $company->id)->first();

        if ($slaPolicy) {
            $this->slaIsEnabled = $slaPolicy->is_enabled;
            $this->slaLowMinutes = $slaPolicy->low_minutes;
            $this->slaMediumMinutes = $slaPolicy->medium_minutes;
            $this->slaHighMinutes = $slaPolicy->high_minutes;
            $this->slaUrgentMinutes = $slaPolicy->urgent_minutes;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'timezone' => 'required|string|timezone',
            ]);

            Auth::user()->company->update([
                'timezone' => $this->timezone,
            ]);
        }

        if ($this->currentStep === 2) {
            $this->validate([
                'slaIsEnabled' => 'boolean',
                'slaLowMinutes' => 'required|integer|min:1',
                'slaMediumMinutes' => 'required|integer|min:1',
                'slaHighMinutes' => 'required|integer|min:1',
                'slaUrgentMinutes' => 'required|integer|min:1',
            ]);
        }

        if ($this->currentStep === 3) {
            $this->validate([
                'categories' => 'required|array|min:1',
                'categories.*.name' => 'required|string|max:255',
            ]);
        }

        if ($this->currentStep === 4) {
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

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function skipStep()
    {
        if ($this->currentStep < 5) {
            $this->currentStep++;
        } else {
            $this->skipEntireWizard();
        }
    }

    public function skipEntireWizard()
    {
        $company = Auth::user()->company;

        // Save defaults for currently reachable data
        $company->update([
            'timezone' => $this->timezone ?? 'UTC',
            'onboarding_completed_at' => now(),
        ]);

        $this->saveSlaPolicy($company);

        // Default categories if none exist
        if ($company->categories()->count() === 0) {
            foreach ($this->categories as $categoryData) {
                if (! empty($categoryData['name'])) {
                    $company->categories()->create([
                        'name' => $categoryData['name'],
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

    public function addCategory(): void
    {
        $this->categories[] = ['name' => ''];
    }

    public function removeCategory($index): void
    {
        if (count($this->categories) > 1) {
            unset($this->categories[$index]);
            $this->categories = array_values($this->categories); // Re-index array
        }
    }

    public function addInvite(): void
    {
        $this->invites[] = ['email' => '', 'name' => '', 'role' => 'operator', 'team_id' => ''];
    }

    public function removeInvite($index): void
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

        $company = Auth::user()->company;

        // Save Steps 1 & 2 (Company Profile + SLA)
        $company->update([
            'timezone' => $this->timezone,
            'onboarding_completed_at' => now(),
        ]);

        $this->saveSlaPolicy($company);

        // Save Step 3 (Categories)
        foreach ($this->categories as $categoryData) {
            $company->categories()->updateOrCreate(
                ['name' => $categoryData['name']],
                []
            );
        }

        // Save Step 4 (Invites)
        foreach ($this->invites as $inviteData) {
            if (! empty($inviteData['email'])) {
                $expiresAt = now()->addHours((int) config('auth.invitation_expire_hours', 72));

                /** @var \App\Models\User $user */
                $user = $company->user()->create([
                    'name' => $inviteData['name'],
                    'email' => $inviteData['email'],
                    'password' => null,
                    'role' => $inviteData['role'],
                    'email_verified_at' => null, // Require setting up password
                    'invite_sent_at' => now(),
                    'invite_expires_at' => $expiresAt,
                    'invite_expired_notified_at' => null,
                ]);

                $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('invitations.accept', $expiresAt, ['user' => $user->id]);
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));

                if (! empty($inviteData['team_id'])) {
                    $team = Team::find($inviteData['team_id']);
                    if ($team && $team->company_id === Auth::user()->company_id) {
                        $team->members()->syncWithoutDetaching([$user->id => ['role' => 'member']]);
                    }
                }
            }
        }

        // Save Step 5 (Widget Setting)
        $company->widgetSettings()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'widget_key' => \Illuminate\Support\Str::random(32),
                'theme_mode' => $this->widgetThemeMode,
                'form_title' => $this->widgetFormTitle,
                'welcome_message' => $this->widgetWelcomeMessage,
                'success_message' => $this->widgetSuccessMessage,
                'require_phone' => $this->widgetRequirePhone,
                'show_category' => $this->widgetShowCategory,
                'is_active' => true,
            ]
        );

        return redirect()->route('tickets', ['company' => $company->slug]);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.onboarding.wizard');
    }

    #[Computed]
    public function teamsForWizard()
    {
        return Team::where('company_id', Auth::user()->company_id)
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();
    }

    protected function saveSlaPolicy(Company $company): void
    {
        SlaPolicy::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'is_enabled' => $this->slaIsEnabled,
                'low_minutes' => $this->slaLowMinutes,
                'medium_minutes' => $this->slaMediumMinutes,
                'high_minutes' => $this->slaHighMinutes,
                'urgent_minutes' => $this->slaUrgentMinutes,
            ]
        );
    }
}
