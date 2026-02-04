# Database Schema Documentation

Complete database schema for the Bulk SMS/WhatsApp Application.

---

## 📊 Overview

- **Database Engine**: PostgreSQL (recommended) or MySQL 8+
- **Multi-Tenancy**: Single database with `tenant_id` scoping
- **Naming Convention**: Snake_case for tables and columns
- **Timestamps**: All tables include `created_at` and `updated_at`
- **Soft Deletes**: Applied where appropriate (marked with 🗑)

---

## 📋 Table of Contents

1. [Core Tables](#core-tables)
2. [Contact Management](#contact-management)
3. [Messaging System](#messaging-system)
4. [Billing & Credits](#billing--credits)
5. [Analytics & Logs](#analytics--logs)
6. [System Configuration](#system-configuration)
7. [Relationships Diagram](#relationships-diagram)
8. [Indexes](#indexes)
9. [Enums Reference](#enums-reference)

---

## 🏢 Core Tables

### `tenants`

Stores tenant account information.

```sql
CREATE TABLE tenants (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    plan_type VARCHAR(50) NOT NULL DEFAULT 'free', -- 'free', 'pro', 'enterprise'
    status VARCHAR(50) NOT NULL DEFAULT 'active', -- 'active', 'suspended', 'deleted'
    sms_credits INTEGER NOT NULL DEFAULT 0,
    monthly_message_count INTEGER NOT NULL DEFAULT 0, -- Reset monthly
    monthly_message_limit INTEGER,
    sender_id_limit INTEGER,
    user_limit INTEGER,
    trial_ends_at TIMESTAMP,
    settings JSONB, -- Custom tenant settings (colors, logo, etc.)
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP -- Soft delete
);

-- Indexes
CREATE INDEX idx_tenants_status ON tenants(status);
CREATE INDEX idx_tenants_plan_type ON tenants(plan_type);
CREATE INDEX idx_tenants_slug ON tenants(slug);
```

**Columns:**
- `plan_type`: Subscription tier (see Enums)
- `status`: Account status
- `sms_credits`: Available message credits
- `monthly_message_count`: Messages sent this month (resets on 1st)
- `settings`: JSONB for app customization (theme colors, logo URL, etc.)

---

### `users`

All users (super admin, tenant admins, regular users).

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id) ON DELETE CASCADE, -- NULL for super admin
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user', -- 'super_admin', 'tenant_admin', 'user'
    status VARCHAR(50) NOT NULL DEFAULT 'active', -- 'active', 'disabled'
    is_account_creator BOOLEAN NOT NULL DEFAULT false,
    can_topup_credits BOOLEAN NOT NULL DEFAULT false,
    can_view_billing BOOLEAN NOT NULL DEFAULT false,
    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,
    two_factor_confirmed_at TIMESTAMP,
    last_login_at TIMESTAMP,
    last_login_ip VARCHAR(45),
    remember_token VARCHAR(100),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_users_tenant_id ON users(tenant_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_tenant_role ON users(tenant_id, role);
```

**Columns:**
- `tenant_id`: NULL for super admin, links to tenant for others
- `role`: User role (see Enums)
- `is_account_creator`: Prevents deletion/demotion
- `can_topup_credits`, `can_view_billing`: Permissions granted by admin

---

## 👥 Contact Management

### `groups`

Contact groups for organizing recipients.

```sql
CREATE TABLE groups (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    contact_count INTEGER NOT NULL DEFAULT 0, -- Cached count
    created_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP, -- Soft delete 🗑
    
    UNIQUE(tenant_id, name) -- Group names unique within tenant
);

-- Indexes
CREATE INDEX idx_groups_tenant_id ON groups(tenant_id);
CREATE INDEX idx_groups_created_by ON groups(created_by_user_id);
```

---

### `contacts`

Individual contact records.

```sql
CREATE TABLE contacts (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    group_id BIGINT REFERENCES groups(id) ON DELETE SET NULL, -- Optional group
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    country_code VARCHAR(5), -- Extracted from phone (e.g., 'GH', 'US')
    metadata JSONB, -- Custom fields (birthday, company, etc.)
    is_unsubscribed BOOLEAN NOT NULL DEFAULT false,
    unsubscribed_at TIMESTAMP,
    created_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP, -- Soft delete 🗑
    
    UNIQUE(tenant_id, phone) -- Phone unique within tenant
);

-- Indexes
CREATE INDEX idx_contacts_tenant_id ON contacts(tenant_id);
CREATE INDEX idx_contacts_group_id ON contacts(group_id);
CREATE INDEX idx_contacts_phone ON contacts(phone);
CREATE INDEX idx_contacts_country_code ON contacts(country_code);
CREATE INDEX idx_contacts_tenant_phone ON contacts(tenant_id, phone);
CREATE INDEX idx_contacts_unsubscribed ON contacts(is_unsubscribed);
```

**Columns:**
- `group_id`: Optional - contacts can exist without a group
- `country_code`: For routing to correct SMS provider
- `is_unsubscribed`: GDPR compliance - don't send to unsubscribed contacts

---

## 📨 Messaging System

### `sender_ids`

Approved sender IDs for outgoing messages.

```sql
CREATE TABLE sender_ids (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    sender_name VARCHAR(11) NOT NULL, -- Max 11 chars for SMS
    status VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    rejection_reason TEXT,
    created_by_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    approved_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    approved_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(tenant_id, sender_name)
);

-- Indexes
CREATE INDEX idx_sender_ids_tenant_id ON sender_ids(tenant_id);
CREATE INDEX idx_sender_ids_status ON sender_ids(status);
CREATE INDEX idx_sender_ids_created_by ON sender_ids(created_by_user_id);
```

**Columns:**
- `status`: Approval workflow state
- `created_by_user_id`: Track who created it (for deletion permissions)

---

### `message_templates`

Reusable message templates with placeholders.

```sql
CREATE TABLE message_templates (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    placeholders JSONB, -- List of available placeholders
    usage_count INTEGER NOT NULL DEFAULT 0, -- Track popularity
    created_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP, -- Soft delete 🗑
    
    UNIQUE(tenant_id, name)
);

-- Indexes
CREATE INDEX idx_templates_tenant_id ON message_templates(tenant_id);
CREATE INDEX idx_templates_usage_count ON message_templates(usage_count DESC);
```

**Example:**
```json
{
  "name": "Order Confirmation",
  "content": "Hi {{customer_name}}, your order from {{business_name}} is confirmed!",
  "placeholders": ["customer_name", "business_name"]
}
```

---

### `messages`

Parent table for sent messages.

```sql
CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    sender_id BIGINT NOT NULL REFERENCES sender_ids(id),
    template_id BIGINT REFERENCES message_templates(id) ON DELETE SET NULL,
    channel VARCHAR(50) NOT NULL, -- 'sms', 'whatsapp', 'both'
    recipient_type VARCHAR(50) NOT NULL, -- 'individual', 'group', 'all_groups'
    content TEXT NOT NULL, -- Final message content
    recipient_count INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'queued', -- 'queued', 'sending', 'sent', 'failed'
    scheduled_at TIMESTAMP, -- For scheduled messages
    sent_at TIMESTAMP,
    credits_used INTEGER NOT NULL DEFAULT 0,
    created_by_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_messages_tenant_id ON messages(tenant_id);
CREATE INDEX idx_messages_status ON messages(status);
CREATE INDEX idx_messages_sent_at ON messages(sent_at);
CREATE INDEX idx_messages_scheduled_at ON messages(scheduled_at);
CREATE INDEX idx_messages_channel ON messages(channel);
CREATE INDEX idx_messages_tenant_status ON messages(tenant_id, status);
CREATE INDEX idx_messages_created_by ON messages(created_by_user_id);
```

**Columns:**
- `channel`: Delivery method
- `recipient_type`: How recipients were selected
- `scheduled_at`: For future/recurring messages

---

### `message_recipients`

Individual delivery records per recipient.

```sql
CREATE TABLE message_recipients (
    id BIGSERIAL PRIMARY KEY,
    message_id BIGINT NOT NULL REFERENCES messages(id) ON DELETE CASCADE,
    contact_id BIGINT REFERENCES contacts(id) ON DELETE SET NULL,
    phone VARCHAR(20) NOT NULL, -- Denormalized for quick access
    name VARCHAR(255),
    status VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'sent', 'delivered', 'read', 'failed', 'bounced'
    external_id VARCHAR(255), -- Provider's message ID (for tracking)
    error_message TEXT,
    provider VARCHAR(50), -- Which provider sent this (twilio, hubtel, etc.)
    cost_credits INTEGER NOT NULL DEFAULT 1,
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    read_at TIMESTAMP,
    failed_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_recipients_message_id ON message_recipients(message_id);
CREATE INDEX idx_recipients_contact_id ON message_recipients(contact_id);
CREATE INDEX idx_recipients_status ON message_recipients(status);
CREATE INDEX idx_recipients_external_id ON message_recipients(external_id);
CREATE INDEX idx_recipients_phone ON message_recipients(phone);
```

**Columns:**
- `external_id`: Provider's tracking ID (for webhook updates)
- `provider`: Track which SMS provider was used
- Timestamps for delivery funnel: sent → delivered → read

---

### `scheduled_messages`

Recurring message configuration.

```sql
CREATE TABLE scheduled_messages (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    sender_id BIGINT NOT NULL REFERENCES sender_ids(id),
    template_id BIGINT REFERENCES message_templates(id),
    channel VARCHAR(50) NOT NULL,
    recipient_type VARCHAR(50) NOT NULL,
    recipient_groups JSONB, -- Array of group IDs
    recipient_contacts JSONB, -- Array of contact IDs
    content TEXT NOT NULL,
    frequency VARCHAR(50) NOT NULL, -- 'once', 'daily', 'weekly', 'monthly'
    schedule_config JSONB, -- Cron-like config (time, day, etc.)
    next_run_at TIMESTAMP NOT NULL,
    last_run_at TIMESTAMP,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_by_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_scheduled_messages_tenant_id ON scheduled_messages(tenant_id);
CREATE INDEX idx_scheduled_messages_next_run ON scheduled_messages(next_run_at);
CREATE INDEX idx_scheduled_messages_active ON scheduled_messages(is_active);
```

**Example schedule_config:**
```json
{
  "time": "08:00",
  "timezone": "Africa/Accra",
  "days_of_week": [1, 3, 5], // Monday, Wednesday, Friday
  "end_date": "2026-12-31"
}
```

---

## 💳 Billing & Credits

### `credit_transactions`

Credit purchase and usage history.

```sql
CREATE TABLE credit_transactions (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'purchase', 'usage', 'refund', 'bonus'
    amount INTEGER NOT NULL, -- Positive for purchase/bonus, negative for usage
    balance_after INTEGER NOT NULL,
    description TEXT,
    message_id BIGINT REFERENCES messages(id), -- Link to message if usage
    payment_id BIGINT REFERENCES payments(id), -- Link to payment if purchase
    created_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_credit_transactions_tenant_id ON credit_transactions(tenant_id);
CREATE INDEX idx_credit_transactions_type ON credit_transactions(type);
CREATE INDEX idx_credit_transactions_created_at ON credit_transactions(created_at);
```

---

### `payments`

Payment records from payment gateways.

```sql
CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    gateway VARCHAR(50) NOT NULL, -- 'stripe', 'paystack', 'flutterwave'
    external_id VARCHAR(255) NOT NULL, -- Payment ID from gateway
    amount_cents INTEGER NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    credits_purchased INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL, -- 'pending', 'completed', 'failed', 'refunded'
    metadata JSONB, -- Gateway-specific data
    paid_at TIMESTAMP,
    refunded_at TIMESTAMP,
    created_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(gateway, external_id)
);

-- Indexes
CREATE INDEX idx_payments_tenant_id ON payments(tenant_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_external_id ON payments(external_id);
CREATE INDEX idx_payments_paid_at ON payments(paid_at);
```

---

## 📊 Analytics & Logs

### `analytics_daily`

Daily aggregated analytics (for performance).

```sql
CREATE TABLE analytics_daily (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    messages_sent INTEGER NOT NULL DEFAULT 0,
    messages_delivered INTEGER NOT NULL DEFAULT 0,
    messages_failed INTEGER NOT NULL DEFAULT 0,
    messages_read INTEGER NOT NULL DEFAULT 0,
    sms_sent INTEGER NOT NULL DEFAULT 0,
    whatsapp_sent INTEGER NOT NULL DEFAULT 0,
    credits_used INTEGER NOT NULL DEFAULT 0,
    revenue_cents INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(tenant_id, date)
);

-- Indexes
CREATE INDEX idx_analytics_tenant_date ON analytics_daily(tenant_id, date);
CREATE INDEX idx_analytics_date ON analytics_daily(date);
```

---

### `audit_logs`

System audit trail (who did what, when).

```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL, -- 'user.created', 'message.sent', etc.
    entity_type VARCHAR(100), -- 'User', 'Message', etc.
    entity_id BIGINT,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_audit_logs_tenant_id ON audit_logs(tenant_id);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
CREATE INDEX idx_audit_logs_entity ON audit_logs(entity_type, entity_id);
```

---

## ⚙️ System Configuration

### `settings`

Global and tenant-specific settings.

```sql
CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id) ON DELETE CASCADE, -- NULL for global settings
    key VARCHAR(255) NOT NULL,
    value TEXT,
    type VARCHAR(50) NOT NULL DEFAULT 'string', -- 'string', 'integer', 'boolean', 'json'
    is_public BOOLEAN NOT NULL DEFAULT false, -- Can be exposed to frontend
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(tenant_id, key)
);

-- Indexes
CREATE INDEX idx_settings_tenant_id ON settings(tenant_id);
CREATE INDEX idx_settings_key ON settings(key);
CREATE INDEX idx_settings_is_public ON settings(is_public);
```

**Example settings:**
- `app.theme_color` (tenant-specific)
- `app.logo_url` (tenant-specific)
- `system.maintenance_mode` (global)

---

### `password_reset_tokens`

Laravel's password reset tokens.

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP
);

CREATE INDEX idx_password_reset_email ON password_reset_tokens(email);
```

---

### `sessions`

User sessions (if using database driver).

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);
```

---

### `jobs`

Queue jobs (if using database queue).

```sql
CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX idx_jobs_queue ON jobs(queue);
```

---

### `failed_jobs`

Failed queue jobs for debugging.

```sql
CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_failed_jobs_uuid ON failed_jobs(uuid);
```

---

## 🔗 Relationships Diagram

```
tenants
  ├─ users (1:N)
  ├─ groups (1:N)
  ├─ contacts (1:N)
  ├─ sender_ids (1:N)
  ├─ message_templates (1:N)
  ├─ messages (1:N)
  ├─ credit_transactions (1:N)
  ├─ payments (1:N)
  └─ analytics_daily (1:N)

users
  ├─ tenant (N:1) [nullable for super admin]
  ├─ created_groups (1:N)
  ├─ created_contacts (1:N)
  ├─ created_sender_ids (1:N)
  ├─ created_messages (1:N)
  └─ audit_logs (1:N)

groups
  ├─ tenant (N:1)
  ├─ contacts (1:N) [optional]
  └─ created_by (N:1 to users)

contacts
  ├─ tenant (N:1)
  ├─ group (N:1) [optional]
  ├─ message_recipients (1:N)
  └─ created_by (N:1 to users)

messages
  ├─ tenant (N:1)
  ├─ sender_id (N:1)
  ├─ template (N:1) [optional]
  ├─ recipients (1:N)
  ├─ created_by (N:1 to users)
  └─ credit_transaction (1:1)

message_recipients
  ├─ message (N:1)
  └─ contact (N:1) [optional]
```

---

## 🔍 Indexes

### Composite Indexes for Common Queries

```sql
-- Finding messages by tenant and status
CREATE INDEX idx_messages_tenant_status ON messages(tenant_id, status);

-- Analytics queries by tenant and date range
CREATE INDEX idx_analytics_tenant_date ON analytics_daily(tenant_id, date);

-- Scheduled message processing
CREATE INDEX idx_scheduled_active_next_run 
    ON scheduled_messages(is_active, next_run_at) 
    WHERE is_active = true;

-- Webhook lookups by external ID
CREATE INDEX idx_recipients_external_id ON message_recipients(external_id);

-- Contact search by phone within tenant
CREATE INDEX idx_contacts_tenant_phone ON contacts(tenant_id, phone);

-- User lookup by tenant and role
CREATE INDEX idx_users_tenant_role ON users(tenant_id, role);

-- Credit transaction history
CREATE INDEX idx_credit_transactions_tenant_date 
    ON credit_transactions(tenant_id, created_at DESC);
```

---

## 📝 Enums Reference

### UserRole (app/Enums/UserRole.php)

```php
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case TENANT_ADMIN = 'tenant_admin';
    case USER = 'user';
}
```

### UserStatus (app/Enums/UserStatus.php)

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
}
```

### TenantStatus (app/Enums/TenantStatus.php)

```php
enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DELETED = 'deleted';
}
```

### PlanType (app/Enums/PlanType.php)

```php
enum PlanType: string
{
    case FREE = 'free';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';
}
```

### MessageChannel (app/Enums/MessageChannel.php)

```php
enum MessageChannel: string
{
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';
    case BOTH = 'both';
}
```

### MessageStatus (app/Enums/MessageStatus.php)

```php
enum MessageStatus: string
{
    case QUEUED = 'queued';
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
```

### RecipientStatus (app/Enums/RecipientStatus.php)

```php
enum RecipientStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';
    case BOUNCED = 'bounced';
}
```

### SenderIdStatus (app/Enums/SenderIdStatus.php)

```php
enum SenderIdStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

### PaymentStatus (app/Enums/PaymentStatus.php)

```php
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
```

---

## 🛠 Migration Example

```php
// database/migrations/YYYY_MM_DD_create_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('sender_ids');
            $table->foreignId('template_id')->nullable()->constrained('message_templates');
            $table->string('channel'); // Use string, not enum!
            $table->string('recipient_type');
            $table->text('content');
            $table->integer('recipient_count')->default(0);
            $table->string('status')->default('queued');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('credits_used')->default(0);
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

---

## 📌 Notes

1. **Foreign Key Constraints**: All foreign keys use `ON DELETE CASCADE` or `ON DELETE SET NULL` appropriately
2. **Soft Deletes**: Tables marked with 🗑 use soft deletes (`deleted_at` column)
3. **JSONB**: PostgreSQL's JSONB type is used for flexible data storage (use JSON for MySQL)
4. **Timestamps**: All tables have `created_at` and `updated_at` managed by Laravel
5. **Indexes**: Composite indexes optimize common query patterns
6. **Unique Constraints**: Enforce data integrity (e.g., unique phone per tenant)

---

**Schema Version**: 1.0  
**Last Updated**: 2026-01-23