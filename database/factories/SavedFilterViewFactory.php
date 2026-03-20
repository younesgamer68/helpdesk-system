<?php

namespace Database\Factories;

use App\Models\SavedFilterView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedFilterView>
 */
class SavedFilterViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'filters' => [
                'status' => $this->faker->randomElement(['open', 'in_progress', 'resolved']),
                'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
                'assigned_to' => $this->faker->boolean(40) ? null : $this->faker->numberBetween(1, 50),
                'search' => $this->faker->optional()->words(2, true),
            ],
        ];
    }
}
