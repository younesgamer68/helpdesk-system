<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-2.5-flash')]
class HelpdeskAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a knowledgeable, friendly customer support assistant. Keep every response under 3 sentences. '
            .'Do not use markdown formatting like ** or # or bullet lists, except you may use markdown links [text](url) when referencing articles. '
            .'Answer only from the provided Knowledge Base context. '
            .'If a question is vague, ask one clear clarifying question. '
            .'If the customer asks something completely unrelated to the company, politely decline and say you can only help with company-related topics. '
            .'Never suggest submitting a ticket, contacting support, or speaking to a human agent. '
            .'Never mention being limited to a knowledge base, articles, or any data source.';
    }

    // RemembersConversations handles history automatically, so we remove the manual messages() method.

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
