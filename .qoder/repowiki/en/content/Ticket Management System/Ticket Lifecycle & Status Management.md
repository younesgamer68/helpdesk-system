# Ticket Lifecycle & Status Management

<cite>
**Referenced Files in This Document**
- [Ticket.php](file://app/Models/Ticket.php)
- [TicketReply.php](file://app/Models/TicketReply.php)
- [TicketLog.php](file://app/Models/TicketLog.php)
- [AiSuggestionLog.php](file://app/Models/AiSuggestionLog.php)
- [GoldenResponse.php](file://app/Models/GoldenResponse.php)
- [SlaPolicy.php](file://app/Models/SlaPolicy.php)
- [SlaPolicyRule.php](file://app/Models/SlaPolicyRule.php)
- [AutoTriageRule.php](file://app/Models/AutoTriageRule.php)
- [HelpdeskAgent.php](file://app/Ai/Agents/HelpdeskAgent.php)
- [SupportReplyAgent.php](file://app/Ai/Agents/SupportReplyAgent.php)
- [AutoTriageService.php](file://app/Services/AutoTriageService.php)
- [TicketObserver.php](file://app/Observers/TicketObserver.php)
- [AutoTriageRules.php](file://app/Livewire/Ai/AutoTriageRules.php)
- [UsageStats.php](file://app/Livewire/Ai/UsageStats.php)
- [2026_02_01_224222_create_tickets_table.php](file://database/migrations/2026_02_01_224222_create_tickets_table.php)
- [2026_02_01_224225_create_ticket_replies_table.php](file://database/migrations/2026_02_01_224225_create_ticket_replies_table.php)
- [2026_03_10_230354_create_ticket_logs_table.php](file://database/migrations/2026_03_10_230354_create_ticket_logs_table.php)
- [2026_03_20_000003_create_ai_suggestion_logs_table.php](file://database/migrations/2026_03_20_000003_create_ai_suggestion_logs_table.php)
- [2026_03_20_000004_create_chatbot_conversations_table.php](file://database/migrations/2026_03_20_000004_create_chatbot_conversations_table.php)
- [2026_03_20_000009_create_auto_triage_rules_table.php](file://database/migrations/2026_03_20_000009_create_auto_triage_rules_table.php)
- [2026_03_20_100001_add_auto_triage_and_model_to_company_ai_settings.php](file://database/migrations/2026_03_20_100001_add_auto_triage_and_model_to_company_ai_settings.php)
- [2026_03_20_100002_create_auto_triage_rules_table.php](file://database/migrations/2026_03_20_100002_create_auto_triage_rules_table.php)
- [2026_03_20_100003_create_golden_responses_table.php](file://database/migrations/2026_03_20_100003_create_golden_responses_table.php)
- [2026_03_20_100004_add_suggestion_text_to_ai_suggestion_logs.php](file://database/migrations/2026_03_20_100004_add_suggestion_text_to_ai_suggestion_logs.php)
- [AutomationEngine.php](file://app/Services/Automation/AutomationEngine.php)
- [EscalationRule.php](file://app/Services/Automation/Rules/EscalationRule.php)
- [AssignmentRule.php](file://app/Services/Automation/Rules/AssignmentRule.php)
- [PriorityRule.php](file://app/Services/Automation/Rules/PriorityRule.php)
- [TicketAssignmentService.php](file://app/Services/TicketAssignmentService.php)
- [ProcessTicketEscalations.php](file://app/Console/Commands/ProcessTicketEscalations.php)
- [TicketsController.php](file://app/Http/Controllers/TicketsController.php)
</cite>

## Update Summary
**Changes Made**
- Added comprehensive AI-powered reply suggestion system with golden response training
- Enhanced ticket management with AI auto-triage capabilities
- Integrated SLA tracking with priority-based response time policies
- Added AI suggestion logging and usage analytics
- Implemented AI-powered ticket details component with summary generation
- Enhanced assignment algorithms with AI assistance

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [AI-Powered Features](#ai-powered-features)
7. [SLA Tracking & Management](#sla-tracking--management)
8. [Dependency Analysis](#dependency-analysis)
9. [Performance Considerations](#performance-considerations)
10. [Troubleshooting Guide](#troubleshooting-guide)
11. [Conclusion](#conclusion)
12. [Appendices](#appendices)

## Introduction
This document explains the complete ticket lifecycle and status management in the HelpDesk System, enhanced with AI-powered features. It covers all status states (Open, In Progress, Pending, Resolved, Closed), their transitions, automated triggers (replies, time-based escalation, priority rules), timestamp tracking (created_at, updated_at, resolved_at, closed_at), soft delete and archive behavior, scope-based filtering for open tickets, and audit trail via logs. The system now includes AI-powered reply suggestions, auto-triage capabilities, enhanced assignment algorithms, SLA tracking, and comprehensive activity logging.

## Project Structure
The ticket lifecycle spans several layers with enhanced AI integration:
- Models define schema, attributes, relationships, scopes, and timestamps.
- AI models handle suggestion logging, golden responses, and triage rules.
- Observers react to model events to trigger automation and maintain operator counters.
- AI agents provide conversational assistance and triage capabilities.
- Automation engine executes rules (assignment, priority, auto-reply, escalation).
- Rules encapsulate evaluation and application logic for status/priority changes.
- Console commands orchestrate periodic tasks like escalation processing.
- Controllers expose ticket details with AI summaries for UI consumption.

```mermaid
graph TB
subgraph "Core Models"
T["Ticket<br/>status, timestamps, softDeletes"]
R["TicketReply<br/>message, internal flag"]
L["TicketLog<br/>audit actions"]
end
subgraph "AI Models"
ASL["AiSuggestionLog<br/>AI suggestion tracking"]
GR["GoldenResponse<br/>training responses"]
ATR["AutoTriageRule<br/>AI triage rules"]
SP["SlaPolicy<br/>response time policies"]
end
subgraph "AI Agents"
HD["HelpdeskAgent<br/>general support"]
SRA["SupportReplyAgent<br/>reply generation"]
end
subgraph "Automation"
AE["AutomationEngine<br/>dispatch rules"]
AR["AssignmentRule<br/>assign operator"]
PR["PriorityRule<br/>set priority"]
ER["EscalationRule<br/>time-based escalation"]
ATS["AutoTriageService<br/>AI triage"]
end
subgraph "Services"
TAS["TicketAssignmentService<br/>assign/reassign/unassign"]
end
subgraph "System"
OBS["TicketObserver<br/>events & counters"]
CMD["ProcessTicketEscalations<br/>CLI"]
US["UsageStats<br/>AI metrics"]
end
T -- "has many" --> R
T -- "has many" --> L
T -. "observed by" .-> OBS
ASL -. "tracks" .-> T
GR -. "trained on" .-> T
ATR -. "evaluates" .-> T
SP -. "governs" .-> T
OBS --> AE
AE --> AR
AE --> PR
AE --> ER
AE --> ATS
ER --> TAS
CMD --> AE
US --> ASL
```

**Diagram sources**
- [Ticket.php:1-64](file://app/Models/Ticket.php#L1-L64)
- [TicketReply.php:1-39](file://app/Models/TicketReply.php#L1-L39)
- [TicketLog.php:1-26](file://app/Models/TicketLog.php#L1-L26)
- [AiSuggestionLog.php:1-40](file://app/Models/AiSuggestionLog.php#L1-L40)
- [GoldenResponse.php:1-53](file://app/Models/GoldenResponse.php#L1-L53)
- [AutoTriageRule.php](file://app/Models/AutoTriageRule.php)
- [SlaPolicy.php:1-44](file://app/Models/SlaPolicy.php#L1-L44)
- [HelpdeskAgent.php:1-42](file://app/Ai/Agents/HelpdeskAgent.php#L1-L42)
- [SupportReplyAgent.php:1-50](file://app/Ai/Agents/SupportReplyAgent.php#L1-L50)
- [AutoTriageService.php:1-163](file://app/Services/AutoTriageService.php#L1-L163)
- [AutomationEngine.php:1-142](file://app/Services/Automation/AutomationEngine.php#L1-L142)
- [AssignmentRule.php:1-67](file://app/Services/Automation/Rules/AssignmentRule.php#L1-L67)
- [PriorityRule.php:1-69](file://app/Services/Automation/Rules/PriorityRule.php#L1-L69)
- [EscalationRule.php:1-157](file://app/Services/Automation/Rules/EscalationRule.php#L1-L157)
- [TicketAssignmentService.php:1-179](file://app/Services/TicketAssignmentService.php#L1-L179)
- [TicketObserver.php:1-109](file://app/Observers/TicketObserver.php#L1-L109)
- [ProcessTicketEscalations.php:1-55](file://app/Console/Commands/ProcessTicketEscalations.php#L1-L55)
- [UsageStats.php:1-102](file://app/Livewire/Ai/UsageStats.php#L1-L102)

**Section sources**
- [Ticket.php:1-64](file://app/Models/Ticket.php#L1-L64)
- [AutomationEngine.php:1-142](file://app/Services/Automation/AutomationEngine.php#L1-L142)
- [TicketObserver.php:1-109](file://app/Observers/TicketObserver.php#L1-L109)
- [ProcessTicketEscalations.php:1-55](file://app/Console/Commands/ProcessTicketEscalations.php#L1-L55)

## Core Components
- Ticket model
  - Enumerated statuses: pending, open, in_progress, resolved, closed.
  - Timestamps: created_at, updated_at, resolved_at, closed_at.
  - Soft deletes enabled.
  - Scope to filter open tickets (open, in_progress, pending).
  - Relationships: belongs to company/category/user; has many replies/logs.
- TicketReply model
  - Stores replies with internal/public distinction and attachments.
  - Belongs to Ticket and User.
- TicketLog model
  - Records audit actions for a ticket.
- AI Models
  - AiSuggestionLog: tracks AI suggestion generation, usage, and dismissal.
  - GoldenResponse: stores trained golden responses for better suggestions.
  - AutoTriageRule: defines AI-powered triage rules with keyword and AI types.
  - SlaPolicy: manages priority-based SLA response time policies.
- AI Agents
  - HelpdeskAgent: general customer support assistance.
  - SupportReplyAgent: generates AI-powered reply suggestions.
- AutoTriageService
  - Provides AI-powered ticket triage with keyword rules and AI fallback.
- AutomationEngine
  - Dispatches rules by type and company.
  - Processes new tickets and escalations.
- Rules
  - AssignmentRule: assigns operator based on category/priority.
  - PriorityRule: sets priority based on keywords/category/current priority.
  - EscalationRule: increases priority or notifies admins after idle threshold.
- TicketAssignmentService
  - Assigns specialists/generalists; supports reassign/unassign with counters.
- TicketObserver
  - Triggers automation on created/verified updates.
  - Maintains operator assigned_tickets_count on status changes and lifecycle events.
  - Handles SLA status calculation and updates.

**Section sources**
- [Ticket.php:25-62](file://app/Models/Ticket.php#L25-L62)
- [TicketReply.php:10-37](file://app/Models/TicketReply.php#L10-L37)
- [TicketLog.php:9-24](file://app/Models/TicketLog.php#L9-L24)
- [AiSuggestionLog.php:11-18](file://app/Models/AiSuggestionLog.php#L11-L18)
- [GoldenResponse.php:14-21](file://app/Models/GoldenResponse.php#L14-L21)
- [AutoTriageRule.php](file://app/Models/AutoTriageRule.php)
- [SlaPolicy.php:15-32](file://app/Models/SlaPolicy.php#L15-L32)
- [HelpdeskAgent.php:25-28](file://app/Ai/Agents/HelpdeskAgent.php#L25-L28)
- [SupportReplyAgent.php:25-27](file://app/Ai/Agents/SupportReplyAgent.php#L25-L27)
- [AutoTriageService.php:16-33](file://app/Services/AutoTriageService.php#L16-L33)
- [AutomationEngine.php:27-96](file://app/Services/Automation/AutomationEngine.php#L27-L96)
- [AssignmentRule.php:15-65](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L65)
- [PriorityRule.php:11-67](file://app/Services/Automation/Rules/PriorityRule.php#L11-L67)
- [EscalationRule.php:24-85](file://app/Services/Automation/Rules/EscalationRule.php#L24-L85)
- [TicketAssignmentService.php:22-108](file://app/Services/TicketAssignmentService.php#L22-L108)
- [TicketObserver.php:21-71](file://app/Observers/TicketObserver.php#L21-L71)

## Architecture Overview
The lifecycle is event-driven with AI enhancements:
- Creation: Verified tickets trigger automation; AI auto-triage processes tickets for categorization and priority assignment.
- AI Suggestions: Reply forms show AI-generated suggestions with training and feedback mechanisms.
- Replies: No direct automatic status change on reply; escalation may increase priority after idle thresholds.
- Time-based escalation: Cron-driven command evaluates idle tickets and applies escalation actions.
- Status transitions: Manual or automated; observer maintains operator counters and handles reopen/close effects.
- SLA Tracking: Priority-based response time policies with breach detection and warnings.
- Audit: All actions logged via TicketLog and AI suggestion logs.

```mermaid
sequenceDiagram
participant C as "Client"
participant M as "Ticket Model"
participant O as "TicketObserver"
participant ATS as "AutoTriageService"
participant AE as "AutomationEngine"
participant AR as "AssignmentRule"
participant PR as "PriorityRule"
participant ER as "EscalationRule"
participant S as "TicketAssignmentService"
C->>M : "Create ticket (verified=true)"
M->>O : "created()"
O->>ATS : "triage(ticket)"
ATS-->>M : "apply AI triage (category/priority)"
O->>AE : "processNewTicket(ticket)"
AE->>AR : "evaluate/apply"
AR->>S : "assignTicket(ticket)"
AE->>PR : "evaluate/apply (optional)"
PR-->>M : "set priority"
O-->>M : "fallback assign if none"
```

**Diagram sources**
- [Ticket.php:31-34](file://app/Models/Ticket.php#L31-L34)
- [TicketObserver.php:21-33](file://app/Observers/TicketObserver.php#L21-L33)
- [AutoTriageService.php:16-33](file://app/Services/AutoTriageService.php#L16-L33)
- [AutomationEngine.php:30-41](file://app/Services/Automation/AutomationEngine.php#L30-L41)
- [AssignmentRule.php:15-65](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L65)
- [PriorityRule.php:11-67](file://app/Services/Automation/Rules/PriorityRule.php#L11-L67)
- [TicketAssignmentService.php:22-39](file://app/Services/TicketAssignmentService.php#L22-L39)

## Detailed Component Analysis

### Status States and Transitions
- Allowed statuses: pending, open, in_progress, resolved, closed.
- Open tickets scope includes pending, open, and in_progress.
- Transitions:
  - From pending/open/in_progress to resolved or closed are manual or automated.
  - Reopening a closed ticket increments operator's assigned count.
  - Closing a ticket decrements operator's assigned count (unless already resolved/closed).

```mermaid
stateDiagram-v2
[*] --> Pending
Pending --> Open : "verified=true"
Open --> In_Progress : "agent replies/assigns"
In_Progress --> Resolved : "work complete"
In_Progress --> Closed : "client closed"
Resolved --> Closed : "mark closed"
Closed --> Open : "reopen (status changed)"
```

**Diagram sources**
- [Ticket.php:26-29](file://app/Models/Ticket.php#L26-L29)
- [Ticket.php:59-62](file://app/Models/Ticket.php#L59-L62)
- [TicketObserver.php:53-70](file://app/Observers/TicketObserver.php#L53-L70)

**Section sources**
- [Ticket.php:26-62](file://app/Models/Ticket.php#L26-L62)
- [TicketObserver.php:53-70](file://app/Observers/TicketObserver.php#L53-L70)

### Automated Status Changes Triggered by Replies
- Direct status change on reply is not implemented in the observed code.
- Indirect effect: escalation rule may increase priority after a ticket has been idle beyond configured hours, even if there were recent replies prior to the idle window boundary.

**Section sources**
- [EscalationRule.php:24-60](file://app/Services/Automation/Rules/EscalationRule.php#L24-L60)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)

### Time-Based Escalation Rules
- Idle threshold: configurable hours since last activity (updated_at).
- Eligible statuses and optional category filters.
- Actions: escalate priority, set specific priority, notify admin, reassign ticket.
- Executed by CLI command that iterates companies or a specific company.

```mermaid
flowchart TD
Start(["Evaluate Idle Tickets"]) --> Load["Load active escalation rules for company"]
Load --> Query["Query tickets: status in [pending,open], verified=true,<br/>updated_at older than idle_hours"]
Query --> Apply{"Rule conditions met?"}
Apply --> |No| Next["Next rule"]
Apply --> |Yes| Action["Apply actions:<br/>- escalate/set priority<br/>- notify admin<br/>- reassign"]
Action --> Next
Next --> Done(["Done"])
```

**Diagram sources**
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)
- [EscalationRule.php:62-85](file://app/Services/Automation/Rules/EscalationRule.php#L62-L85)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)

**Section sources**
- [EscalationRule.php:24-60](file://app/Services/Automation/Rules/EscalationRule.php#L24-L60)
- [EscalationRule.php:115-132](file://app/Services/Automation/Rules/EscalationRule.php#L115-L132)
- [ProcessTicketEscalations.php:16-53](file://app/Console/Commands/ProcessTicketEscalations.php#L16-L53)

### Priority-Based Automatic Changes
- Keywords in subject/description can trigger priority changes.
- Category and current priority filters supported.
- Applies only when priority is lower than target.

**Section sources**
- [PriorityRule.php:11-52](file://app/Services/Automation/Rules/PriorityRule.php#L11-L52)
- [PriorityRule.php:54-67](file://app/Services/Automation/Rules/PriorityRule.php#L54-L67)

### Assignment Automation
- Assigns operator based on category specialty or generalist availability.
- Falls back to default assignment if automation does not assign.
- Supports explicit operator assignment via rule actions.

**Section sources**
- [AssignmentRule.php:15-48](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L48)
- [AssignmentRule.php:50-65](file://app/Services/Automation/Rules/AssignmentRule.php#L50-L65)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)

### Timestamp Tracking
- created_at: initial creation timestamp.
- updated_at: last modified timestamp (used for escalation idle checks).
- resolved_at: set when status moves to resolved.
- closed_at: set when status moves to closed.
- Soft deletes: deleted_at stored via softDeletes.

```mermaid
classDiagram
class Ticket {
+string status
+string priority
+datetime created_at
+datetime updated_at
+datetime resolved_at
+datetime closed_at
+scopeOpen(query)
}
```

**Diagram sources**
- [Ticket.php:41-49](file://app/Models/Ticket.php#L41-L49)
- [Ticket.php:59-62](file://app/Models/Ticket.php#L59-L62)
- [2026_02_01_224222_create_tickets_table.php:26-44](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L26-L44)

**Section sources**
- [Ticket.php:41-49](file://app/Models/Ticket.php#L41-L49)
- [2026_02_01_224222_create_tickets_table.php:26-44](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L26-L44)

### Soft Delete and Archive Management
- Soft deletes enabled on Ticket model.
- Observer decrements operator counters on deletion unless ticket is already resolved/closed.
- Restored tickets increment counters accordingly.

**Section sources**
- [Ticket.php:12](file://app/Models/Ticket.php#L12)
- [TicketObserver.php:77-98](file://app/Observers/TicketObserver.php#L77-L98)

### Scope Methods for Filtering
- scopeOpen(query): returns tickets where status is open, in_progress, or pending.

**Section sources**
- [Ticket.php:59-62](file://app/Models/Ticket.php#L59-L62)

### Audit Trails
- TicketLog records actions performed on tickets (e.g., status changes, assignments).
- Logs include ticket_id, user_id, action, description, timestamps.
- AiSuggestionLog tracks AI suggestion generation, usage, and dismissal for analytics.

**Section sources**
- [TicketLog.php:9-24](file://app/Models/TicketLog.php#L9-L24)
- [AiSuggestionLog.php:11-18](file://app/Models/AiSuggestionLog.php#L11-L18)
- [2026_03_10_230354_create_ticket_logs_table.php:14-21](file://database/migrations/2026_03_10_230354_create_ticket_logs_table.php#L14-L21)
- [2026_03_20_000003_create_ai_suggestion_logs_table.php:14-21](file://database/migrations/2026_03_20_000003_create_ai_suggestion_logs_table.php#L14-L21)

### Status Transition Logic Examples
- Verified ticket creation triggers automation; AI auto-triage processes tickets for categorization and priority assignment.
- Escalation rule finds idle tickets and applies priority or reassignment actions.
- Operator counters are adjusted on status changes and lifecycle events.

```mermaid
sequenceDiagram
participant U as "User"
participant T as "Ticket"
participant O as "Observer"
participant ATS as "AutoTriageService"
participant AE as "AutomationEngine"
participant ER as "EscalationRule"
participant L as "TicketLog"
U->>T : "Create ticket (verified)"
T->>O : "created()"
O->>ATS : "triage(ticket)"
ATS-->>T : "apply AI triage"
O->>AE : "processNewTicket(ticket)"
AE->>ER : "findIdleTickets(rule)"
ER-->>AE : "matching tickets"
AE-->>T : "apply actions (priority/notify/reassign)"
T->>L : "log action"
```

**Diagram sources**
- [TicketObserver.php:21-33](file://app/Observers/TicketObserver.php#L21-L33)
- [AutoTriageService.php:16-33](file://app/Services/AutoTriageService.php#L16-L33)
- [AutomationEngine.php:30-41](file://app/Services/Automation/AutomationEngine.php#L30-L41)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)

## AI-Powered Features

### AI Auto-Triage System
The system now includes intelligent ticket categorization and priority assignment through AI:
- Keyword-based triage rules for immediate categorization.
- AI-powered triage using SupportReplyAgent for complex cases.
- Fallback to generic AI triage when no specific rules exist.
- Priority adjustment based on ticket complexity and category.

```mermaid
flowchart TD
Start(["New Ticket Created"]) --> CheckKeywords["Check Keyword Rules"]
CheckKeywords --> FoundKeywords{"Keywords Match?"}
FoundKeywords --> |Yes| ApplyKeywords["Apply Keyword Rule<br/>- Set category<br/>- Set priority"]
FoundKeywords --> |No| CheckAI["Check AI Triaging Rules"]
CheckAI --> AIExists{"AI Rule Exists?"}
AIExists --> |Yes| ApplyAIRule["Apply AI Rule"]
AIExists --> |No| GenericAI["Generic AI Triage"]
GenericAI --> Analyze["Analyze Ticket Context"]
Analyze --> SuggestCat["Suggest Category"]
Analyze --> SuggestPri["Suggest Priority"]
SuggestCat --> UpdateCat["Update Category"]
SuggestPri --> UpdatePri["Update Priority"]
ApplyKeywords --> End(["Complete"])
ApplyAIRule --> End
UpdateCat --> End
UpdatePri --> End
```

**Diagram sources**
- [AutoTriageService.php:16-33](file://app/Services/AutoTriageService.php#L16-L33)
- [AutoTriageService.php:35-62](file://app/Services/AutoTriageService.php#L35-L62)
- [AutoTriageService.php:64-81](file://app/Services/AutoTriageService.php#L64-L81)
- [AutoTriageService.php:83-144](file://app/Services/AutoTriageService.php#L83-L144)

**Section sources**
- [AutoTriageService.php:16-163](file://app/Services/AutoTriageService.php#L16-L163)
- [AutoTriageRules.php:85-120](file://app/Livewire/Ai/AutoTriageRules.php#L85-L120)

### AI Reply Suggestions
Enhanced reply generation with AI-powered suggestions:
- AI-generated reply suggestions with confidence scoring.
- Golden response training for better suggestions.
- User feedback collection for continuous improvement.
- Suggestion usage tracking and analytics.

**Section sources**
- [SupportReplyAgent.php:25-27](file://app/Ai/Agents/SupportReplyAgent.php#L25-L27)
- [GoldenResponse.php:14-21](file://app/Models/GoldenResponse.php#L14-L21)
- [AiSuggestionLog.php:11-18](file://app/Models/AiSuggestionLog.php#L11-L18)
- [UsageStats.php:15-51](file://app/Livewire/Ai/UsageStats.php#L15-L51)

### AI Training and Feedback System
Comprehensive system for improving AI suggestions:
- Golden response management for training AI models.
- Suggestion feedback collection (accept/dismiss).
- Usage statistics and acceptance rate tracking.
- Continuous learning from user interactions.

**Section sources**
- [GoldenResponse.php:28-51](file://app/Models/GoldenResponse.php#L28-L51)
- [AiSuggestionLog.php:25-38](file://app/Models/AiSuggestionLog.php#L25-L38)
- [UsageStats.php:53-95](file://app/Livewire/Ai/UsageStats.php#L53-L95)

## SLA Tracking & Management

### Priority-Based Response Time Policies
The system now implements comprehensive SLA tracking:
- Configurable response times based on ticket priority (low, medium, high, urgent).
- Company-specific SLA policies with timezone-aware calculations.
- Breach detection and automatic status updates.
- Warning notifications before SLA expiration.

```mermaid
flowchart TD
Start(["Ticket Created"]) --> CalcDue["Calculate Due Time<br/>based on Priority"]
CalcDue --> StoreDue["Store due_time<br/>and sla_status"]
StoreDue --> Monitor["Monitor SLA Status"]
Monitor --> CheckBreach{"SLA Breached?"}
CheckBreach --> |Yes| MarkBreach["Set sla_status = 'breached'"]
CheckBreach --> |No| CheckWarning{"Within Warning Window?"}
CheckWarning --> |Yes| WarnAdmin["Send SLA Warning"]
CheckWarning --> |No| Continue["Continue Normal Processing"]
MarkBreach --> Notify["Notify Stakeholders"]
WarnAdmin --> Continue
Continue --> Update["Ticket Updated"]
Update --> CalcDue
```

**Diagram sources**
- [TicketObserver.php:55-79](file://app/Observers/TicketObserver.php#L55-L79)
- [SlaPolicy.php:17-32](file://app/Models/SlaPolicy.php#L17-L32)

**Section sources**
- [TicketObserver.php:44-79](file://app/Observers/TicketObserver.php#L44-L79)
- [SlaPolicy.php:15-44](file://app/Models/SlaPolicy.php#L15-L44)
- [SlaPolicyRule.php:12-21](file://app/Models/SlaPolicyRule.php#L12-L21)

### SLA Configuration and Management
Administrative controls for SLA policies:
- Enable/disable SLA monitoring per company.
- Configure response times for each priority level.
- Set warning windows and auto-close policies.
- Category-specific SLA rules.

**Section sources**
- [SlaPolicy.php:17-32](file://app/Models/SlaPolicy.php#L17-L32)
- [SlaPolicyRule.php:12-21](file://app/Models/SlaPolicyRule.php#L12-L21)

## Dependency Analysis
- Ticket depends on User (assigned_to), Company, TicketCategory.
- Ticket has many TicketReply and TicketLog.
- AI models depend on Company scope for tenant isolation.
- AI agents provide conversational capabilities through Laravel AI SDK.
- AutoTriageService orchestrates AI-powered triage with rule evaluation.
- Observer depends on AutomationEngine, TicketAssignmentService, and SLA calculations.
- AutomationEngine dispatches rules and coordinates execution.
- EscalationRule uses TicketAssignmentService for reassignment.
- CLI command invokes AutomationEngine per company.
- UsageStats aggregates AI performance metrics.

```mermaid
graph LR
Ticket["Ticket"] --> User["User"]
Ticket --> Company["Company"]
Ticket --> Category["TicketCategory"]
Ticket --> Reply["TicketReply"]
Ticket --> Log["TicketLog"]
Ticket --> SlaPolicy["SlaPolicy"]
AiSuggestionLog["AiSuggestionLog"] --> Company
GoldenResponse["GoldenResponse"] --> Company
AutoTriageRule["AutoTriageRule"] --> Company
Observer["TicketObserver"] --> Engine["AutomationEngine"]
Observer --> SlaPolicy
Observer --> TicketAssignmentService
Engine --> RuleA["AssignmentRule"]
Engine --> RuleP["PriorityRule"]
Engine --> RuleE["EscalationRule"]
RuleE --> AssignSvc["TicketAssignmentService"]
AutoTriageService --> SupportReplyAgent
AutoTriageService --> AutoTriageRule
AutoTriageService --> CompanyAiSettings
CLI["ProcessTicketEscalations"] --> Engine
UsageStats["UsageStats"] --> AiSuggestionLog
```

**Diagram sources**
- [Ticket.php:16-29](file://app/Models/Ticket.php#L16-L29)
- [TicketObserver.php:12-15](file://app/Observers/TicketObserver.php#L12-L15)
- [AutoTriageService.php:5-10](file://app/Services/AutoTriageService.php#L5-L10)
- [SupportReplyAgent.php:16-17](file://app/Ai/Agents/SupportReplyAgent.php#L16-L17)
- [AutomationEngine.php:15-25](file://app/Services/Automation/AutomationEngine.php#L15-L25)
- [EscalationRule.php:147-155](file://app/Services/Automation/Rules/EscalationRule.php#L147-L155)
- [ProcessTicketEscalations.php:29](file://app/Console/Commands/ProcessTicketEscalations.php#L29)
- [UsageStats.php:5-8](file://app/Livewire/Ai/UsageStats.php#L5-L8)

**Section sources**
- [Ticket.php:16-29](file://app/Models/Ticket.php#L16-L29)
- [AutomationEngine.php:15-25](file://app/Services/Automation/AutomationEngine.php#L15-L25)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)

## Performance Considerations
- Indexes on tickets: company_id, ticket_number, customer_email, status, priority, assigned_to, verified, created_at.
- Use scopeOpen to efficiently query open work-in-progress tickets.
- Prefer batch processing via CLI for escalations to avoid heavy on-request computation.
- Keep rule conditions narrow (category, priority filters) to reduce evaluation overhead.
- AI operations should be cached and rate-limited to prevent performance degradation.
- SLA calculations use timezone-aware carbon instances for accurate timing.
- AI suggestion logs are timestamp-indexed for efficient analytics queries.

**Section sources**
- [2026_02_01_224222_create_tickets_table.php:46-54](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L46-L54)
- [Ticket.php:59-62](file://app/Models/Ticket.php#L59-L62)
- [TicketObserver.php:55-79](file://app/Observers/TicketObserver.php#L55-L79)

## Troubleshooting Guide
- Ticket remains unassigned after creation:
  - Verify ticket is marked verified; automation runs only for verified tickets.
  - Check active automation rules and their conditions.
  - Confirm operators are available and have appropriate specialties.
- Operator assigned count incorrect:
  - Recalculate counts via service method to sync post-migration or after bulk changes.
- Escalation not triggering:
  - Confirm idle hours threshold and status filters.
  - Ensure the CLI command is scheduled and running.
- AI suggestions not working:
  - Verify AI provider configuration and model settings.
  - Check golden response training data quality.
  - Ensure AI auto-triage rules are properly configured.
- SLA tracking issues:
  - Verify SLA policy is enabled for the company.
  - Check timezone configuration for accurate due time calculations.
  - Review SLA breach notifications and warning thresholds.
- Status not updating:
  - Ensure status transitions are initiated manually or via automation rules.
  - Check observer logic for reopen/close adjustments.

**Section sources**
- [TicketObserver.php:23-32](file://app/Observers/TicketObserver.php#L23-L32)
- [TicketAssignmentService.php:166-177](file://app/Services/TicketAssignmentService.php#L166-L177)
- [ProcessTicketEscalations.php:32-48](file://app/Console/Commands/ProcessTicketEscalations.php#L32-L48)
- [AutoTriageService.php:22-24](file://app/Services/AutoTriageService.php#L22-L24)
- [TicketObserver.php:55-79](file://app/Observers/TicketObserver.php#L55-L79)

## Conclusion
The HelpDesk System enforces a robust ticket lifecycle governed by verified-triggered automation, AI-powered auto-triage, time-based escalation, and explicit status transitions. The system now includes comprehensive AI features for reply suggestions, golden response training, and intelligent triage. SLA tracking provides priority-based response time management with breach detection and warnings. Timestamps and soft deletes support auditing and archival, while scopes and indexes enable efficient querying. The observer and services maintain operational counters and ensure consistent state transitions across the system, enhanced by machine learning capabilities for improved efficiency and accuracy.

## Appendices
- Example controller usage for viewing a ticket:
  - [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- AI usage statistics dashboard:
  - [UsageStats.php:97-100](file://app/Livewire/Ai/UsageStats.php#L97-L100)
- Auto-triage rule management:
  - [AutoTriageRules.php:155-158](file://app/Livewire/Ai/AutoTriageRules.php#L155-L158)

**Section sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [UsageStats.php:97-100](file://app/Livewire/Ai/UsageStats.php#L97-L100)
- [AutoTriageRules.php:155-158](file://app/Livewire/Ai/AutoTriageRules.php#L155-L158)