<?php

namespace App\Services\Automation;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Services\Automation\Rules\AssignmentRule;
use App\Services\Automation\Rules\AutoReplyRule;
use App\Services\Automation\Rules\EscalationRule;
use App\Services\Automation\Rules\KeywordAssignmentRule;
use App\Services\Automation\Rules\PriorityRule;
use App\Services\Automation\Rules\RuleInterface;
use App\Services\Automation\Rules\SlaBreachRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    /**
     * @var array<string, class-string<RuleInterface>>
     */
    protected array $ruleHandlers = [
        AutomationRule::TYPE_ASSIGNMENT => AssignmentRule::class,
        AutomationRule::TYPE_KEYWORD_ASSIGNMENT => KeywordAssignmentRule::class,
        AutomationRule::TYPE_PRIORITY => PriorityRule::class,
        AutomationRule::TYPE_AUTO_REPLY => AutoReplyRule::class,
        AutomationRule::TYPE_ESCALATION => EscalationRule::class,
        AutomationRule::TYPE_SLA_BREACH => SlaBreachRule::class,
    ];

    /**
     * Process all applicable rules for a newly created ticket.
     */
    public function processNewTicket(Ticket $ticket): void
    {
        $rules = $this->getActiveRulesForCompany($ticket->company_id);

        $keywordAssignmentRules = $rules
            ->filter(fn (AutomationRule $rule) => $rule->type === AutomationRule::TYPE_KEYWORD_ASSIGNMENT)
            ->sortBy('priority');

        foreach ($keywordAssignmentRules as $rule) {
            $this->executeRule($rule, $ticket);
            $ticket->refresh();
        }

        foreach ($rules as $rule) {
            if ($rule->type === AutomationRule::TYPE_KEYWORD_ASSIGNMENT) {
                continue;
            }

            if ($rule->type === AutomationRule::TYPE_ESCALATION) {
                continue; // Escalation rules are processed by scheduler
            }

            if ($rule->type === AutomationRule::TYPE_SLA_BREACH) {
                continue; // SLA breach rules are processed by the helpdesk:check-sla-breaches command
            }

            $this->executeRule($rule, $ticket);
        }
    }

    /**
     * Process escalation rules for idle tickets.
     */
    public function processEscalations(int $companyId): void
    {
        $rules = $this->getActiveRulesForCompany($companyId)
            ->filter(fn (AutomationRule $rule) => $rule->type === AutomationRule::TYPE_ESCALATION);

        foreach ($rules as $rule) {
            $this->processEscalationRule($rule);
        }
    }

    /**
     * Execute a single automation rule on a ticket.
     */
    public function executeRule(AutomationRule $rule, Ticket $ticket): bool
    {
        $handlerClass = $this->ruleHandlers[$rule->type] ?? null;

        if (! $handlerClass) {
            Log::warning("No handler found for rule type: {$rule->type}");

            return false;
        }

        /** @var RuleInterface $handler */
        $handler = app($handlerClass);

        if (! $handler->evaluate($rule, $ticket)) {
            return false;
        }

        try {
            $handler->apply($rule, $ticket);
            $rule->recordExecution();

            Log::info('Automation rule executed', [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'ticket_id' => $ticket->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to execute automation rule', [
                'rule_id' => $rule->id,
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process an escalation rule for all matching idle tickets.
     */
    protected function processEscalationRule(AutomationRule $rule): void
    {
        /** @var EscalationRule $handler */
        $handler = app(EscalationRule::class);

        $idleTickets = $handler->findIdleTickets($rule);

        foreach ($idleTickets as $ticket) {
            $this->executeRule($rule, $ticket);
        }
    }

    /**
     * Get all active rules for a company, ordered by priority.
     *
     * @return Collection<int, AutomationRule>
     */
    protected function getActiveRulesForCompany(int $companyId): Collection
    {
        return AutomationRule::query()
            ->where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get rules of a specific type for a company.
     *
     * @return Collection<int, AutomationRule>
     */
    public function getRulesOfType(int $companyId, string $type): Collection
    {
        return AutomationRule::query()
            ->where('company_id', $companyId)
            ->active()
            ->ofType($type)
            ->ordered()
            ->get();
    }
}
