# Dashboard & Analytics

<cite>
**Referenced Files in This Document**
- [AdminDashboard.php](file://app/Livewire/Dashboard/AdminDashboard.php)
- [AgentDashboard.php](file://app/Livewire/Dashboard/AgentDashboard.php)
- [ReportsAnalytics.php](file://app/Livewire/Dashboard/ReportsAnalytics.php)
- [admin-dashboard.blade.php](file://resources/views/livewire/dashboard/admin-dashboard.blade.php)
- [agent-dashboard.blade.php](file://resources/views/livewire/dashboard/agent-dashboard.blade.php)
- [reports-analytics.blade.php](file://resources/views/livewire/dashboard/reports-analytics.blade.php)
- [web.php](file://routes/web.php)
- [AdminOnly.php](file://app/Http/Middleware/AdminOnly.php)
- [AgentOnly.php](file://app/Http/Middleware/AgentOnly.php)
- [Ticket.php](file://app/Models/Ticket.php)
- [User.php](file://app/Models/User.php)
- [header.blade.php](file://resources/views/components/dashboard/reports/header.blade.php)
- [tabs.blade.php](file://resources/views/components/dashboard/reports/tabs.blade.php)
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
This document explains the dual dashboard architecture that serves both administrators and agents, along with comprehensive reporting and analytics capabilities. It covers:
- Role-specific dashboards: admin overview and agent workflow
- Metrics and KPIs: open/resolved/unassigned tickets, agent activity, recent system activity
- Reporting and analytics: ticket volume trends, resolution times, agent productivity, category performance
- Interactive charts, export functionality, and customizable report views
- Guidance for creating custom dashboard widgets and integrating additional business intelligence metrics

## Project Structure
The dashboards and analytics are implemented as Livewire components with Blade templates and routed under company subdomains. Middleware enforces role-based access.

```mermaid
graph TB
subgraph "Routing"
RWeb["routes/web.php"]
end
subgraph "Middleware"
MAdmin["AdminOnly.php"]
MAgent["AgentOnly.php"]
end
subgraph "Livewire Dashboards"
DAdmin["AdminDashboard.php"]
DAgent["AgentDashboard.php"]
DReports["ReportsAnalytics.php"]
end
subgraph "Blade Templates"
TAdmin["admin-dashboard.blade.php"]
TAgent["agent-dashboard.blade.php"]
TReports["reports-analytics.blade.php"]
THdr["components/dashboard/reports/header.blade.php"]
TTabs["components/dashboard/reports/tabs.blade.php"]
end
subgraph "Models"
MTicket["Ticket.php"]
MUser["User.php"]
end
RWeb --> MAdmin
RWeb --> MAgent
RWeb --> DAdmin
RWeb --> DAgent
RWeb --> DReports
DAdmin --> TAdmin
DAgent --> TAgent
DReports --> TReports
TReports --> THdr
TReports --> TTabs
DAdmin --> MTicket
DAdmin --> MUser
DAgent --> MTicket
DAgent --> MUser
DReports --> MTicket
DReports --> MUser
```

**Diagram sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [AdminOnly.php:16-23](file://app/Http/Middleware/AdminOnly.php#L16-L23)
- [AgentOnly.php:16-23](file://app/Http/Middleware/AgentOnly.php#L16-L23)
- [AdminDashboard.php:14-127](file://app/Livewire/Dashboard/AdminDashboard.php#L14-L127)
- [AgentDashboard.php:16-141](file://app/Livewire/Dashboard/AgentDashboard.php#L16-L141)
- [ReportsAnalytics.php:21-1012](file://app/Livewire/Dashboard/ReportsAnalytics.php#L21-L1012)
- [admin-dashboard.blade.php:1-406](file://resources/views/livewire/dashboard/admin-dashboard.blade.php#L1-L406)
- [agent-dashboard.blade.php:1-268](file://resources/views/livewire/dashboard/agent-dashboard.blade.php#L1-L268)
- [reports-analytics.blade.php:1-32](file://resources/views/livewire/dashboard/reports-analytics.blade.php#L1-L32)
- [Ticket.php:9-64](file://app/Models/Ticket.php#L9-L64)
- [User.php:13-137](file://app/Models/User.php#L13-L137)

**Section sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [AdminOnly.php:16-23](file://app/Http/Middleware/AdminOnly.php#L16-L23)
- [AgentOnly.php:16-23](file://app/Http/Middleware/AgentOnly.php#L16-L23)

## Core Components
- AdminDashboard: Provides system-wide KPIs, lists of open/resolved/unassigned tickets, agent workload, and recent activity.
- AgentDashboard: Focuses on personal KPIs (open/resolved/pending), quick access to unassigned tickets, and recent notifications.
- ReportsAnalytics: Central analytics hub with tabs for Overview, Agent Performance, Tickets, and Categories; supports date presets, filters, sorting, and exports.

**Section sources**
- [AdminDashboard.php:14-127](file://app/Livewire/Dashboard/AdminDashboard.php#L14-L127)
- [AgentDashboard.php:16-141](file://app/Livewire/Dashboard/AgentDashboard.php#L16-L141)
- [ReportsAnalytics.php:21-1012](file://app/Livewire/Dashboard/ReportsAnalytics.php#L21-L1012)

## Architecture Overview
The dashboards are rendered server-side via Livewire components and templates. Routing detects the user’s role and company subdomain to direct them to the appropriate dashboard. Middleware ensures only authorized roles access specific dashboards.

```mermaid
sequenceDiagram
participant Browser as "Browser"
participant Router as "routes/web.php"
participant MWAdmin as "AdminOnly.php"
participant MWAgent as "AgentOnly.php"
participant AdminDash as "AdminDashboard.php"
participant AgentDash as "AgentDashboard.php"
participant Reports as "ReportsAnalytics.php"
Browser->>Router : GET /{company}/dashboard
Router->>Router : Determine user role and redirect
alt Admin
Router->>MWAdmin : Apply middleware
MWAdmin-->>Router : Allow
Router->>AdminDash : Render AdminDashboard
AdminDash-->>Browser : admin-dashboard.blade.php
else Agent/Operator
Router->>MWAgent : Apply middleware
MWAgent-->>Router : Allow
Router->>AgentDash : Render AgentDashboard
AgentDash-->>Browser : agent-dashboard.blade.php
end
Browser->>Router : GET /{company}/reports
Router->>Reports : Render ReportsAnalytics
Reports-->>Browser : reports-analytics.blade.php
```

**Diagram sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [AdminOnly.php:16-23](file://app/Http/Middleware/AdminOnly.php#L16-L23)
- [AgentOnly.php:16-23](file://app/Http/Middleware/AgentOnly.php#L16-L23)
- [AdminDashboard.php:122-127](file://app/Livewire/Dashboard/AdminDashboard.php#L122-L127)
- [AgentDashboard.php:137-141](file://app/Livewire/Dashboard/AgentDashboard.php#L137-L141)
- [ReportsAnalytics.php:1007-1012](file://app/Livewire/Dashboard/ReportsAnalytics.php#L1007-L1012)

## Detailed Component Analysis

### Admin Dashboard
- Purpose: Provide company/system overview for administrators.
- Key metrics:
  - Open tickets count and list
  - Resolved today count and list
  - Unassigned tickets count and list
  - Total agents count and per-agent open ticket counts
  - Recent tickets feed
  - Agent activity bar chart (workload vs capacity)
  - Recent system activity log
- Views: Blade template renders KPI cards, tables, and modals for drill-down.

```mermaid
flowchart TD
Start(["AdminDashboard render"]) --> KPIs["Compute KPIs<br/>open/resolved/unassigned/agents"]
KPIs --> Lists["Fetch lists<br/>recent/open/resolved/unassigned"]
Lists --> Activity["Fetch agent activity<br/>and recent logs"]
Activity --> Render["Render admin-dashboard.blade.php"]
Render --> End(["UI with modals"])
```

**Diagram sources**
- [AdminDashboard.php:16-120](file://app/Livewire/Dashboard/AdminDashboard.php#L16-L120)
- [admin-dashboard.blade.php:8-298](file://resources/views/livewire/dashboard/admin-dashboard.blade.php#L8-L298)

**Section sources**
- [AdminDashboard.php:16-120](file://app/Livewire/Dashboard/AdminDashboard.php#L16-L120)
- [admin-dashboard.blade.php:8-298](file://resources/views/livewire/dashboard/admin-dashboard.blade.php#L8-L298)

### Agent Dashboard
- Purpose: Enable agents/operators to manage their workload and stay informed.
- Key metrics:
  - Open tickets count and list
  - Resolved today count and list
  - Pending reply count and list
  - Unread notifications count
  - My tickets (ordered by priority and age)
  - Unassigned tickets with “Assign to me” action
  - Recent notifications
- Interactions: Self-assignment of unassigned tickets updates logs and emits toast feedback.

```mermaid
sequenceDiagram
participant Agent as "Agent"
participant AgentDash as "AgentDashboard.php"
participant DB as "DB : Tickets/Users"
participant Log as "TicketLog"
Agent->>AgentDash : Click "Assign to me"
AgentDash->>DB : Find unassigned ticket in company
DB-->>AgentDash : Ticket record
AgentDash->>DB : Update assigned_to + status
DB-->>AgentDash : Updated ticket
AgentDash->>Log : Create log entry
Log-->>AgentDash : OK
AgentDash-->>Agent : Emit success toast
```

**Diagram sources**
- [AgentDashboard.php:115-135](file://app/Livewire/Dashboard/AgentDashboard.php#L115-L135)

**Section sources**
- [AgentDashboard.php:18-141](file://app/Livewire/Dashboard/AgentDashboard.php#L18-L141)
- [agent-dashboard.blade.php:8-268](file://resources/views/livewire/dashboard/agent-dashboard.blade.php#L8-L268)

### Reports & Analytics
- Tabs: Overview, Agent Performance, Tickets, Categories.
- Date range controls: presets (today, this week, this month, last 3 months) and custom date pickers.
- Filters: status, priority, category, agent; plus free-text ticket search.
- Sorting: tickets and agents by various columns.
- Charts: ticket volume trend, status breakdown, priority breakdown, category volume, agent leaderboards, category health.
- Exports: CSV for tickets and agents; PDF export overlay present in template.
- Data aggregation: optimized with single-query aggregations and memoized previous period dates.

```mermaid
classDiagram
class ReportsAnalytics {
+string activeTab
+string datePreset
+string startDate
+string endDate
+string filterStatus
+string filterPriority
+string filterCategory
+string filterAgent
+string ticketSearch
+string ticketSortBy
+string ticketSortDir
+string agentSortColumn
+string agentSortDirection
+applyPreset(preset)
+updatedDatePreset()
+updatedStartDate()
+updatedEndDate()
+setTab(tab)
+applyChartFilter(type, value)
+selectAgent(agentId)
+toggleCategory(categoryId)
+setTicketSort(column)
+setAgentSort(column)
+clearTicketFilters()
+ticketVolumeChart()
+statusBreakdown()
+priorityBreakdown()
+categoryVolume()
+agentLeaderboard()
+categoryHealth()
+expandedCategoryDetails()
+selectedAgentData()
+allAgentPerformance()
+agentDailyLabels()
+agents()
+categories()
+paginatedTickets()
+exportTicketsCsv()
+exportAgentsCsv()
}
```

**Diagram sources**
- [ReportsAnalytics.php:21-1012](file://app/Livewire/Dashboard/ReportsAnalytics.php#L21-L1012)

**Section sources**
- [ReportsAnalytics.php:25-187](file://app/Livewire/Dashboard/ReportsAnalytics.php#L25-L187)
- [ReportsAnalytics.php:277-381](file://app/Livewire/Dashboard/ReportsAnalytics.php#L277-L381)
- [ReportsAnalytics.php:383-482](file://app/Livewire/Dashboard/ReportsAnalytics.php#L383-L482)
- [ReportsAnalytics.php:489-574](file://app/Livewire/Dashboard/ReportsAnalytics.php#L489-L574)
- [ReportsAnalytics.php:576-721](file://app/Livewire/Dashboard/ReportsAnalytics.php#L576-L721)
- [ReportsAnalytics.php:724-807](file://app/Livewire/Dashboard/ReportsAnalytics.php#L724-L807)
- [ReportsAnalytics.php:839-868](file://app/Livewire/Dashboard/ReportsAnalytics.php#L839-L868)
- [ReportsAnalytics.php:875-946](file://app/Livewire/Dashboard/ReportsAnalytics.php#L875-L946)
- [ReportsAnalytics.php:948-973](file://app/Livewire/Dashboard/ReportsAnalytics.php#L948-L973)
- [reports-analytics.blade.php:1-32](file://resources/views/livewire/dashboard/reports-analytics.blade.php#L1-L32)
- [header.blade.php:1-24](file://resources/views/components/dashboard/reports/header.blade.php#L1-L24)
- [tabs.blade.php:1-38](file://resources/views/components/dashboard/reports/tabs.blade.php#L1-L38)

## Dependency Analysis
- Routing depends on user role and company subdomain to choose the correct dashboard.
- Middleware restricts access to AdminDashboard and AgentDashboard.
- Dashboards depend on models for data retrieval and Eloquent relationships.
- ReportsAnalytics coordinates multiple data sources and presents them via Blade components.

```mermaid
graph LR
Routes["routes/web.php"] --> MWAdmin["AdminOnly.php"]
Routes --> MWAgent["AgentOnly.php"]
Routes --> DAdmin["AdminDashboard.php"]
Routes --> DAgent["AgentDashboard.php"]
Routes --> DReports["ReportsAnalytics.php"]
DAdmin --> MTicket["Ticket.php"]
DAdmin --> MUser["User.php"]
DAgent --> MTicket
DAgent --> MUser
DReports --> MTicket
DReports --> MUser
DAdmin --> TAdmin["admin-dashboard.blade.php"]
DAgent --> TAgent["agent-dashboard.blade.php"]
DReports --> TReports["reports-analytics.blade.php"]
TReports --> THdr["header.blade.php"]
TReports --> TTabs["tabs.blade.php"]
```

**Diagram sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [AdminOnly.php:16-23](file://app/Http/Middleware/AdminOnly.php#L16-L23)
- [AgentOnly.php:16-23](file://app/Http/Middleware/AgentOnly.php#L16-L23)
- [AdminDashboard.php:14-127](file://app/Livewire/Dashboard/AdminDashboard.php#L14-L127)
- [AgentDashboard.php:16-141](file://app/Livewire/Dashboard/AgentDashboard.php#L16-L141)
- [ReportsAnalytics.php:21-1012](file://app/Livewire/Dashboard/ReportsAnalytics.php#L21-L1012)
- [Ticket.php:9-64](file://app/Models/Ticket.php#L9-L64)
- [User.php:13-137](file://app/Models/User.php#L13-L137)
- [admin-dashboard.blade.php:1-406](file://resources/views/livewire/dashboard/admin-dashboard.blade.php#L1-L406)
- [agent-dashboard.blade.php:1-268](file://resources/views/livewire/dashboard/agent-dashboard.blade.php#L1-L268)
- [reports-analytics.blade.php:1-32](file://resources/views/livewire/dashboard/reports-analytics.blade.php#L1-L32)
- [header.blade.php:1-24](file://resources/views/components/dashboard/reports/header.blade.php#L1-L24)
- [tabs.blade.php:1-38](file://resources/views/components/dashboard/reports/tabs.blade.php#L1-L38)

**Section sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [Ticket.php:16-54](file://app/Models/Ticket.php#L16-L54)
- [User.php:74-97](file://app/Models/User.php#L74-L97)

## Performance Considerations
- Aggregation efficiency: ReportsAnalytics consolidates multiple KPIs into single queries and memoizes previous period calculations to avoid repeated computations.
- Chart data: Uses grouped date series and precomputed arrays to minimize client-side processing.
- Pagination: Paginates ticket listings to limit payload sizes.
- Streaming exports: Uses chunked processing for CSV exports to reduce memory usage.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
- Access denied:
  - Ensure the user’s role matches the dashboard middleware. Admins go to admin/dashboard; agents/operators go to home.
- No tickets displayed:
  - Verify date range filters and applied filters (status, priority, category, agent).
  - Confirm company context and that the user belongs to the correct company.
- Export issues:
  - Large datasets should use chunked exports; ensure sufficient memory limits and streaming support.
- Real-time updates:
  - Some UI updates rely on Livewire events; ensure browser supports event dispatching and that the page reloads when changing date presets.

**Section sources**
- [web.php:78-112](file://routes/web.php#L78-L112)
- [ReportsAnalytics.php:875-946](file://app/Livewire/Dashboard/ReportsAnalytics.php#L875-L946)
- [ReportsAnalytics.php:948-973](file://app/Livewire/Dashboard/ReportsAnalytics.php#L948-L973)

## Conclusion
The system provides a robust, role-aware dashboard and analytics platform. Administrators gain system-wide visibility, while agents focus on personal productivity and workflow. The analytics module offers flexible filtering, insightful charts, and efficient exports suitable for operational review and business intelligence.

[No sources needed since this section summarizes without analyzing specific files]

## Appendices

### Creating Custom Dashboard Widgets
- Widget pattern:
  - Create a new Livewire component extending the shared layout.
  - Define computed properties for data fetching and caching.
  - Render the widget in a Blade template and include it in the desired dashboard layout.
- Integration tips:
  - Use existing filters and date range props from ReportsAnalytics to align widget data.
  - Leverage model relationships (Ticket, User, Category) to keep queries efficient.
  - Consider pagination or chunking for large datasets.

[No sources needed since this section provides general guidance]