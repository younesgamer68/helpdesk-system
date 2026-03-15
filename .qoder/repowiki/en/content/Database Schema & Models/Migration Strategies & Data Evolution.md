# Migration Strategies & Data Evolution

<cite>
**Referenced Files in This Document**
- [0001_01_01_000000_create_users_table.php](file://database/migrations/0001_01_01_000000_create_users_table.php)
- [2026_02_01_224200_create_companies_table.php](file://database/migrations/2026_02_01_224200_create_companies_table.php)
- [2026_02_01_224218_create_ticket_categories_table.php](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php)
- [2026_02_01_224222_create_tickets_table.php](file://database/migrations/2026_02_01_224222_create_tickets_table.php)
- [2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php](file://database/migrations/2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php)
- [2026_03_07_151242_update_user_role_to_operator.php](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php)
- [2026_03_09_104729_create_automation_rules_table.php](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php)
- [Company.php](file://app/Models/Company.php)
- [AutomationRule.php](file://app/Models/AutomationRule.php)
- [IdentifyCompanyFromSubdomain.php](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php)
- [EnsureUserBelongsToCompany.php](file://app/Http/Middleware/EnsureUserBelongsToCompany.php)
- [DatabaseSeeder.php](file://database/seeders/DatabaseSeeder.php)
- [CompanyFactory.php](file://database/factories/CompanyFactory.php)
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
This document defines a comprehensive migration strategy for the Helpdesk System, covering the lifecycle from initial schema creation through ongoing modifications. It documents multi-tenant patterns using subdomain-based company isolation, data seeding strategies for development and testing, and approaches to schema changes such as column additions, type changes, and constraint updates. It also outlines rollback strategies, data preservation techniques, handling of breaking changes and backward compatibility, zero-downtime deployment considerations, and automation rule migration patterns. Guidance is included for production safety, testing, and rollback procedures.

## Project Structure
The migration system follows Laravel’s convention of placing timestamped migration files under database/migrations. The application enforces multi-tenancy via subdomain routing and middleware that bind requests to a company context. Models define relationships and casting for typed fields. Seeders and factories populate test data for local and CI environments.

```mermaid
graph TB
subgraph "Migrations"
M1["Initial Users<br/>0001_01_01_000000_create_users_table.php"]
M2["Companies<br/>2026_02_01_224200_create_companies_table.php"]
M3["Ticket Categories<br/>2026_02_01_224218_create_ticket_categories_table.php"]
M4["Tickets<br/>2026_02_01_224222_create_tickets_table.php"]
M5["Automation Rules<br/>2026_03_09_104729_create_automation_rules_table.php"]
M6["Add onboarding_completed_at<br/>2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php"]
M7["Update role enum<br/>2026_03_07_151242_update_user_role_to_operator.php"]
M8["Rename theme column<br/>2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php"]
end
subgraph "Models"
C["Company"]
AR["AutomationRule"]
end
subgraph "Middleware"
ID["IdentifyCompanyFromSubdomain"]
UC["EnsureUserBelongsToCompany"]
end
subgraph "Seeders/Factories"
DS["DatabaseSeeder"]
CF["CompanyFactory"]
end
M1 --> C
M2 --> C
M3 --> C
M4 --> C
M5 --> AR
ID --> C
UC --> C
DS --> C
DS --> AR
CF --> C
```

**Diagram sources**
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224200_create_companies_table.php:1-41](file://database/migrations/2026_02_01_224200_create_companies_table.php#L1-L41)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)
- [AutomationRule.php:1-117](file://app/Models/AutomationRule.php#L1-L117)
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [DatabaseSeeder.php:1-151](file://database/seeders/DatabaseSeeder.php#L1-L151)
- [CompanyFactory.php:1-30](file://database/factories/CompanyFactory.php#L1-L30)

**Section sources**
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224200_create_companies_table.php:1-41](file://database/migrations/2026_02_01_224200_create_companies_table.php#L1-L41)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)
- [AutomationRule.php:1-117](file://app/Models/AutomationRule.php#L1-L117)
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [DatabaseSeeder.php:1-151](file://database/seeders/DatabaseSeeder.php#L1-L151)
- [CompanyFactory.php:1-30](file://database/factories/CompanyFactory.php#L1-L30)

## Core Components
- Multi-tenant isolation via subdomain: Requests are bound to a Company instance using middleware that extracts the subdomain and attaches the company to the request.
- Company-centric schema: Core entities (users, tickets, categories, automation rules) include company_id foreign keys to enforce tenant boundaries.
- Typed JSON for automation rules: Conditions and actions are stored as JSON with model casting to arrays for safe handling.
- Seeding for development: A seeder creates a representative dataset including companies, users, categories, and tickets.

**Section sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)
- [AutomationRule.php:1-117](file://app/Models/AutomationRule.php#L1-L117)
- [DatabaseSeeder.php:1-151](file://database/seeders/DatabaseSeeder.php#L1-L151)

## Architecture Overview
The migration lifecycle is centered around timestamped migrations that evolve the schema while preserving tenant boundaries. Middleware ensures that all requests operate within a company context, and models encapsulate relationships and data typing.

```mermaid
sequenceDiagram
participant Dev as "Developer"
participant Artisan as "php artisan"
participant Migrator as "Migration Runner"
participant DB as "Database"
Dev->>Artisan : "migrate"
Artisan->>Migrator : "Run pending migrations"
Migrator->>DB : "Apply schema changes per migration"
DB-->>Migrator : "Success"
Migrator-->>Artisan : "Complete"
Artisan-->>Dev : "Migrations applied"
```

[No sources needed since this diagram shows conceptual workflow, not actual code structure]

## Detailed Component Analysis

### Multi-Tenant Subdomain Isolation
- Subdomain extraction: The middleware parses the host to extract the subdomain and resolves it to a Company record via slug.
- Request binding: The resolved Company is merged into the request and shared with views.
- Access control: A second middleware validates that authenticated users belong to the same company as the request-bound company.

```mermaid
sequenceDiagram
participant Client as "Browser"
participant MW1 as "IdentifyCompanyFromSubdomain"
participant MW2 as "EnsureUserBelongsToCompany"
participant Company as "Company Model"
participant Controller as "Controller"
Client->>MW1 : "HTTP Request"
MW1->>MW1 : "Parse subdomain"
MW1->>Company : "Find by slug"
Company-->>MW1 : "Company"
MW1->>Client : "Attach company to request"
Client->>MW2 : "Next middleware"
MW2->>MW2 : "Validate user.company_id"
MW2-->>Controller : "Proceed or abort"
```

**Diagram sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)

**Section sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)

### Initial Schema Creation
- Users table: Includes company_id, OAuth-friendly nullable password, and indexes for performance.
- Companies table: Contains company metadata, soft deletes, and composite indexing for filtered queries.
- Ticket categories and tickets: Enforce company isolation via foreign keys and include extensive indexes for common filters.
- Automation rules: JSON-backed conditions/actions with typed casting and multi-column indexes for efficient filtering.

```mermaid
erDiagram
COMPANIES {
bigint id PK
string name
string slug
string email
string phone
string logo
boolean require_client_verification
boolean deleted
timestamp timezone
timestamp onboarding_completed_at
}
USERS {
bigint id PK
bigint company_id FK
string name
string email UK
string google_id UK
string avatar
enum role
timestamp email_verified_at
timestamp remember_token
}
TICKET_CATEGORIES {
bigint id PK
bigint company_id FK
string name
text description
string color
enum default_priority
}
TICKETS {
bigint id PK
bigint company_id FK
string ticket_number
string customer_name
string customer_email
string customer_phone
string subject
text description
enum status
enum priority
bigint assigned_to FK
bigint category_id FK
boolean verified
string verification_token
timestamp resolved_at
timestamp closed_at
}
AUTOMATION_RULES {
bigint id PK
bigint company_id FK
string name
text description
enum type
json conditions
json actions
boolean is_active
int priority
int executions_count
timestamp last_executed_at
}
COMPANIES ||--o{ USERS : "hasMany"
COMPANIES ||--o{ TICKET_CATEGORIES : "hasMany"
COMPANIES ||--o{ TICKETS : "hasMany"
COMPANIES ||--o{ AUTOMATION_RULES : "hasMany"
```

**Diagram sources**
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224200_create_companies_table.php:1-41](file://database/migrations/2026_02_01_224200_create_companies_table.php#L1-L41)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)

**Section sources**
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224200_create_companies_table.php:1-41](file://database/migrations/2026_02_01_224200_create_companies_table.php#L1-L41)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)

### Schema Modifications: Column Additions, Type Changes, Constraints
- Adding nullable columns with defaults: New optional fields are introduced with after() placement and nullable() to preserve existing rows.
- Enum updates with data normalization: Existing values are migrated before altering the enum definition to avoid constraint violations.
- Column renaming with value normalization: A legacy column is renamed and existing values are normalized to the new semantic.

```mermaid
flowchart TD
Start(["Migration Start"]) --> Detect["Detect target column/state"]
Detect --> Action{"Action Required?"}
Action --> |Add nullable column| AddCol["Add column with default/null"]
Action --> |Change enum| Normalize["Normalize existing values"]
Normalize --> AlterEnum["Alter enum definition"]
Action --> |Rename column| Rename["Rename column"]
Rename --> NormalizeVals["Normalize existing values"]
AddCol --> End(["Migration Complete"])
AlterEnum --> End
NormalizeVals --> End
```

**Diagram sources**
- [2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php:1-35](file://database/migrations/2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php#L1-L35)
- [2026_03_07_151242_update_user_role_to_operator.php:1-36](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php#L1-L36)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php:1-28](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php#L1-L28)

**Section sources**
- [2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php:1-35](file://database/migrations/2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php#L1-L35)
- [2026_03_07_151242_update_user_role_to_operator.php:1-36](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php#L1-L36)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php:1-28](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php#L1-L28)

### Rollback Strategies and Data Preservation
- Rollbacks remove tables or drop/rename columns as appropriate.
- Data normalization is reversed in down() to restore previous semantics.
- Soft deletes and timestamps enable selective restoration of records when needed.

```mermaid
sequenceDiagram
participant Dev as "Developer"
participant Artisan as "php artisan"
participant Migrator as "Migration Runner"
participant DB as "Database"
Dev->>Artisan : "migrate : rollback"
Artisan->>Migrator : "Run down() for last batch"
Migrator->>DB : "Drop tables/columns, revert renames"
DB-->>Migrator : "Success"
Migrator-->>Artisan : "Complete"
Artisan-->>Dev : "Rollback applied"
```

**Diagram sources**
- [2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php:24-34](file://database/migrations/2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php#L24-L34)
- [2026_03_07_151242_update_user_role_to_operator.php:27-35](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php#L27-L35)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php:19-27](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php#L19-L27)

**Section sources**
- [2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php:24-34](file://database/migrations/2026_03_07_080820_add_onboarding_completed_at_to_companies_table.php#L24-L34)
- [2026_03_07_151242_update_user_role_to_operator.php:27-35](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php#L27-L35)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php:19-27](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php#L19-L27)

### Automation Rule Migration Patterns
- JSON-backed conditions/actions: Stored as JSON with model casting to arrays, enabling flexible rule definitions.
- Multi-column indexes: Composite indexes on company_id, is_active, type and priority support efficient filtering and ordering.
- Execution tracking: Separate counters and timestamps allow auditing and performance monitoring.

```mermaid
classDiagram
class AutomationRule {
+int id
+int company_id
+string name
+string description
+string type
+array conditions
+array actions
+bool is_active
+int priority
+int executions_count
+datetime last_executed_at
+recordExecution()
+scopeActive()
+scopeOfType()
+scopeOrdered()
}
class Company {
+int id
+string name
+string slug
+string email
+string phone
+string logo
+bool require_client_verification
+datetime onboarding_completed_at
+datetime timezone
}
AutomationRule --> Company : "belongsTo"
```

**Diagram sources**
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)
- [AutomationRule.php:1-117](file://app/Models/AutomationRule.php#L1-L117)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)

**Section sources**
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)
- [AutomationRule.php:1-117](file://app/Models/AutomationRule.php#L1-L117)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)

### Data Seeding Strategies
- Development/testing datasets: A seeder creates a realistic mix of companies, users, categories, and tickets with varied statuses and priorities.
- Factories: Factories provide default attributes and states for scalable, repeatable test data generation.
- Representative distributions: The seeder generates tickets across statuses and adds urgent tickets to simulate real-world workloads.

```mermaid
flowchart TD
SeedStart["DatabaseSeeder::run()"] --> CreateCompany["Create test company"]
CreateCompany --> CreateUsers["Create admin/operator users"]
CreateUsers --> CreateCategories["Create ticket categories"]
CreateCategories --> GenerateTickets["Generate tickets by status"]
GenerateTickets --> UrgentTickets["Add urgent tickets"]
UrgentTickets --> Summary["Report totals"]
```

**Diagram sources**
- [DatabaseSeeder.php:1-151](file://database/seeders/DatabaseSeeder.php#L1-L151)
- [CompanyFactory.php:1-30](file://database/factories/CompanyFactory.php#L1-L30)

**Section sources**
- [DatabaseSeeder.php:1-151](file://database/seeders/DatabaseSeeder.php#L1-L151)
- [CompanyFactory.php:1-30](file://database/factories/CompanyFactory.php#L1-L30)

## Dependency Analysis
- Middleware depends on the Company model to resolve tenant context.
- Models depend on Eloquent relationships and casting to maintain data integrity.
- Migrations depend on each other via foreign keys and shared tenant columns.

```mermaid
graph LR
ID["IdentifyCompanyFromSubdomain"] --> C["Company Model"]
UC["EnsureUserBelongsToCompany"] --> C
C --> TBL_USERS["Users Table"]
C --> TBL_TICKETS["Tickets Table"]
C --> TBL_CATEGORIES["Ticket Categories Table"]
C --> TBL_AUTOMATION["Automation Rules Table"]
```

**Diagram sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)

**Section sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [Company.php:1-47](file://app/Models/Company.php#L1-L47)
- [0001_01_01_000000_create_users_table.php:1-59](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L59)
- [2026_02_01_224222_create_tickets_table.php:1-62](file://database/migrations/2026_02_01_224222_create_tickets_table.php#L1-L62)
- [2026_02_01_224218_create_ticket_categories_table.php:1-33](file://database/migrations/2026_02_01_224218_create_ticket_categories_table.php#L1-L33)
- [2026_03_09_104729_create_automation_rules_table.php:1-53](file://database/migrations/2026_03_09_104729_create_automation_rules_table.php#L1-L53)

## Performance Considerations
- Indexes on foreign keys and frequently queried columns improve join and filter performance.
- Composite indexes on company_id with booleans and enums optimize rule and ticket filtering.
- JSON fields for automation rules enable flexible schemas while maintaining typed casting for safe access.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
- Subdomain resolution failures: Ensure the subdomain matches the company slug and that middleware runs before route resolution.
- Access denied errors: Confirm the authenticated user belongs to the same company as the request-bound company.
- Migration failures on enum changes: Normalize existing values before altering the enum definition.
- Rollback issues: Verify that down() removes added columns and restores renames; ensure data normalization reversals are applied.

**Section sources**
- [IdentifyCompanyFromSubdomain.php:1-54](file://app/Http/Middleware/IdentifyCompanyFromSubdomain.php#L1-L54)
- [EnsureUserBelongsToCompany.php:1-39](file://app/Http/Middleware/EnsureUserBelongsToCompany.php#L1-L39)
- [2026_03_07_151242_update_user_role_to_operator.php:1-36](file://database/migrations/2026_03_07_151242_update_user_role_to_operator.php#L1-L36)
- [2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php:1-28](file://database/migrations/2026_03_07_173715_rename_primary_color_to_theme_mode_in_widget_settings.php#L1-L28)

## Conclusion
The Helpdesk System employs a robust, multi-tenant migration strategy centered on subdomain isolation and company-scoped schemas. Migrations evolve incrementally with careful attention to data preservation, backward compatibility, and typed JSON for complex rule definitions. Middleware enforces tenant boundaries, while seeders and factories provide reliable test data. The documented patterns and procedures support safe, repeatable deployments and effective rollbacks.

## Appendices
- Best practices for zero-downtime deployments:
  - Use additive-only schema changes where possible.
  - Normalize data before altering constraints or enums.
  - Test rollbacks locally and in staging before production.
  - Prefer JSON-backed fields for evolving rule structures.
- Production safety checklist:
  - Review migration order and dependencies.
  - Validate subdomain-to-company mapping in production DNS.
  - Confirm middleware precedence and user-company alignment.
  - Back up databases before running migrations in production.

[No sources needed since this section summarizes without analyzing specific files]