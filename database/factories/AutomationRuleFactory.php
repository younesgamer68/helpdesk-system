<?php

namespace Database\Factories;

use App\Models\AutomationRule;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationRule>
 */
class AutomationRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            AutomationRule::TYPE_ASSIGNMENT,
            AutomationRule::TYPE_KEYWORD_ASSIGNMENT,
            AutomationRule::TYPE_PRIORITY,
            AutomationRule::TYPE_AUTO_REPLY,
            AutomationRule::TYPE_ESCALATION,
        ]);

        return [
            'company_id' => Company::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'type' => $type,
            'conditions' => $this->generateConditions($type),
            'actions' => $this->generateActions($type),
            'is_active' => true,
            'priority' => fake()->numberBetween(0, 100),
            'executions_count' => 0,
            'last_executed_at' => null,
        ];
    }

    /**
     * Generate conditions based on rule type.
     *
     * @return array<string, mixed>
     */
    protected function generateConditions(string $type): array
    {
        return match ($type) {
            AutomationRule::TYPE_ASSIGNMENT => [
                'category_id' => null,
                'priority' => null,
            ],
            AutomationRule::TYPE_PRIORITY => [
                'keywords' => ['urgent', 'critical', 'asap'],
                'category_id' => null,
            ],
            AutomationRule::TYPE_KEYWORD_ASSIGNMENT => [
                'keywords' => ['network', 'vpn', 'router'],
                'without_category' => true,
            ],
            AutomationRule::TYPE_AUTO_REPLY => [
                'on_create' => true,
            ],
            AutomationRule::TYPE_ESCALATION => [
                'idle_hours' => 24,
                'status' => ['pending', 'open'],
            ],
            default => [],
        };
    }

    /**
     * Generate actions based on rule type.
     *
     * @return array<string, mixed>
     */
    protected function generateActions(string $type): array
    {
        return match ($type) {
            AutomationRule::TYPE_ASSIGNMENT => [
                'assign_to_specialist' => true,
                'fallback_to_generalist' => true,
            ],
            AutomationRule::TYPE_PRIORITY => [
                'set_priority' => 'urgent',
            ],
            AutomationRule::TYPE_KEYWORD_ASSIGNMENT => [
                'assign_to_operator_id' => null,
                'set_category_id' => null,
            ],
            AutomationRule::TYPE_AUTO_REPLY => [
                'send_email' => true,
                'message' => 'Thank you for your ticket. We will respond shortly.',
            ],
            AutomationRule::TYPE_ESCALATION => [
                'escalate_priority' => true,
                'notify_admin' => true,
            ],
            default => [],
        };
    }

    /**
     * State for assignment rules.
     */
    public function assignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AutomationRule::TYPE_ASSIGNMENT,
            'name' => 'Auto Assignment Rule',
            'conditions' => ['category_id' => null, 'priority' => null],
            'actions' => ['assign_to_specialist' => true, 'fallback_to_generalist' => true],
        ]);
    }

    /**
     * State for priority rules.
     */
    public function priority(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AutomationRule::TYPE_PRIORITY,
            'name' => 'Priority Escalation Rule',
            'conditions' => ['keywords' => ['urgent', 'critical']],
            'actions' => ['set_priority' => 'urgent'],
        ]);
    }

    /**
     * State for auto-reply rules.
     */
    public function autoReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AutomationRule::TYPE_AUTO_REPLY,
            'name' => 'Auto Reply Rule',
            'conditions' => ['on_create' => true],
            'actions' => ['send_email' => true, 'message' => 'We have received your ticket.'],
        ]);
    }

    /**
     * State for escalation rules.
     */
    public function escalation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AutomationRule::TYPE_ESCALATION,
            'name' => 'Escalation Rule',
            'conditions' => ['idle_hours' => 24, 'status' => ['pending', 'open']],
            'actions' => ['escalate_priority' => true, 'notify_admin' => true],
        ]);
    }

    /**
     * State for SLA breach rules.
     */
    public function slaBreach(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AutomationRule::TYPE_SLA_BREACH,
            'name' => 'SLA Breach Rule',
            'conditions' => [],
            'actions' => ['escalate_priority' => true, 'notify_admin' => true],
        ]);
    }

    /**
     * State for inactive rules.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
