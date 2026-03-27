<?php

namespace Tests\Unit;

use App\Http\Controllers\ChatbotWidgetController;
use PHPUnit\Framework\TestCase;

class ChatbotFallbackSimilarityTest extends TestCase
{
    /**
     * Test semantic similarity calculation between strings.
     *
     * @return void
     */
    public function test_semantic_similarity_calculation()
    {
        $controller = new ChatbotWidgetController;
        $reflection = new \ReflectionMethod($controller, 'calculateSemanticSimilarity');
        $reflection->setAccessible(true);

        // Test exact match
        $this->assertGreaterThan(0.8, $reflection->invoke($controller, 'billing', 'How do I update my payment method?'));

        // Test high-value keywords boost
        $billingScore = $reflection->invoke($controller, 'billing issue', 'What is your refund policy?');
        $this->assertGreaterThan(0.2, $billingScore);

        // Test low similarity
        $lowScore = $reflection->invoke($controller, 'pizza', 'How do I reset my password?');
        $this->assertLessThan(0.2, $lowScore);

        // Test vpn match
        $vpnScore = $reflection->invoke($controller, 'vpn connection', 'How do I connect to the VPN?');
        $this->assertGreaterThan(0.6, $vpnScore);

        // Generic prompts should not match FAQs strongly
        $genericScore = $reflection->invoke($controller, 'how', 'How do I reset my password?');
        $this->assertLessThan(0.3, $genericScore);
    }
}
