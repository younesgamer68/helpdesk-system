<?php

namespace App\Livewire\Auth;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class SetupCompany extends Component
{
    public string $name = '';

    public string $companyName = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'companyName' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function mount()
    {
        // If user already has a company, redirect to tickets
        $user = Auth::user();

        if ($user && $user->company_id && $user->company) {
            return redirect()->to(
                'http://'.$user->company->slug.'.'.config('app.domain').'/tickets'
            );
        }

        // Pre-fill name from Google account
        $this->name = $user->name ?? '';
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            // Generate a unique slug
            $baseSlug = Str::slug($this->companyName);
            $slug = $baseSlug;
            $counter = 1;

            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            // Create the company
            $company = Company::create([
                'name' => $this->companyName,
                'slug' => $slug,
                'email' => $user->email,
                'phone' => null,
                'logo' => null,
                'require_client_verification' => false,
            ]);

            // Update the user with name, company_id and admin role
            $user->update([
                'name' => $this->name,
                'company_id' => $company->id,
                'role' => 'admin',
            ]);
        });

        // Refresh user to get the new company relationship
        $user->refresh();

        // Redirect to the company's tickets page
        return redirect()->to(
            'http://'.$user->company->slug.'.'.config('app.domain').'/tickets'
        );
    }

    public function render()
    {
        return view('livewire.auth.setup-company')
            ->layout('layouts.auth');
    }
}
