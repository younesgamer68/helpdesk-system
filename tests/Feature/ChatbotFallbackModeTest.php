<?php

namespace Tests\Feature;

use App\Http\Controllers\ChatbotWidgetController;
use App\Models\ChatbotFaq;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFallbackModeTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private ChatbotWidgetController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['name' => 'Test Company']);
        $this->controller = new ChatbotWidgetController;

        // Seed sample FAQs matching the seeder
        ChatbotFaq::create([
            'company_id' => $this->company->id,
            'question' => 'What is your refund policy?',
            'answer' => 'We offer a 30-day money-back guarantee for new subscriptions. Contact billing support for refund requests.',
        ]);

        ChatbotFaq::create([
            'company_id' => $this->company->id,
            'question' => 'How do I update my payment method?',
            'answer' => 'Go to Settings → Billing → Payment Methods. Add your new card, set it as default, then remove the old one.',
        ]);

        ChatbotFaq::create([
            'company_id' => $this->company->id,
            'question' => 'How do I connect to the VPN?',
            'answer' => 'Download the VPN client from the IT portal, enter server address vpn.automationdemo.test, and log in with your company credentials.',
        ]);

        ChatbotFaq::create([
            'company_id' => $this->company->id,
            'question' => 'How do I reset my password?',
            'answer' => 'Click "Forgot Password" on the login page, enter your email, and follow the reset link sent to your inbox. The link expires after 60 minutes.',
        ]);
    }

    public function test_billing_query_returns_billing_faq(): void
    {
        // Use reflection to access the private buildLocalFallbackReply method
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'I need help with my billing');

        // Should return a billing-related answer, not VPN or password answer
        $this->assertTrue(
            str_contains($reply, 'Payment Methods') || str_contains($reply, 'money-back'),
            'Should return a billing-related FAQ, not VPN or password'
        );
        $this->assertStringNotContainsString('VPN', $reply);
        $this->assertStringNotContainsString('Forgot Password', $reply);
    }

    public function test_billing_question_returns_billing_faq(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'I have a question about refunds');

        $this->assertStringContainsString('30-day money-back guarantee', $reply);
    }

    public function test_vpn_query_returns_vpn_faq(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'How do I connect to VPN?');

        $this->assertStringContainsString('VPN', $reply);
        $this->assertStringNotContainsString('Payment', $reply);
    }

    public function test_password_query_returns_password_faq(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'I forgot my password');

        $this->assertStringContainsString('Forgot Password', $reply);
        $this->assertStringNotContainsString('Payment', $reply);
    }

    public function test_irrelevant_query_asks_for_clarification(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'What is the weather today?');

        // Should ask for clarification when similarity is low, not return a random FAQ
        $this->assertStringContainsString('not quite sure', $reply);
        $this->assertStringContainsString('support agents', $reply);
    }

    public function test_vague_prompt_asks_for_details_instead_of_guessing_faq(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'buildLocalFallbackReply');
        $reflection->setAccessible(true);

        $reply = $reflection->invoke($this->controller, $this->company, 'how');

        $this->assertStringContainsString('share a bit more detail', $reply);
        $this->assertStringNotContainsString('Forgot Password', $reply);
    }
}
