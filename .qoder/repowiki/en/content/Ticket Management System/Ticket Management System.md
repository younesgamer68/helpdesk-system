# Ticket Management System

<cite>
**Referenced Files in This Document**
- [Ticket.php](file://app/Models/Ticket.php)
- [TicketCategory.php](file://app/Models/TicketCategory.php)
- [TicketReply.php](file://app/Models/TicketReply.php)
- [TicketsController.php](file://app/Http/Controllers/TicketsController.php)
- [TicketDetails.php](file://app/Livewire/Dashboard/TicketDetails.php)
- [TicketsTable.php](file://app/Livewire/Dashboard/TicketsTable.php)
- [TicketAssignmentService.php](file://app/Services/TicketAssignmentService.php)
- [AutomationEngine.php](file://app/Services/Automation/AutomationEngine.php)
- [AssignmentRule.php](file://app/Services/Automation/Rules/AssignmentRule.php)
- [EscalationRule.php](file://app/Services/Automation/Rules/EscalationRule.php)
- [ProcessTicketEscalations.php](file://app/Console/Commands/ProcessTicketEscalations.php)
- [SavedFilterView.php](file://app/Models/SavedFilterView.php)
- [show.blade.php](file://resources/views/dashboard/tickets/show.blade.php)
- [ticket-details.blade.php](file://resources/views/livewire/dashboard/ticket-details.blade.php)
- [tickets-table.blade.php](file://resources/views/livewire/dashboard/tickets-table.blade.php)
- [TicketFactory.php](file://database/factories/TicketFactory.php)
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

## Introduction
This document describes the ticket management system, focusing on the complete lifecycle from creation to resolution, including status management, priority levels, categorization, reply handling, assignment workflows, search and filtering, saved views, bulk operations, and automation integration for rule-based processing and escalation handling.

## Project Structure
The system is built with Laravel and Livewire. Key areas:
- Models define domain entities (Ticket, TicketCategory, TicketReply, SavedFilterView)
- Controllers orchestrate HTTP requests (TicketsController)
- Livewire components provide interactive UI for ticket details and listings
- Services encapsulate business logic (TicketAssignmentService)
- Automation engine applies rules for assignment, priority, auto-reply, and escalation
- Blade templates render UI and integrate Livewire components

```mermaid
graph TB
subgraph "HTTP Layer"
Ctlr["TicketsController"]
end
subgraph "Livewire UI"
DT["TicketDetails"]
TT["TicketsTable"]
end
subgraph "Domain Models"
M_Ticket["Ticket"]
M_Category["TicketCategory"]
M_Reply["TicketReply"]
M_View["SavedFilterView"]
end
subgraph "Services"
S_Assign["TicketAssignmentService"]
S_Auto["AutomationEngine"]
end
subgraph "Automation Rules"
R_Assign["AssignmentRule"]
R_Escalate["EscalationRule"]
end
subgraph "Console"
Cmd["ProcessTicketEscalations"]
end
Ctlr --> DT
DT --> M_Ticket
DT --> M_Reply
DT --> S_Assign
TT --> M_Ticket
TT --> M_View
S_Assign --> M_Ticket
S_Assign --> M_Ticket
S_Auto --> R_Assign
S_Auto --> R_Escalate
Cmd --> S_Auto
```

**Diagram sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [TicketDetails.php:61-65](file://app/Livewire/Dashboard/TicketDetails.php#L61-L65)
- [TicketsTable.php:260-317](file://app/Livewire/Dashboard/TicketsTable.php#L260-L317)
- [Ticket.php:16-54](file://app/Models/Ticket.php#L16-L54)
- [TicketReply.php:29-37](file://app/Models/TicketReply.php#L29-L37)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)
- [AutomationEngine.php:30-41](file://app/Services/Automation/AutomationEngine.php#L30-L41)
- [AssignmentRule.php:15-48](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L48)
- [EscalationRule.php:24-60](file://app/Services/Automation/Rules/EscalationRule.php#L24-L60)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)

**Section sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [TicketDetails.php:61-65](file://app/Livewire/Dashboard/TicketDetails.php#L61-L65)
- [TicketsTable.php:260-317](file://app/Livewire/Dashboard/TicketsTable.php#L260-L317)

## Core Components
- Ticket model defines lifecycle fields (status, priority, timestamps), relationships to company, category, user, replies, and logs, plus an open scope.
- TicketCategory model represents hierarchical categories (via category_id relationship).
- TicketReply model supports both public customer communications and internal agent notes, with attachments and casting for booleans and arrays.
- TicketAssignmentService handles automatic assignment to specialists/generalists and manual reassignment.
- AutomationEngine executes rules for new tickets and escalations via scheduled command.
- SavedFilterView persists user-defined filter configurations.

**Section sources**
- [Ticket.php:16-62](file://app/Models/Ticket.php#L16-L62)
- [TicketCategory.php:8-13](file://app/Models/TicketCategory.php#L8-L13)
- [TicketReply.php:10-37](file://app/Models/TicketReply.php#L10-L37)
- [TicketAssignmentService.php:22-160](file://app/Services/TicketAssignmentService.php#L22-L160)
- [AutomationEngine.php:30-96](file://app/Services/Automation/AutomationEngine.php#L30-L96)
- [SavedFilterView.php:14-30](file://app/Models/SavedFilterView.php#L14-L30)

## Architecture Overview
The system separates concerns across models, services, Livewire components, and automation. HTTP requests route to controllers, which delegate to Livewire components for rendering and interactivity. Services encapsulate business logic, and automation runs independently to enforce policies.

```mermaid
sequenceDiagram
participant U as "User"
participant Ctrl as "TicketsController"
participant LW as "TicketDetails"
participant M as "Ticket"
participant R as "TicketReply"
participant A as "AutomationEngine"
participant S as "TicketAssignmentService"
U->>Ctrl : "View ticket"
Ctrl->>LW : "Render ticket details"
LW->>M : "Load ticket with relations"
U->>LW : "Add reply/internal note"
LW->>R : "Create reply (public/internal)"
LW->>M : "Update status if needed"
LW->>A : "Trigger rule evaluation (optional)"
A->>S : "Apply assignment/priority actions (optional)"
LW-->>U : "UI updates"
```

**Diagram sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [TicketDetails.php:263-322](file://app/Livewire/Dashboard/TicketDetails.php#L263-L322)
- [TicketDetails.php:475-505](file://app/Livewire/Dashboard/TicketDetails.php#L475-L505)
- [AutomationEngine.php:59-96](file://app/Services/Automation/AutomationEngine.php#L59-L96)
- [TicketAssignmentService.php:99-108](file://app/Services/TicketAssignmentService.php#L99-L108)

## Detailed Component Analysis

### Ticket Lifecycle and Status Management
- Status lifecycle includes pending, open, in_progress, resolved, closed. The model exposes an open scope for active tickets.
- UI supports resolving/unresolving and closing tickets, updating timestamps accordingly.
- Status transitions are validated in the UI and logged.

```mermaid
stateDiagram-v2
[*] --> Pending
Pending --> Open
Open --> In_Progress
In_Progress --> Resolved
Resolved --> Closed
In_Progress --> Open : "Unresolve"
Resolved --> Open : "Unresolve"
```

**Diagram sources**
- [TicketDetails.php:91-143](file://app/Livewire/Dashboard/TicketDetails.php#L91-L143)
- [TicketDetails.php:237-261](file://app/Livewire/Dashboard/TicketDetails.php#L237-L261)
- [TicketFactory.php:14-244](file://database/factories/TicketFactory.php#L14-L244)

**Section sources**
- [Ticket.php:59-62](file://app/Models/Ticket.php#L59-L62)
- [TicketDetails.php:91-143](file://app/Livewire/Dashboard/TicketDetails.php#L91-L143)
- [TicketDetails.php:237-261](file://app/Livewire/Dashboard/TicketDetails.php#L237-L261)
- [TicketFactory.php:14-244](file://database/factories/TicketFactory.php#L14-L244)

### Priority Levels
- Supported priorities: low, medium, high, urgent.
- UI validates priority changes and notifies stakeholders when appropriate.
- Escalation rule can increase priority based on idle thresholds.

**Section sources**
- [TicketDetails.php:176-203](file://app/Livewire/Dashboard/TicketDetails.php#L176-L203)
- [EscalationRule.php:115-132](file://app/Services/Automation/Rules/EscalationRule.php#L115-L132)

### Ticket Categorization and Routing
- Tickets belong to a category via category_id; categories are associated with operators by specialty.
- Automatic assignment prefers specialists matching the ticket’s category; falls back to generalists.
- UI displays category color badges and allows category selection during creation and filtering.

```mermaid
classDiagram
class Ticket {
+int category_id
+string status
+string priority
+datetime created_at
+datetime updated_at
+datetime resolved_at
+datetime closed_at
}
class TicketCategory {
+int id
+string name
+string color
}
class User {
+int specialty_id
}
Ticket --> TicketCategory : "belongsTo"
User --> TicketCategory : "specialty"
```

**Diagram sources**
- [Ticket.php:26-29](file://app/Models/Ticket.php#L26-L29)
- [TicketAssignmentService.php:44-53](file://app/Services/TicketAssignmentService.php#L44-L53)

**Section sources**
- [Ticket.php:26-29](file://app/Models/Ticket.php#L26-L29)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)
- [ticket-details.blade.php:79-89](file://resources/views/livewire/dashboard/ticket-details.blade.php#L79-L89)

### Reply System: Public and Internal
- Public replies are visible to customers; internal notes are visible only to agents.
- Both support rich text and optional attachments.
- UI toggles between conversation and internal notes tabs.

```mermaid
flowchart TD
Start(["Add Reply"]) --> CheckStatus{"Ticket closed?"}
CheckStatus --> |Yes| Block["Block reply"]
CheckStatus --> |No| Validate["Validate message"]
Validate --> IsInternal{"Internal note?"}
IsInternal --> |Yes| CreateInternal["Create internal reply"]
IsInternal --> |No| CreatePublic["Create public reply"]
CreatePublic --> MaybeProgress{"Keep open?"}
MaybeProgress --> |No| SetInProgress["Set status to in_progress"]
MaybeProgress --> |Yes| Done["Done"]
CreateInternal --> Done
SetInProgress --> Done
Block --> End(["Exit"])
Done --> End
```

**Diagram sources**
- [TicketDetails.php:263-322](file://app/Livewire/Dashboard/TicketDetails.php#L263-L322)
- [TicketDetails.php:475-505](file://app/Livewire/Dashboard/TicketDetails.php#L475-L505)

**Section sources**
- [TicketReply.php:10-18](file://app/Models/TicketReply.php#L10-L18)
- [ticket-details.blade.php:132-287](file://resources/views/livewire/dashboard/ticket-details.blade.php#L132-L287)
- [ticket-details.blade.php:710-800](file://resources/views/livewire/dashboard/ticket-details.blade.php#L710-L800)

### Assignment Workflows
- Automatic assignment:
  - If category exists, find an available operator with matching specialty and lowest workload.
  - Else, find an available generalist (no specialty) with lowest workload.
  - If none available, notify admins via TicketUnassigned.
- Manual reassignment:
  - Adjust assigned_to and update counters for previous/new operators.
- UI supports assigning to specific agents and reassigning.

```mermaid
sequenceDiagram
participant UI as "TicketDetails.assign()"
participant Svc as "TicketAssignmentService"
participant DB as "DB Transaction"
participant Op as "Operator"
UI->>Svc : "assignTicket(ticket)"
Svc->>DB : "Find specialist/generalist"
DB-->>Svc : "Best candidate"
Svc->>DB : "Update ticket.assigned_to + increment counter"
DB-->>Op : "Notify"
Svc-->>UI : "Assigned"
```

**Diagram sources**
- [TicketDetails.php:145-174](file://app/Livewire/Dashboard/TicketDetails.php#L145-L174)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)

**Section sources**
- [TicketAssignmentService.php:22-160](file://app/Services/TicketAssignmentService.php#L22-L160)
- [TicketDetails.php:145-174](file://app/Livewire/Dashboard/TicketDetails.php#L145-L174)

### Search, Filtering, Saved Views, and Bulk Operations
- Search across ticket_number, subject, customer_name, description.
- Filters: status, priority, category, assigned agent (admin), date range.
- Sorting by configurable column and direction.
- SavedFilterView stores user filter presets.
- Bulk operations: set status, set priority, assign agent for selected tickets.

```mermaid
flowchart TD
F["Filter Form"] --> Apply["Apply filters"]
Apply --> Page["Paginated Results"]
Page --> SaveView["Save current view"]
SaveView --> Presets["User presets"]
Page --> Select["Select tickets"]
Select --> Bulk["Bulk actions"]
Bulk --> BulkStatus["Set status"]
Bulk --> BulkPriority["Set priority"]
Bulk --> BulkAssign["Assign agent"]
```

**Diagram sources**
- [TicketsTable.php:18-74](file://app/Livewire/Dashboard/TicketsTable.php#L18-L74)
- [TicketsTable.php:259-317](file://app/Livewire/Dashboard/TicketsTable.php#L259-L317)
- [SavedFilterView.php:14-30](file://app/Models/SavedFilterView.php#L14-L30)
- [TicketsTable.php:460-516](file://app/Livewire/Dashboard/TicketsTable.php#L460-L516)

**Section sources**
- [TicketsTable.php:259-317](file://app/Livewire/Dashboard/TicketsTable.php#L259-L317)
- [SavedFilterView.php:14-30](file://app/Models/SavedFilterView.php#L14-L30)
- [TicketsTable.php:460-516](file://app/Livewire/Dashboard/TicketsTable.php#L460-L516)

### Automation Engine and Escalation Handling
- New ticket processing: evaluates assignment, priority, auto-reply rules (excluding escalations).
- Escalation processing: scheduled command scans idle tickets and escalates priority, notifies admins, or reassigns.
- Rule types:
  - AssignmentRule: assigns specialists/generalists or specific operators.
  - EscalationRule: increases priority, sets specific priority, notifies admins, reassigns operator.

```mermaid
sequenceDiagram
participant Cron as "ProcessTicketEscalations"
participant Eng as "AutomationEngine"
participant Rule as "EscalationRule"
participant DB as "DB"
participant Admin as "Admin"
Cron->>Eng : "processEscalations(companyId)"
Eng->>Rule : "findIdleTickets(rule)"
Rule->>DB : "Query idle tickets"
DB-->>Rule : "Matching tickets"
loop For each ticket
Eng->>Rule : "evaluate(ticket)"
Rule-->>Eng : "true/false"
Eng->>Rule : "apply(ticket)"
Rule->>DB : "Update priority/status"
Rule->>Admin : "Notify via mail"
end
```

**Diagram sources**
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)
- [AutomationEngine.php:46-54](file://app/Services/Automation/AutomationEngine.php#L46-L54)
- [EscalationRule.php:92-113](file://app/Services/Automation/Rules/EscalationRule.php#L92-L113)
- [EscalationRule.php:134-155](file://app/Services/Automation/Rules/EscalationRule.php#L134-L155)

**Section sources**
- [AutomationEngine.php:30-96](file://app/Services/Automation/AutomationEngine.php#L30-L96)
- [AssignmentRule.php:15-65](file://app/Services/Automation/Rules/AssignmentRule.php#L15-L65)
- [EscalationRule.php:24-155](file://app/Services/Automation/Rules/EscalationRule.php#L24-L155)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)

## Dependency Analysis
- Controllers depend on Livewire components for rendering.
- Livewire components depend on models and services for persistence and business logic.
- Services encapsulate assignment and automation logic, minimizing coupling.
- Automation rules are pluggable and evaluated by the engine.

```mermaid
graph LR
Ctrl["TicketsController"] --> View["show.blade.php"]
View --> LW["TicketDetails"]
LW --> M_T["Ticket"]
LW --> M_R["TicketReply"]
LW --> S_Asg["TicketAssignmentService"]
LW --> S_Auto["AutomationEngine"]
TT["TicketsTable"] --> M_T
TT --> M_View["SavedFilterView"]
S_Auto --> R_Assign["AssignmentRule"]
S_Auto --> R_Escalate["EscalationRule"]
```

**Diagram sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [show.blade.php:1-4](file://resources/views/dashboard/tickets/show.blade.php#L1-L4)
- [TicketDetails.php:61-65](file://app/Livewire/Dashboard/TicketDetails.php#L61-L65)
- [TicketsTable.php:260-317](file://app/Livewire/Dashboard/TicketsTable.php#L260-L317)
- [TicketAssignmentService.php:22-94](file://app/Services/TicketAssignmentService.php#L22-L94)
- [AutomationEngine.php:30-96](file://app/Services/Automation/AutomationEngine.php#L30-L96)

**Section sources**
- [TicketsController.php:12-17](file://app/Http/Controllers/TicketsController.php#L12-L17)
- [TicketDetails.php:61-65](file://app/Livewire/Dashboard/TicketDetails.php#L61-L65)
- [TicketsTable.php:260-317](file://app/Livewire/Dashboard/TicketsTable.php#L260-L317)

## Performance Considerations
- Use pagination for ticket lists to avoid loading large datasets.
- Cache category and agent lists for admin dashboards to reduce queries.
- Batch operations (bulk updates) minimize individual writes.
- Escalation processing runs via a scheduled command to avoid blocking requests.

## Troubleshooting Guide
- Auto-assignment fails: check availability and specialties of operators; verify category presence; review admin notifications for TicketUnassigned.
- Escalation not applied: ensure the scheduled command is running and that idle thresholds and statuses match expectations.
- Replies not visible: confirm is_internal flag and visibility settings; verify attachment storage permissions.
- Filters not working: validate filter keys and ensure SavedFilterView entries are correctly stored.

**Section sources**
- [TicketAssignmentService.php:84-94](file://app/Services/TicketAssignmentService.php#L84-L94)
- [ProcessTicketEscalations.php:29-53](file://app/Console/Commands/ProcessTicketEscalations.php#L29-L53)
- [TicketDetails.php:263-322](file://app/Livewire/Dashboard/TicketDetails.php#L263-L322)
- [SavedFilterView.php:14-30](file://app/Models/SavedFilterView.php#L14-L30)

## Conclusion
The ticket management system provides a robust lifecycle with clear status and priority controls, flexible categorization with specialized routing, dual-mode reply handling, and powerful automation for assignment and escalation. The UI integrates seamlessly with backend services to support efficient agent workflows and customer communication.