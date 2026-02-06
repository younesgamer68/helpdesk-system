<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Company;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'open', 'in_progress', 'resolved', 'closed']);

        // Set resolved_at for resolved/closed tickets
        $resolvedAt = null;
        if (in_array($status, ['resolved', 'closed'])) {
            $resolvedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        }

        // Set closed_at for closed tickets
        $closedAt = null;
        if ($status === 'closed') {
            $closedAt = $this->faker->dateTimeBetween($resolvedAt ?? '-30 days', 'now');
        }

        $createdAt = $this->faker->dateTimeBetween('-60 days', 'now');

        return [
            'company_id' => 1, // Will be overridden when called
            'ticket_number' => 'TKT-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->boolean(70) ? $this->faker->phoneNumber() : null,
            'subject' => $this->generateSubject(),
            'description' => $this->generateDescription(),
            'status' => $status,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'assigned_to' => null, // Will be set in seeder
            'category_id' => null, // Will be set in seeder
            'verified' => true,
            'verification_token' => null,
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ];
    }

    /**
     * Generate realistic ticket subjects
     */
    private function generateSubject(): string
    {
        $subjects = [
            // Login/Authentication Issues
            'Unable to login to account',
            'Password reset not working',
            'Two-factor authentication failing',
            'Account locked after failed attempts',
            'Login page showing error',
            'Cannot access my account',
            'Forgot password email not received',

            // Performance Issues
            'Dashboard loading very slowly',
            'Application freezing frequently',
            'Page timeout errors',
            'Slow response time',
            'Website performance issues',
            'Long loading times',

            // Feature Requests
            'Feature request: Dark mode',
            'Request for mobile app',
            'Integration with third-party tools',
            'Export to Excel feature needed',
            'Bulk actions functionality',
            'Advanced search filters',

            // Billing/Payment
            'Payment processing failed',
            'Billing invoice not generated',
            'Refund request for duplicate charge',
            'Subscription upgrade inquiry',
            'Payment method update',
            'Incorrect billing amount',

            // Technical Errors
            'Error when uploading files',
            'Data export not working',
            'Email notifications not sending',
            'API integration failing',
            'Database connection error',
            '500 Internal Server Error',

            // UI/UX Issues
            'Mobile app UI bug',
            'Buttons not clickable',
            'Layout broken on mobile',
            'Images not displaying',
            'Dropdown menu not working',
            'Forms not submitting',

            // Account Management
            'Change account email address',
            'Delete account request',
            'Update profile information',
            'Merge duplicate accounts',
            'Transfer account ownership',

            // General Questions
            'How to use feature X?',
            'Documentation request',
            'Training materials needed',
            'Best practices inquiry',
            'API rate limits question',
            'Timezone settings',
        ];

        return $this->faker->randomElement($subjects);
    }

    /**
     * Generate realistic ticket descriptions
     */
    private function generateDescription(): string
    {
        $descriptions = [
            'I have been experiencing this issue since yesterday. Could you please help me resolve it as soon as possible?',
            'This problem is affecting my daily workflow. I have tried clearing cache and cookies but it did not help.',
            'When I attempt to perform this action, I receive an error message. The issue occurs consistently.',
            'I followed the documentation but still cannot get this to work. Please provide step-by-step guidance.',
            'This feature was working fine last week, but now it has stopped functioning properly.',
            'I have checked my settings and everything appears to be configured correctly, yet the problem persists.',
            'Could you please investigate this issue? It is impacting my team\'s productivity significantly.',
            'I tried on multiple browsers (Chrome, Firefox, Safari) and the issue occurs on all of them.',
            'This is urgent as it is preventing me from completing an important task with a deadline.',
            'I would appreciate any assistance you can provide. Thank you for your help!',
            'After the recent update, this functionality stopped working. Please advise on the next steps.',
            'I have attached screenshots showing the error. Let me know if you need any additional information.',
        ];

        // Sometimes add more detail
        $description = $this->faker->randomElement($descriptions);

        if ($this->faker->boolean(30)) {
            $additionalDetails = [
                ' I have already tried restarting my device.',
                ' This is affecting multiple users in my organization.',
                ' I checked the status page and no outages were reported.',
                ' I am using the latest version of the application.',
                ' Transaction ID: ' . strtoupper($this->faker->bothify('TXN-####-####-####')),
                ' Error code: ' . $this->faker->bothify('ERR-###'),
            ];
            $description .= $this->faker->randomElement($additionalDetails);
        }

        return $description;
    }

    /**
     * State for pending tickets
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * State for open tickets
     */
    public function open(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * State for in progress tickets
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'in_progress',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * State for resolved tickets
     */
    public function resolved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'closed_at' => null,
        ]);
    }

    /**
     * State for closed tickets
     */
    public function closed(): static
    {
        $resolvedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');

        return $this->state(fn(array $attributes) => [
            'status' => 'closed',
            'resolved_at' => $resolvedAt,
            'closed_at' => $this->faker->dateTimeBetween($resolvedAt, 'now'),
        ]);
    }

    /**
     * State for urgent priority
     */
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * State for high priority
     */
    public function high(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => 'high',
        ]);
    }
}
