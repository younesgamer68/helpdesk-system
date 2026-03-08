<?php

namespace Database\Factories;

use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketCategory>
 */
class TicketCategoryFactory extends Factory
{
    protected $model = TicketCategory::class;

    public function definition(): array
    {
        $categories = ['Network', 'Software', 'Hardware', 'Security', 'Database', 'General'];

        return [
            'company_id' => 1,
            'name' => fake()->unique()->randomElement($categories),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'default_priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        ];
    }
}
