<?php

namespace Database\Factories;

use App\Models\AutoTriageRule;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AutoTriageRule> */
class AutoTriageRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->sentence(3),
            'type' => 'keyword',
            'keywords' => [fake()->word(), fake()->word()],
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'is_active' => true,
            'order' => 0,
        ];
    }
}
