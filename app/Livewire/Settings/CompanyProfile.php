<?php

namespace App\Livewire\Settings;

use App\Models\TenantConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanyProfile extends Component
{
    use WithFileUploads;

    public string $companyName = '';

    public string $companyEmail = '';

    public ?string $companyPhone = '';

    public string $companySlug = '';

    public ?string $website = '';

    public $logo;

    public int $maxTicketsPerAgent = 20;

    public function mount(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $company = Auth::user()->company;
        $this->companyName = $company->name;
        $this->companyEmail = $company->email;
        $this->companyPhone = $company->phone ?? '';
        $this->companySlug = $company->slug;
        $this->website = $company->website ?? '';

        $config = TenantConfig::query()->firstOrCreate(
            ['company_id' => $company->id],
            ['max_tickets_per_agent' => 20]
        );
        $this->maxTicketsPerAgent = $config->max_tickets_per_agent;
    }

    public function save(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $company = Auth::user()->company;

        $this->companySlug = Str::of($this->companySlug)->slug('-')->value();

        $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'companyEmail' => ['required', 'email', 'max:255'],
            'companyPhone' => ['nullable', 'string', 'max:20'],
            'companySlug' => ['required', 'string', 'max:63',
                Rule::unique('companies', 'slug')->ignore($company->id)],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'maxTicketsPerAgent' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'companySlug.required' => 'Please enter a valid company slug.',
            'companySlug.unique' => 'This slug is already taken.',
        ]);

        $slugChanged = $company->slug !== $this->companySlug;

        $data = [
            'name' => $this->companyName,
            'email' => $this->companyEmail,
            'phone' => $this->companyPhone ?: null,
            'slug' => $this->companySlug,
            'website' => $this->website ?: null,
        ];

        if ($this->logo) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            $data['logo'] = $this->logo->store('logos', 'public');
        }

        $company->update($data);

        TenantConfig::query()->updateOrCreate(
            ['company_id' => $company->id],
            ['max_tickets_per_agent' => $this->maxTicketsPerAgent]
        );

        if ($slugChanged) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $protocol = app()->environment('local') ? 'http' : 'https';
            $newLoginUrl = $protocol.'://'.config('app.domain').'/login';
            $this->redirect($newLoginUrl);

            return;
        }

        $this->dispatch('company-profile-updated');
    }

    public function render()
    {
        return view('livewire.settings.company-profile');
    }
}
