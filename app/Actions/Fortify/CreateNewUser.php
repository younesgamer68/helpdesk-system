<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Generate a unique slug
            $baseSlug = Str::slug($input['name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create a company for the new user
            $company = Company::create([
                'name' => $input['name'] . "'s Company",
                'slug' => $slug,
                'email' => $input['email'], // Use user's email as company email
                'phone' => null,
                'logo' => null,
                'require_client_verification' => false,
            ]);

            // Create the user with the company_id
            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'admin',
                'company_id' => $company->id,
            ]);
        });
    }
}
