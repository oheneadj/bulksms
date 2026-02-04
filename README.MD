# Bulk SMS/WhatsApp Application

A multi-tenant SaaS platform for sending bulk SMS and WhatsApp messages with advanced features including contact management, message templates, analytics, and multi-channel messaging.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [Deployment](#deployment)
- [Project Structure](#project-structure)
- [Additional Documentation](#additional-documentation)

---

## ✨ Features

### Multi-Tenant Architecture
- Separate tenant accounts with isolated data
- Multiple users per tenant with role-based permissions
- Super admin panel for platform management

### User Roles & Permissions
- **Super Admin**: Platform owner with complete control
- **Tenant Admin**: Account owner with full tenant access
- **Regular User**: Limited permissions, configurable by admin

### Messaging Features
- Send via SMS, WhatsApp, or both channels simultaneously
- Three sending modes: Individual, Specific Groups, All Groups
- Message templates with dynamic placeholders
- Scheduled and recurring messages
- Message analytics and delivery tracking

### Contact Management
- Import contacts via CSV
- Organize contacts into groups
- Contacts can exist independently or within groups

### Sender ID Management
- Multiple sender IDs per tenant (plan-based limits)
- Users can create and delete their own sender IDs only
- Approval workflow for sender IDs

### Credits & Billing
- Pay-per-message credit system
- Multiple payment gateways (Stripe, Paystack, Flutterwave)
- Configurable permissions for users to top-up and view billing

### Analytics Dashboard
- Message delivery rates
- Read rates and click-through rates
- Bounce rate tracking
- Revenue metrics
- Template usage statistics

### Regional Routing
- Automatic service provider selection based on recipient country
- Support for Ghana (Hubtel/Mnotify), Europe, and international routes

### Compliance & Security
- GDPR-compliant data handling
- Cookie consent management
- Two-factor authentication
- Audit logging
- Data export and deletion requests

---

## 🛠 Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Livewire 3** - Full-stack framework for dynamic interfaces
- **Filament 4** - Admin panel (Super Admin only)
- **Alpine.js** - Lightweight JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework

### Database
- **PostgreSQL** (recommended) or MySQL
- Single database with `tenant_id` scoping

### Queue & Cache
- **Redis** (production) / Database (development)
- Background job processing for message sending

### Testing
- **Pest PHP** - Testing framework
- Feature tests, unit tests, browser tests

### Third-Party Services
- **SMS Providers**: Twilio, Hubtel, Mnotify, MessageBird
- **WhatsApp**: Twilio WhatsApp Business API
- **Payments**: Stripe, Paystack, Flutterwave
- **Email**: Any SMTP provider or services like Mailgun, SendGrid

---

## 💻 System Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and NPM
- PostgreSQL 14+ or MySQL 8+
- Redis 6+ (for production)
- Git

---

## 🚀 Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd bulk-sms-app
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your settings:

```env
# See .env.example for complete configuration
APP_URL=http://localhost
DB_CONNECTION=pgsql
DB_DATABASE=bulk_sms_app
# ... (see Configuration section)
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database (creates super admin + sample data)
php artisan db:seed
```

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Application

```bash
# Start Laravel server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# In another terminal, start scheduler (for cron jobs)
php artisan schedule:work
```

Visit: `http://bulksms.test`

---

## ⚙️ Configuration

### Super Admin Login

After seeding, default super admin credentials:

```
Email: admin@example.com
Password: password
URL: /admin
```

**⚠️ IMPORTANT:** Change these credentials immediately after first login!

### Environment Variables

See `.env.example` for complete list. Key variables:

#### Application
```env
APP_NAME="Bulk SMS Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

#### Database
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bulk_sms_app
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Queue & Cache
```env
# Development
QUEUE_CONNECTION=database
CACHE_DRIVER=file

# Production
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### SMS Providers
```env
# Twilio
SMS_TWILIO_ENABLED=true
SMS_TWILIO_SID=your_account_sid
SMS_TWILIO_AUTH_TOKEN=your_auth_token
SMS_TWILIO_FROM=your_twilio_number

# Hubtel (Ghana)
SMS_HUBTEL_ENABLED=true
SMS_HUBTEL_CLIENT_ID=your_client_id
SMS_HUBTEL_CLIENT_SECRET=your_client_secret
SMS_HUBTEL_FROM=your_sender_id

# MessageBird (International)
SMS_MESSAGEBIRD_ENABLED=false
SMS_MESSAGEBIRD_API_KEY=your_api_key
SMS_MESSAGEBIRD_FROM=your_sender_id
```

#### WhatsApp
```env
WHATSAPP_PROVIDER=twilio
WHATSAPP_TWILIO_SID=your_account_sid
WHATSAPP_TWILIO_AUTH_TOKEN=your_auth_token
WHATSAPP_TWILIO_FROM=whatsapp:+14155238886
```

#### Payment Gateways
```env
# Stripe
STRIPE_ENABLED=true
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Paystack
PAYSTACK_ENABLED=true
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...

# Flutterwave
FLUTTERWAVE_ENABLED=false
FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_TEST-...
FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-...
```

---

## 🗄 Database Setup

### Migrations

Run migrations to create all tables:

```bash
php artisan migrate
```

### Seeders

Seed the database with initial data:

```bash
# Seed everything (super admin + sample data)
php artisan db:seed

# Or seed specific seeders
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=TenantSeeder
```

### Database Schema

See `DATABASE_SCHEMA.md` for complete schema documentation.

**Key Tables:**
- `tenants` - Tenant accounts
- `users` - All users (super admin, tenant admins, regular users)
- `contacts` - Contact records
- `groups` - Contact groups
- `sender_ids` - Sender ID configurations
- `message_templates` - Reusable message templates
- `messages` - Sent messages
- `message_recipients` - Individual message delivery records

---

## 🏃 Running the Application

### Development

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite dev server (hot reload)
npm run dev

# Terminal 3: Queue worker
php artisan queue:work --tries=3

# Terminal 4: Scheduler (for scheduled messages)
php artisan schedule:work
```

### Production

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run queue worker as daemon
php artisan queue:work --daemon --tries=3 --timeout=90

# Setup cron job for scheduler
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Testing

### Run All Tests

```bash
# Run Pest test suite
php artisan test

# Or use Pest directly
./vendor/bin/pest
```

### Run Specific Tests

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/MessageSendingTest.php

# With coverage
php artisan test --coverage
```

### Browser Tests (Dusk)

```bash
# Install Dusk
php artisan dusk:install

# Run browser tests
php artisan dusk
```

See `TESTING_GUIDE.md` for comprehensive testing documentation.

---

## 📦 Deployment

### Pre-Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up Redis for queue and cache
- [ ] Configure production SMS/WhatsApp providers
- [ ] Set up payment gateway webhooks
- [ ] Configure email service
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure backup strategy
- [ ] Set up monitoring (error tracking, uptime)
- [ ] Configure CORS if using API

### Deployment Steps

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart queue workers
php artisan queue:restart

# 6. Link storage
php artisan storage:link
```

See `DEPLOYMENT.md` for detailed deployment guide.

---

## 📁 Project Structure

```
bulk-sms-app/
├── app/
│   ├── Enums/              # PHP Enums (UserRole, MessageStatus, etc.)
│   ├── Filament/           # Filament admin panel resources
│   ├── Http/
│   │   ├── Controllers/    # HTTP Controllers
│   │   ├── Livewire/       # Livewire components
│   │   └── Middleware/     # Custom middleware
│   ├── Models/             # Eloquent models
│   ├── Observers/          # Model observers
│   ├── Policies/           # Authorization policies
│   ├── Providers/          # Service providers
│   ├── Services/           # Business logic services
│   │   ├── MessageService.php
│   │   ├── PaymentService.php
│   │   └── SMS/
│   │       ├── TwilioProvider.php
│   │       ├── HubtelProvider.php
│   │       └── MessageBirdProvider.php
│   └── Traits/             # Reusable traits (TenantScoped, etc.)
├── config/                 # Configuration files
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── views/              # Blade templates
│       ├── livewire/       # Livewire component views
│       ├── layouts/        # Layout files
│       └── pages/          # Static pages
├── routes/
│   ├── web.php             # Web routes
│   ├── api.php             # API routes (if needed)
│   └── console.php         # Artisan commands
├── tests/
│   ├── Feature/            # Feature tests
│   ├── Unit/               # Unit tests
│   └── Browser/            # Dusk browser tests
├── .env.example            # Environment template
├── CASCADE_RULES.md        # Development rules
├── DATABASE_SCHEMA.md      # Database documentation
├── DEPLOYMENT.md           # Deployment guide
└── TESTING_GUIDE.md        # Testing documentation
```

---

## 📚 Additional Documentation

- **CASCADE_RULES.md** - Development best practices and coding standards
- **DATABASE_SCHEMA.md** - Complete database schema and relationships
- **TESTING_GUIDE.md** - Comprehensive testing guide
- **DEPLOYMENT.md** - Production deployment checklist
- **API_DOCUMENTATION.md** - API endpoints (if applicable)

---

## 🔐 Security

- All user passwords are hashed using bcrypt
- Two-factor authentication via Laravel Fortify
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade templating
- Rate limiting on API endpoints
- Tenant data isolation enforced at database level
- Webhook signature verification for payment and delivery callbacks

---

## 🤝 Support

For issues and questions:
- Check documentation in `README.MD`, `REQUIREMENTS.md`, `USER_STORIES.MD` `DATABASE_SCHEMA.md` fiels and  .agent folder
- Review `.cursorrules` for development guidelines
- Contact: support@yourapp.com

---

## 📄 License
                                                          
[Your License Here]

---

**Built with ❤️ using Laravel, Livewire, and Filament**