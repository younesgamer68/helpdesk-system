<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotFaqController extends Controller
{
    public function random(): JsonResponse
    {
        $faqs = ChatbotFaq::query()
            ->inRandomOrder()
            ->limit(4)
            ->get(['id', 'question', 'answer']);

        return response()->json($faqs);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $reply = $this->generateReply($validated['message']);

        Conversation::query()->create([
            'user_message' => $validated['message'],
            'bot_response' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }

    /** @var array<string, string> */
    private const KEYWORD_RESPONSES = [
        'sales' => 'I would be happy to connect you with our sales team! You can reach them at sales@helpdesk.com or call us at (555) 123-4567. They are available Monday through Friday, 9 AM to 6 PM UTC.',
        'trial' => 'Great choice! You can start a free 14-day trial by clicking "Get Started" on our homepage. No credit card required. You will get full access to all features during the trial period.',
        'pricing' => 'We offer flexible plans: Starter at $19/month (up to 3 agents), Professional at $49/month (up to 10 agents), and Enterprise with custom pricing. All plans include unlimited tickets and email support.',
        'demo' => 'We would love to show you a demo! Please email demo@helpdesk.com with your preferred date and time, and our team will set up a personalized walkthrough of the platform.',
        'account' => 'For account-related help, please go to Settings in your dashboard. There you can update your profile, change your password, manage two-factor authentication, and more. If you are locked out, use the "Forgot Password" link on the sign-in page.',
        'ticket' => 'To create a support ticket, log in to your dashboard and click "New Ticket". Fill in the subject, description, and priority level. You can track all your tickets from the Dashboard.',
        'password' => 'To reset your password, go to the Sign In page and click "Forgot your password?". Enter your email and we will send you a reset link. The link expires after 60 minutes.',
        'feature' => 'Our platform includes ticket management, team collaboration, real-time analytics, two-factor authentication, email notifications, and a powerful API. Check our documentation for the full feature list.',
        'help' => 'I am here to help! You can ask me about pricing, features, account management, creating tickets, or getting a demo. What would you like to know?',
        'hello' => 'Hello! Welcome to our helpdesk. How can I assist you today? Feel free to ask about our features, pricing, or anything else.',
        'hi' => 'Hi there! How can I help you today? You can ask me about sales, pricing, features, or account management.',
        'thank' => 'You are welcome! Is there anything else I can help you with?',
    ];

    private function generateReply(string $message): string
    {
        $message = mb_strtolower($message);

        foreach (self::KEYWORD_RESPONSES as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return 'Thank you for your message. I am not sure I understand that fully, but I want to help! You can ask me about sales, pricing, features, demos, or account management. Or, would you like me to connect you with a human agent?';
    }
}
