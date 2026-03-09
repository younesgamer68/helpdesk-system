<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Ticket;

interface RuleInterface
{
    /**
     * Evaluate if the rule conditions are met for this ticket.
     */
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool;

    /**
     * Apply the rule actions to the ticket.
     */
    public function apply(AutomationRule $rule, Ticket $ticket): void;
}
