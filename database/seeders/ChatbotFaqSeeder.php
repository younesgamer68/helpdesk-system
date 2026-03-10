<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I create a support ticket?',
                'answer' => 'To create a support ticket, log in to your account, navigate to the Dashboard, and click the "New Ticket" button. Fill in the subject, description, and priority level, then submit.',
            ],
            [
                'question' => 'What are your support hours?',
                'answer' => 'Our support team is available Monday through Friday, 9 AM to 6 PM (UTC). For urgent issues outside these hours, please mark your ticket as high priority and we will respond as soon as possible.',
            ],
            [
                'question' => 'How can I track my ticket status?',
                'answer' => 'You can track your ticket status from your Dashboard. Each ticket displays its current status: Open, In Progress, Awaiting Response, or Resolved. You will also receive email notifications on updates.',
            ],
            [
                'question' => 'How do I reset my password?',
                'answer' => 'Click "Sign In" then select "Forgot your password?" on the login page. Enter your email address and we will send you a password reset link. The link expires after 60 minutes.',
            ],
            [
                'question' => 'Can I assign a ticket to a specific team member?',
                'answer' => 'Yes, if you have admin or manager privileges, you can assign tickets to specific team members from the ticket detail page using the "Assign To" dropdown.',
            ],
            [
                'question' => 'What is two-factor authentication and how do I enable it?',
                'answer' => 'Two-factor authentication (2FA) adds an extra layer of security to your account. Go to Settings > Two-Factor Authentication and follow the setup instructions to enable it using an authenticator app.',
            ],
            [
                'question' => 'How do I update my profile information?',
                'answer' => 'Navigate to Settings > Profile from the sidebar. You can update your name and email address. If you change your email, you will need to verify the new address.',
            ],
            [
                'question' => 'Is there a limit on how many tickets I can create?',
                'answer' => 'There is no hard limit on tickets. However, we recommend checking existing tickets before creating duplicates. Use the search feature to find similar issues that may already be addressed.',
            ],
            [
                'question' => 'How do I delete my account?',
                'answer' => 'Go to Settings > Profile, scroll to the bottom, and click "Delete Account". You will need to confirm your password. Please note this action is permanent and all your data will be removed.',
            ],
            [
                'question' => 'Do you offer analytics and reporting?',
                'answer' => 'Yes, our Analytics dashboard provides real-time insights including ticket volume, response times, resolution rates, and team performance metrics. Access it from the main navigation.',
            ],
        ];

        foreach ($faqs as $faq) {
            ChatbotFaq::query()->updateOrCreate(
                ['question' => $faq['question']],
                $faq,
            );
        }
    }
}
