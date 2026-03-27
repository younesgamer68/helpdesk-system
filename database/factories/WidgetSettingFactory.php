<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\WidgetSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WidgetSetting>
 */
class WidgetSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\WidgetSetting>
     */
    protected $model = WidgetSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'widget_key' => WidgetSetting::generateUniqueKey(),
            'is_active' => true,
            'theme_mode' => '#14b8a6',
            'form_title' => 'Submit a Support Ticket',
            'welcome_message' => 'How can we help?',
            'success_message' => 'Thank you! Please check your email to verify your ticket.',
            'require_phone' => false,
            'show_category' => true,
            'default_status' => 'open',
            'default_priority' => 'medium',
        ];
    }
}
