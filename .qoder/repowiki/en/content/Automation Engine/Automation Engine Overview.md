# Automation Engine Overview

<cite>
**Referenced Files in This Document**
- [AutomationEngine.php](file://app/Services/Automation/AutomationEngine.php)
- [RuleInterface.php](file://app/Services/Automation/Rules/RuleInterface.php)
- [AssignmentRule.php](file://app/Services/Automation/Rules/AssignmentRule.php)
- [PriorityRule.php](file://app/Services/Automation/Rules/PriorityRule.php)
- [AutoReplyRule.php](file://app/Services/Automation/Rules/AutoReplyRule.php)
- [EscalationRule.php](file://app/Services/Automation/Rules/EscalationRule.php)
- [AutomationRule.php](file://app/Models/AutomationRule.php)
- [Ticket.php](file://app/Models/Ticket.php)
- [TicketAssignmentService.php](file://app/Services/TicketAssignmentService.php)
- [ProcessTicketEscalations.php](file://app/Console/Commands/ProcessTicketEscalations.php)
- [2026_03_09_104729_create_automation_rules_table.php](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php)
- [AutomationEngineTest.php](file://tests/Feature/Services/AutomationEngineTest.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)
10. [Appendices](#appendices)

## Introduction
This document explains the automation engine architecture that powers rule-driven ticket processing in the helpdesk system. It focuses on the central AutomationEngine orchestrator, the RuleInterface contract, the rule handler registration system, and how different rule types are mapped to their handlers. It also covers the rule evaluation lifecycle, separation between immediate and scheduled processing, logging and error handling, performance considerations, and extensibility points for adding new rule types.

## Project Structure
The automation engine lives under Services/Automation and is composed of:
- A central orchestrator (AutomationEngine)
- A shared interface for rule handlers (RuleInterface)
- Concrete rule handlers (AssignmentRule, PriorityRule, AutoReplyRule, EscalationRule)
- Supporting models (AutomationRule, Ticket)
- A dedicated service for assignment logic (TicketAssignmentService)
- A console command to trigger escalation processing (ProcessTicketEscalations)
- Database schema for automation rules

```mermaid
graph TB
subgraph "Automation Layer"
AE["AutomationEngine"]
RI["RuleInterface"]
AR["AssignmentRule"]
PR["PriorityRule"]
ARR["AutoReplyRule"]
ER["EscalationRule"]
end
subgraph "Domain Models"
ARule["AutomationRule"]
Tkt["Ticket"]
end
subgraph "Supporting Services"
TAS["TicketAssignmentService"]
end
subgraph "Infrastructure"
CMD["ProcessTicketEscalations Command"]
DB["automation_rules table"]
end
AE --> ARule
AE --> Tkt
AE --> AR
AE --> PR
AE --> ARR
AE --> ER
AR --> TAS
PR --> Tkt
ARR --> Tkt
ER --> Tkt
ER --> TAS
CMD --> AE
ARule --> DB
```

**Diagram sources**
- [AutomationEngine.php:15-141](file://app/Services/Automation/AutomationEngine.php#L15-L141)
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)
- [AssignmentRule.php:9-66](file://app/Services/Automation/Rules/AssignmentRule.php#L9-L66)
- [PriorityRule.php:9-68](file://app/Services/Automation/Rules/PriorityRule.php#L9-L68)
- [AutoReplyRule.php:10-64](file://app/Services/Automation/Rules/AutoReplyRule.php#L10-L64)
- [EscalationRule.php:12-156](file://app/Services/Automation/Rules/EscalationRule.php#L12-L156)
- [AutomationRule.php:22-116](file://app/Models/AutomationRule.php#L22-L116)
- [Ticket.php:9-63](file://app/Models/Ticket.php#L9-L63)
- [TicketAssignmentService.php:12-178](file://app/Services/TicketAssignmentService.php#L12-L178)
- [ProcessTicketEscalations.php:9-54](file://app/Console/Commands/ProcessTicketEscalations.php#L9-L54)
- [2026_03_09_104729_create_automation_rules_table.php:14-42](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L14-L42)

**Section sources**
- [AutomationEngine.php:15-141](file://app/Services/Automation/AutomationEngine.php#L15-L141)
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)
- [AutomationRule.php:22-116](file://app/Models/AutomationRule.php#L22-L116)
- [Ticket.php:9-63](file://app/Models/Ticket.php#L9-L63)
- [TicketAssignmentService.php:12-178](file://app/Services/TicketAssignmentService.php#L12-L178)
- [ProcessTicketEscalations.php:9-54](file://app/Console/Commands/ProcessTicketEscalations.php#L9-L54)
- [2026_03_09_104729_create_automation_rules_table.php:14-42](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L14-L42)

## Core Components
- AutomationEngine: Central orchestrator that discovers, filters, evaluates, and executes rules. It separates immediate processing (for new tickets) from scheduled processing (for escalation rules).
- RuleInterface: Contract defining evaluate(rule, ticket) and apply(rule, ticket) for all rule handlers.
- Rule Handlers: Concrete implementations for assignment, priority change, auto reply, and escalation.
- AutomationRule: Eloquent model representing persisted rules with conditions, actions, activation, priority, and execution tracking.
- Ticket: Domain model for tickets with attributes used by rules.
- TicketAssignmentService: Service encapsulating assignment logic (including specialists vs generalists).
- ProcessTicketEscalations: Artisan command to run escalation processing across companies.

**Section sources**
- [AutomationEngine.php:15-141](file://app/Services/Automation/AutomationEngine.php#L15-L141)
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)
- [AutomationRule.php:22-116](file://app/Models/AutomationRule.php#L22-L116)
- [Ticket.php:9-63](file://app/Models/Ticket.php#L9-L63)
- [TicketAssignmentService.php:12-178](file://app/Services/TicketAssignmentService.php#L12-L178)
- [ProcessTicketEscalations.php:9-54](file://app/Console/Commands/ProcessTicketEscalations.php#L9-L54)

## Architecture Overview
The automation engine follows a handler registry pattern:
- A typed map associates rule types to handler classes.
- Immediate processing (new tickets) excludes escalation rules and runs inline.
- Scheduled processing (escalations) is invoked via a console command and scans idle tickets.

```mermaid
sequenceDiagram
participant Client as "Caller"
participant Engine as "AutomationEngine"
participant DB as "AutomationRule"
participant Handler as "RuleHandler"
participant Ticket as "Ticket"
participant Log as "Log"
Client->>Engine : processNewTicket(ticket)
Engine->>DB : getActiveRulesForCompany(company_id)
DB-->>Engine : Collection<AutomationRule>
loop For each rule (excluding escalation)
Engine->>Engine : executeRule(rule, ticket)
Engine->>Handler : evaluate(rule, ticket)
alt evaluate returns true
Engine->>Handler : apply(rule, ticket)
Handler->>Ticket : mutate state
Engine->>DB : recordExecution()
Engine->>Log : info(...)
else evaluate returns false
Engine->>Log : skip
end
end
Engine-->>Client : void
```

**Diagram sources**
- [AutomationEngine.php:28-96](file://app/Services/Automation/AutomationEngine.php#L28-L96)
- [AutomationRule.php:66-91](file://app/Models/AutomationRule.php#L66-L91)
- [AssignmentRule.php:15-48](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L48)
- [PriorityRule.php:11-52](file://app/Services/Automation/Rules/PriorityRule.php#L11-L52)
- [AutoReplyRule.php:12-48](file://app/Services/Automation/Rules/AutoReplyRule.php#L12-L48)
- [EscalationRule.php:24-60](file://app/Services/Automation/Rules/EscalationRule.php#L24-L60)

## Detailed Component Analysis

### AutomationEngine
Responsibilities:
- Register rule handlers via a type-to-class map.
- Immediate processing: fetch active rules for a company, filter out escalations, and execute each rule against the ticket.
- Scheduled processing: fetch only escalation rules and process idle tickets.
- Centralized execution: evaluate then apply, with robust logging and error handling.
- Utility methods to fetch rules by type and company, and to order by priority.

Key behaviors:
- Immediate processing excludes escalation rules to prevent redundant processing.
- Execution tracking updates rule metrics after successful apply.
- Logging provides visibility for rule execution and failures.

```mermaid
classDiagram
class AutomationEngine {
-array ruleHandlers
+processNewTicket(ticket)
+processEscalations(companyId)
+executeRule(rule, ticket) bool
-processEscalationRule(rule) void
-getActiveRulesForCompany(companyId) Collection
+getRulesOfType(companyId, type) Collection
}
class RuleInterface {
<<interface>>
+evaluate(rule, ticket) bool
+apply(rule, ticket) void
}
class AssignmentRule
class PriorityRule
class AutoReplyRule
class EscalationRule
AutomationEngine --> RuleInterface : "invokes"
AssignmentRule ..|> RuleInterface
PriorityRule ..|> RuleInterface
AutoReplyRule ..|> RuleInterface
EscalationRule ..|> RuleInterface
```

**Diagram sources**
- [AutomationEngine.php:15-141](file://app/Services/Automation/AutomationEngine.php#L15-L141)
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)
- [AssignmentRule.php:9-66](file://app/Services/Automation/Rules/AssignmentRule.php#L9-L66)
- [PriorityRule.php:9-68](file://app/Services/Automation/Rules/PriorityRule.php#L9-L68)
- [AutoReplyRule.php:10-64](file://app/Services/Automation/Rules/AutoReplyRule.php#L10-L64)
- [EscalationRule.php:12-156](file://app/Services/Automation/Rules/EscalationRule.php#L12-L156)

**Section sources**
- [AutomationEngine.php:15-141](file://app/Services/Automation/AutomationEngine.php#L15-L141)

### RuleInterface
Contract:
- evaluate(rule, ticket): returns true if conditions match for the given ticket.
- apply(rule, ticket): performs the action(s) defined by the rule.

This ensures all rule handlers implement a consistent evaluation and application lifecycle.

**Section sources**
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)

### Rule Handler Registration System
AutomationEngine maintains a type-to-handler map:
- assignment -> AssignmentRule
- priority -> PriorityRule
- auto_reply -> AutoReplyRule
- escalation -> EscalationRule

This enables dynamic dispatch based on rule.type and allows easy extension by adding new entries.

**Section sources**
- [AutomationEngine.php:18-25](file://app/Services/Automation/AutomationEngine.php#L18-L25)

### Rule Types and Handlers

#### AssignmentRule
- Purpose: Auto-assign tickets to specialists or operators based on category and priority.
- Evaluation: Skips if ticket is already assigned or not verified; checks category and priority conditions.
- Application: Either assigns via TicketAssignmentService or directly to a specific operator.

**Section sources**
- [AssignmentRule.php:9-66](file://app/Services/Automation/Rules/AssignmentRule.php#L9-L66)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)

#### PriorityRule
- Purpose: Change ticket priority based on keywords, category, and current priority.
- Evaluation: Requires verified ticket; checks keywords and category; enforces current priority constraints.
- Application: Sets priority to a valid level if configured.

**Section sources**
- [PriorityRule.php:9-68](file://app/Services/Automation/Rules/PriorityRule.php#L9-L68)

#### AutoReplyRule
- Purpose: Send automated replies to customers upon ticket creation or based on conditions.
- Evaluation: Requires verified ticket; optionally restricts to newly created tickets; checks category and priority.
- Application: Queues an email with configurable subject/message.

**Section sources**
- [AutoReplyRule.php:10-64](file://app/Services/Automation/Rules/AutoReplyRule.php#L10-L64)

#### EscalationRule
- Purpose: Handle scheduled escalation of idle tickets.
- Evaluation: Checks status, idle duration, category; prevents escalating already urgent tickets unless notifying admins.
- Application: Escalates priority, sets specific priority, notifies admins, and optionally reassigns.
- Specialized scanning: findIdleTickets collects tickets meeting criteria for batch processing.

**Section sources**
- [EscalationRule.php:12-156](file://app/Services/Automation/Rules/EscalationRule.php#L12-L156)

### Rule Evaluation Lifecycle
End-to-end flow:
1. Discovery: Retrieve active rules for the company, ordered by priority.
2. Filtering: Exclude escalations during immediate processing; select escalations for scheduled processing.
3. Evaluation: Each handler’s evaluate determines if the rule applies.
4. Application: If evaluate passes, apply mutates the ticket and persists execution metrics.
5. Completion Tracking: AutomationRule.recordExecution increments executions_count and updates last_executed_at.
6. Logging: Info logs successful executions; warning/error logs indicate missing handlers or exceptions.

```mermaid
flowchart TD
Start(["Start"]) --> Load["Load Active Rules<br/>by company and priority"]
Load --> FilterImmediate{"Immediate Processing?"}
FilterImmediate --> |Yes| SkipEscalation["Skip escalation rules"]
FilterImmediate --> |No| SelectEscalation["Select only escalation rules"]
SkipEscalation --> Iterate["Iterate rules"]
SelectEscalation --> Iterate
Iterate --> Eval["Evaluate(rule, ticket)"]
Eval --> Pass{"Evaluate passed?"}
Pass --> |No| NextRule["Next rule"]
Pass --> |Yes| Apply["Apply(rule, ticket)"]
Apply --> Track["Record execution on rule"]
Track --> LogInfo["Log info"]
LogInfo --> NextRule
NextRule --> Iterate
Iterate --> Done(["Done"])
```

**Diagram sources**
- [AutomationEngine.php:28-96](file://app/Services/Automation/AutomationEngine.php#L28-L96)
- [AutomationRule.php:94-100](file://app/Models/AutomationRule.php#L94-L100)

**Section sources**
- [AutomationEngine.php:28-96](file://app/Services/Automation/AutomationEngine.php#L28-L96)
- [AutomationRule.php:94-100](file://app/Models/AutomationRule.php#L94-L100)

### Separation Between Immediate and Scheduled Processing
- Immediate processing (new tickets):
  - Called from the ticket creation flow.
  - Excludes escalation rules to avoid duplication with scheduled processing.
  - Executes evaluate and apply inline for fast response.
- Scheduled processing (escalations):
  - Triggered by a console command.
  - Loads only escalation rules and scans idle tickets.
  - Applies escalation actions and can notify administrators.

```mermaid
sequenceDiagram
participant NewTicket as "New Ticket"
participant Engine as "AutomationEngine"
participant EscCmd as "ProcessTicketEscalations"
participant EscRule as "EscalationRule"
NewTicket->>Engine : processNewTicket(ticket)
Engine->>Engine : skip escalation rules
Engine->>Engine : executeRule(rule, ticket)
EscCmd->>Engine : processEscalations(companyId)
Engine->>EscRule : findIdleTickets(rule)
EscRule-->>Engine : Collection<Ticket>
loop For each idle ticket
Engine->>Engine : executeRule(rule, ticket)
end
```

**Diagram sources**
- [AutomationEngine.php:28-54](file://app/Services/Automation/AutomationEngine.php#L28-L54)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)

**Section sources**
- [AutomationEngine.php:28-54](file://app/Services/Automation/AutomationEngine.php#L28-L54)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)

### Logging and Error Handling
- Warning: No handler found for a rule type.
- Info: Successful rule execution with rule and ticket identifiers.
- Error: Exceptions during apply include rule and ticket identifiers plus error message.
- Logging uses the framework’s logging facility.

**Section sources**
- [AutomationEngine.php:64-95](file://app/Services/Automation/AutomationEngine.php#L64-L95)

### Performance Considerations
- Rule ordering: Rules are fetched ordered by priority, ensuring deterministic application.
- Minimal overhead: Handlers perform lightweight checks before applying actions.
- Batch escalation: EscalationRule.findIdleTickets retrieves eligible tickets in bulk to reduce repeated queries.
- Queueing: AutoReplyRule queues emails to avoid blocking synchronous processing.
- Transaction-safe assignments: TicketAssignmentService wraps assignments in transactions to maintain consistency.

**Section sources**
- [AutomationEngine.php:118-125](file://app/Services/Automation/AutomationEngine.php#L118-L125)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)
- [AutoReplyRule.php:61-62](file://app/Services/Automation/Rules/AutoReplyRule.php#L61-L62)
- [TicketAssignmentService.php:101-108](file://app/Services/TicketAssignmentService.php#L101-L108)

### Extensibility Points
To add a new rule type:
1. Define a new handler class implementing RuleInterface.
2. Add a new constant in AutomationRule for the rule type.
3. Register the handler in AutomationEngine’s ruleHandlers map.
4. Extend the database schema if new conditions/actions require persistence.
5. Write tests verifying evaluation and application logic.

```mermaid
classDiagram
class NewRuleHandler {
+evaluate(rule, ticket) bool
+apply(rule, ticket) void
}
class AutomationEngine {
-array ruleHandlers
}
class AutomationRule {
+const TYPE_NEW
}
NewRuleHandler ..|> RuleInterface
AutomationEngine --> NewRuleHandler : "register in map"
AutomationRule --> NewRuleHandler : "mapped by type"
```

**Diagram sources**
- [AutomationEngine.php:18-25](file://app/Services/Automation/AutomationEngine.php#L18-L25)
- [AutomationRule.php:27-33](file://app/Models/AutomationRule.php#L27-L33)
- [RuleInterface.php:8-19](file://app/Services/Automation/Rules/RuleInterface.php#L8-L19)

**Section sources**
- [AutomationEngine.php:18-25](file://app/Services/Automation/AutomationEngine.php#L18-L25)
- [AutomationRule.php:27-33](file://app/Models/AutomationRule.php#L27-L33)

## Dependency Analysis
- AutomationEngine depends on:
  - AutomationRule (persistence, scopes, execution tracking)
  - Ticket (domain model for evaluation and mutation)
  - RuleInterface implementations (AssignmentRule, PriorityRule, AutoReplyRule, EscalationRule)
  - TicketAssignmentService (assignment logic)
  - Logging facility (info/warning/error)
- EscalationRule additionally depends on:
  - TicketAssignmentService for reassignment
  - Mail infrastructure for notifications

```mermaid
graph LR
AE["AutomationEngine"] --> ARule["AutomationRule"]
AE --> Tkt["Ticket"]
AE --> RI["RuleInterface"]
AE --> AR["AssignmentRule"]
AE --> PR["PriorityRule"]
AE --> ARR["AutoReplyRule"]
AE --> ER["EscalationRule"]
AR --> TAS["TicketAssignmentService"]
ER --> TAS
ER --> Mail["Mail Facade"]
```

**Diagram sources**
- [AutomationEngine.php:5-13](file://app/Services/Automation/AutomationEngine.php#L5-L13)
- [AssignmentRule.php:7](file://app/Services/Automation/Rules/AssignmentRule.php#L7)
- [EscalationRule.php:5-10](file://app/Services/Automation/Rules/EscalationRule.php#L5-L10)

**Section sources**
- [AutomationEngine.php:5-13](file://app/Services/Automation/AutomationEngine.php#L5-L13)
- [AssignmentRule.php:7](file://app/Services/Automation/Rules/AssignmentRule.php#L7)
- [EscalationRule.php:5-10](file://app/Services/Automation/Rules/EscalationRule.php#L5-L10)

## Performance Considerations
- Fetch only active rules and order by priority to minimize unnecessary evaluations.
- Use database indexes on company_id, is_active, type, and priority for efficient queries.
- Queue emails (auto reply) to avoid blocking synchronous processing.
- Batch escalation scanning reduces repeated database queries for idle tickets.
- Transactional assignments ensure consistency and prevent race conditions.

**Section sources**
- [AutomationEngine.php:118-125](file://app/Services/Automation/AutomationEngine.php#L118-L125)
- [2026_03_09_104729_create_automation_rules_table.php:39-41](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L39-L41)
- [AutoReplyRule.php:61-62](file://app/Services/Automation/Rules/AutoReplyRule.php#L61-L62)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)
- [TicketAssignmentService.php:101-108](file://app/Services/TicketAssignmentService.php#L101-L108)

## Troubleshooting Guide
Common issues and diagnostics:
- No handler found for rule type:
  - Symptom: Warning logged indicating missing handler.
  - Action: Ensure the rule type is registered in AutomationEngine and the handler class exists.
- Exception during apply:
  - Symptom: Error logged with rule and ticket identifiers and error message.
  - Action: Inspect handler logic and dependencies; verify permissions and data validity.
- Escalation not triggered:
  - Symptom: Idle tickets remain unchanged.
  - Action: Confirm the console command is scheduled and executed; verify escalation rule conditions and statuses.
- Auto reply not sent:
  - Symptom: Customer did not receive an email.
  - Action: Check queue worker status and verify AutoReplyRule conditions and actions.

**Section sources**
- [AutomationEngine.php:64-95](file://app/Services/Automation/AutomationEngine.php#L64-L95)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)
- [AutoReplyRule.php:54-63](file://app/Services/Automation/Rules/AutoReplyRule.php#L54-L63)

## Conclusion
The automation engine provides a clean, extensible framework for rule-driven ticket processing. Its central orchestrator coordinates evaluation and application across multiple rule types while maintaining separation between immediate and scheduled processing. Robust logging, error handling, and performance-conscious design enable reliable operation at scale. Adding new rule types requires minimal changes to the handler registry and a small amount of handler logic, preserving system cohesion and maintainability.

## Appendices

### Data Model for Automation Rules
The automation_rules table stores rule metadata, conditions, actions, activation, priority, and execution tracking.

```mermaid
erDiagram
AUTOMATION_RULE {
bigint id PK
bigint company_id FK
string name
text description
enum type
json conditions
json actions
boolean is_active
int priority
bigint executions_count
timestamp last_executed_at
timestamps timestamps
}
COMPANY ||--o{ AUTOMATION_RULE : "has"
```

**Diagram sources**
- [2026_03_09_104729_create_automation_rules_table.php:14-42](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L14-L42)
- [AutomationRule.php:55](file://app/Models/AutomationRule.php#L55)

**Section sources**
- [2026_03_09_104729_create_automation_rules_table.php:14-42](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L14-L42)
- [AutomationRule.php:55](file://app/Models/AutomationRule.php#L55)

### Test Coverage Highlights
- Assignment rule application on new tickets.
- Priority rule application based on keywords.
- Auto reply email queuing.
- Escalation rule finding idle tickets and notifying admins.
- Rule execution counting and timing.

**Section sources**
- [AutomationEngineTest.php:19-47](file://tests/Feature/Services/AutomationEngineTest.php#L19-L47)
- [AutomationEngineTest.php:49-95](file://tests/Feature/Services/AutomationEngineTest.php#L49-L95)
- [AutomationEngineTest.php:97-123](file://tests/Feature/Services/AutomationEngineTest.php#L97-L123)
- [AutomationEngineTest.php:209-241](file://tests/Feature/Services/AutomationEngineTest.php#L209-L241)
- [AutomationEngineTest.php:243-277](file://tests/Feature/Services/AutomationEngineTest.php#L243-L277)