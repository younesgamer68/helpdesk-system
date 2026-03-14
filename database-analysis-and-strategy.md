# Database Analysis & Multi-Tenant Strategy

## Part 1: Current Database Analysis

---

## Architecture Overview

Your current system uses a **Shared Database, Shared Schema** multi-tenant approach with `company_id` columns on most tables.

### Schema Overview

| Table | Has company_id? | Status |
|-------|-----------------|--------|
| `companies` | N/A (root) | ✅ OK |
| `users` | ✅ Yes | ✅ OK |
| `tickets` | ✅ Yes | ✅ OK |
| `ticket_replies` | ✅ Yes (via ticket_id) | ✅ OK |
| `ticket_categories` | ✅ Yes | ✅ OK |
| `ticket_logs` | ✅ Yes (via ticket_id) | ✅ OK |
| `automation_rules` | ✅ Yes | ✅ OK |
| `widget_settings` | ✅ Yes | ✅ OK |
| `category_user` | ✅ Yes (via user_id) | ✅ OK |
| `saved_filter_views` | ❌ No | ❌ Missing |
| `conversations` | ❌ No | ❌ Critical - no isolation |
| `chatbot_faqs` | ❌ No | ❌ Critical - no isolation |

---

## Current Issues

### 1. Missing company_id on Critical Tables

**`conversations` table** (database/migrations/2026_03_08_041644_create_conversations_table.php):
```php
$table->id();
$table->text('user_message');
$table->text('bot_response');
// NO company_id - all conversations mixed together!
```

**`chatbot_faqs` table** (database/migrations/2026_03_08_034518_create_chatbot_faqs_table.php):
```php
$table->id();
$table->string('question');
$table->text('answer');
// NO company_id - all FAQ mixed together!
```

**Impact**: All companies share the same chatbot data. Company A's conversations and FAQs are visible to Company B - **critical data leak**.

---

### 2. Missing Compound Indexes

The current schema uses mostly single-column indexes. For efficient multi-tenant queries, you need compound indexes:

| Needed Index | Table | Purpose |
|--------------|-------|---------|
| `(company_id, status)` | tickets | Filter tickets by status per company |
| `(company_id, status, verified, updated_at)` | tickets | Automation rule queries |
| `(company_id, created_at)` | tickets, users, categories | Date range queries per company |
| `(company_id, assigned_to, status)` | tickets | Agent workload queries |

---

### 3. Enum Columns

```php
$table->enum('status', ['pending', 'open', 'in_progress', 'resolved', 'closed']);
$table->enum('priority', ['low', 'medium', 'high', 'urgent']);
$table->enum('role', ['admin', 'operator']);
```

**Problems**:
- Adding new values requires schema migration
- Not portable (MySQL enum ≠ PostgreSQL enum)
- Use PHP enums instead

---

### 4. No Composite Unique Constraints

Ticket numbers are globally unique (`ticket_number` has `unique()`), but you may want company-scoped uniqueness with a different format per company.

---

## Current Scalability Assessment

| Scale Level | Status | Notes |
|-------------|--------|-------|
| 10 companies, 1K tickets | ✅ Fine | No issues |
| 100 companies, 10K tickets | ⚠️ Warning | Missing indexes hurt |
| 1,000 companies, 100K tickets | ❌ Problems | No isolation, slow queries |
| 10,000+ companies, 1M+ tickets | ❌ Broken | Need architectural change |

### Bottlenecks at Scale

1. **Query Isolation Risk**: Every query MUST include `company_id` - easy to miss, catastrophic if forgotten
2. **No Resource Isolation**: One company's heavy query affects everyone
3. **Backup/Restore**: Cannot restore single company data without affecting others
4. **Soft Delete Accumulation**: `deleted_at` rows grow indefinitely

---

## Verdict on Current Design

| Aspect | Rating | Notes |
|--------|--------|-------|
| Design | 6/10 | Good foundation, missing tenant isolation on some tables |
| Indexing | 5/10 | Single-column only, missing compound indexes |
| Scalability | 4/10 | Needs architecture change for 1000+ companies |
| Security | 5/10 | Critical data leakage in chatbot tables |

---

## Part 2: Proposed Multi-Tenant Solution

---

## Executive Summary

For a true multi-tenant SaaS supporting 10,000+ companies, three architectural options exist:

| Strategy | Companies | Complexity | Isolation | Cost |
|----------|-----------|------------|-----------|------|
| Shared Database, Shared Schema | ~500 | Low | Poor | $ |
| Shared Database, Separate Schema | ~10,000 | Medium | Good | $$ |
| **Separate Database Per Tenant** | **Unlimited** | **High** | **Complete** | **$$$** |

---

## Recommended: Database-Per-Tenant

For a scalable SaaS, we recommend **Database-Per-Tenant** using **Spatie Laravel Multitenancy**.

### Why This Approach?

1. **Complete Data Isolation** - GDPR compliance, zero data leakage risk
2. **Independent Backups** - Restore single company without affecting others
3. **Performance Isolation** - One company's heavy query doesn't slow others
4. **Independent Scaling** - Move heavy tenants to dedicated servers
5. **Easier Migration** - Test schema changes on one company first

---

## Implementation Strategy

### Phase 1: Foundation (Weeks 1-2)

#### 1.1 Install Multitenancy Package

```bash
composer require spatie/laravel-multitenancy
php artisan vendor:publish --provider="Spatie\Multitenancy\MultitenancyServiceProvider"
```

#### 1.2 Configure Tenant Model

```php
// app/Models/Tenant.php
namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Company extends BaseTenant
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
```

#### 1.3 Create Domains Table

```php
// database/migrations/create_domains_table.php
Schema::create('domains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->string('domain')->unique(); // e.g., 'acme.helpdesk.test'
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
});
```

---

### Phase 2: Database Architecture (Weeks 2-4)

#### 2.1 Master Database Structure (Landlord)

```sql
-- helpdesk_master (landlord database)
CREATE TABLE companies (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    database_name VARCHAR(255) UNIQUE,  -- e.g., 'tenant_123'
    is_active BOOLEAN DEFAULT true,
    plan VARCHAR(50),                    -- 'free', 'pro', 'enterprise'
    created_at,
    updated_at
);

CREATE TABLE domains (
    id BIGINT PRIMARY KEY,
    company_id BIGINT REFERENCES companies(id),
    domain VARCHAR(255) UNIQUE,
    is_primary BOOLEAN DEFAULT false
);

CREATE TABLE plans (
    id BIGINT PRIMARY KEY,
    name VARCHAR(50),
    max_users INT,
    max_tickets_per_month INT,
    features JSON,
    price DECIMAL(10,2)
);

CREATE TABLE subscriptions (
    id BIGINT PRIMARY KEY,
    company_id BIGINT REFERENCES companies(id),
    plan_id BIGINT REFERENCES plans(id),
    status VARCHAR(20),  -- 'active', 'cancelled', 'past_due'
    starts_at DATETIME,
    ends_at DATETIME
);
```

#### 2.2 Tenant Database Structure

Each tenant gets identical schema - **no company_id needed**:

```sql
-- tenant_{id} databases (identical for all tenants)
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    role VARCHAR(20),
    -- NO company_id - implied by database
    created_at, updated_at, deleted_at
);

CREATE TABLE tickets (
    id BIGINT PRIMARY KEY,
    ticket_number VARCHAR(20) UNIQUE,
    customer_name, customer_email,
    status, priority,
    assigned_to, category_id,
    -- NO company_id - implied by database
    created_at, updated_at, deleted_at
);

-- All other tables without company_id
```

---

### Phase 3: Tenant Isolation (Weeks 4-6)

#### 3.1 Multitenancy Configuration

```php
// config/multitenancy.php
return [
    'tenant_model' => App\Models\Company::class,
    
    'domains_column' => 'domain',
    
    'tenant_database_connection' => 'tenant',
    
    'landlord_database_connection' => 'landlord',
    
    'switch_tenant_tasks' => [
        Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask::class,
    ],
    
    'boot_tenant_model' => true,
    
    'queues' => [
        'tenant' => 'tenant-queue',
    ],
];
```

#### 3.2 Middleware Configuration

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'tenant' => \Spatie\Multitenancy\Http\Middleware\TenantAware::class,
    ]);
})
```

#### 3.3 Database Configuration

```env
# Landlord (master) database
DB_CONNECTION=pg
DB_HOST=landlord-db
DB_DATABASE=helpdesk_master

# Tenant databases will be created automatically
TENANT_DB_CONNECTION=tenant
```

```php
// config/database.php
'tenant' => [
    'driver' => 'pgsql',
    'host' => 'tenant-db',
    'database' => 'tenant_'. TenantManager::getTenantId(),
    // ...
]
```

---

### Phase 4: Migration Path (Weeks 6-8)

#### 4.1 Immediate Fixes (Before Multitenancy)

```php
// Add missing company_id columns
Schema::table('conversations', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
});

Schema::table('chatbot_faqs', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
});

// Add compound indexes
Schema::table('tickets', function (Blueprint $table) {
    $table->index(['company_id', 'status', 'verified', 'updated_at']);
    $table->index(['company_id', 'created_at']);
});
```

#### 4.2 Data Migration Strategy

```php
// Step 1: Create tenant databases for existing companies
foreach (Company::all() as $company) {
    TenantManager::createTenantDatabase($company);
}

// Step 2: Migrate each company's data to their database
foreach (Company::all() as $company) {
    TenantManager::executeForTenant($company, function () use ($company) {
        // Import users, tickets, categories, etc.
    });
}

// Step 3: Verify data integrity
// Step 4: Switch traffic to tenant databases
// Step 5: Archive master data
```

---

## File Structure After Migration

```
helpdesk-system/
├── database/
│   ├── migrations/              # Landlord migrations (master DB)
│   │   ├── 2026_01_01_create_companies_table.php
│   │   ├── 2026_01_01_create_domains_table.php
│   │   └── 2026_01_01_create_plans_table.php
│   │
│   └── tenant_migrations/      # Tenant database migrations
│       ├── 2026_01_01_create_users_table.php
│       ├── 2026_01_01_create_tickets_table.php
│       └── ...
│
├── config/
│   └── multitenancy.php        # Multitenancy config
│
├── app/
│   ├── Models/
│   │   ├── Company.php         # Tenant model (Spatie)
│   │   ├── Domain.php
│   │   ├── Plan.php
│   │   └── User.php           # No company_id needed
│   │
│   └── Http/
│       └── Controllers/
│           └── TenantController.php  # Create tenant, assign domain
```

---

## Scalability Roadmap

| Phase | Companies | Database | Key Actions |
|-------|-----------|----------|-------------|
| 1 | 0-100 | Single + company_id | Add compound indexes, fix missing company_id |
| 2 | 100-1,000 | Single + company_id | Implement Spatie, test thoroughly |
| 3 | 1,000-10,000 | Per-Tenant DB | Database-per-tenant with connection pooling |
| 4 | 10,000+ | Sharded Tenants | Distribute across multiple database servers |

---

## Tenant Creation Flow

```
1. User signs up at helpdesk.test/register
2. Create Company record in master DB
3. Create tenant database: tenant_{id}
4. Run migrations on tenant DB
5. Add domain: {company}.helpdesk.test
6. Send verification email
7. User accesses their dashboard
```

---

## Backup Strategy

```bash
# Backup landlord (runs hourly)
pg_dump helpdesk_master > backups/landlord/$(date +%Y%m%d_%H%M%S).sql

# Backup all tenants (runs daily)
for db in $(psql -l -t | grep tenant_ | awk '{print $1}'); do
    pg_dump $db > backups/tenants/$(date +%Y%m%d)/$db.sql
done
```

---

## Monitoring Per Tenant

```php
// Track per-tenant metrics
- Ticket creation rate
- Storage usage  
- API request volume
- Database query time

// Alert on anomalies
- Spike in tickets
- Unusual query patterns
- Storage nearing limit
```

---

## Cost Comparison

| Setup | Monthly Cost (AWS) | Notes |
|-------|-------------------|-------|
| Single DB (t3.medium) | ~$50 | Up to 500 companies |
| RDS Aurora Serverless | ~$100-500 | Auto-scaling |
| Multiple RDS (per 1000) | ~$500 | Separate instances |
| Distributed (10K+) | Custom | Multi-region |

---

## Summary

| Aspect | Current | After Migration |
|--------|---------|-----------------|
| Architecture | Shared DB | Database-per-tenant |
| Isolation | Partial | Complete |
| Scalability | ~500 companies | 10,000+ companies |
| GDPR | Risk | Compliant |
| Backup | All-or-nothing | Per-tenant |
| Implementation | N/A | 8 weeks |

---

## Recommended Next Steps

1. **Immediate**: Add `company_id` to `conversations` and `chatbot_faqs` tables
2. **Immediate**: Add compound indexes for tenant queries
3. **Week 1-2**: Install and configure Spatie Laravel Multitenancy
4. **Week 2-4**: Set up landlord/tenant database architecture
5. **Week 4-6**: Implement tenant switching and middleware
6. **Week 6-8**: Migrate existing data and test thoroughly
7. **Go Live**: Deploy with database-per-tenant architecture
