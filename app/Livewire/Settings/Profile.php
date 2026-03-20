<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\TicketCategory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $avatar;

    public array $selectedCategories = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;

        if (Auth::user()->isOperator()) {
            $categories = Auth::user()->categories()->pluck('ticket_category_id')->map(fn ($id) => (string) $id)->toArray();
            if (Auth::user()->specialty_id && ! in_array((string) Auth::user()->specialty_id, $categories)) {
                $categories[] = (string) Auth::user()->specialty_id;
            }
            $this->selectedCategories = $categories;
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function categories()
    {
        return TicketCategory::where('company_id', Auth::user()->company_id)->get();
    }

    public function toggleAvailability(): void
    {
        $user = Auth::user();
        $user->is_available = ! $user->is_available;
        $user->save();
    }

    public function updateSpecialties(): void
    {
        $user = Auth::user();
        $user->specialty_id = ! empty($this->selectedCategories) ? (int) $this->selectedCategories[0] : null;
        $user->save();
        $user->categories()->sync($this->selectedCategories);
        $user->load(['categories', 'specialty']);

        $this->dispatch('show-toast', message: 'Specialties updated successfully.', type: 'success');
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}
