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

    public string $companySlug = '';

    public $logo;

    public int $maxTicketsPerAgent = 20;

    public function mount(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $company = Auth::user()->company;
        $this->companyName = $company->name;
        $this->companySlug = $company->slug;

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
            'companySlug' => ['required', 'string', 'max:63',
                Rule::unique('companies', 'slug')->ignore($company->id)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'maxTicketsPerAgent' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'companySlug.required' => 'Please enter a valid company slug.',
            'companySlug.unique' => 'This slug is already taken.',
        ]);

        $slugChanged = $company->slug !== $this->companySlug;

        $data = [
            'name' => $this->companyName,
            'slug' => $this->companySlug,
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

    public function resetLogo(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $company = Auth::user()->company;

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
            $company->update(['logo' => null]);
        }

        $this->logo = null;

        $this->dispatch('company-profile-updated');
        $this->dispatch('show-toast', message: 'Company logo removed successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.company-profile');
    }
}
