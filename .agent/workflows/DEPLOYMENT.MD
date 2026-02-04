# Deployment Guide

Complete production deployment guide for the Bulk SMS/WhatsApp Application.

---

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Deployment Options](#deployment-options)
4. [Step-by-Step Deployment](#step-by-step-deployment)
5. [Post-Deployment](#post-deployment)
6. [Monitoring & Maintenance](#monitoring--maintenance)
7. [Troubleshooting](#troubleshooting)

---

## ✅ Pre-Deployment Checklist

### Code Preparation

- [ ] All tests passing (`php artisan test`)
- [ ] Code coverage meets minimum threshold (80%+)
- [ ] No debug code or console.logs remaining
- [ ] All environment variables documented in `.env.example`
- [ ] Database migrations tested and reversible
- [ ] Assets built for production (`npm run build`)
- [ ] Security audit completed (`composer audit`)

### Configuration

- [ ] `APP_ENV=production` in `.env`
- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_URL` set to production domain
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials configured
- [ ] Redis configured for cache and queue
- [ ] Mail provider configured (SMTP/Mailgun/etc.)
- [ ] SMS/WhatsApp provider credentials set
- [ ] Payment gateway credentials configured
- [ ] Sentry or error tracking configured

### Security

- [ ] SSL certificate installed (HTTPS)
- [ ] Firewall rules configured
- [ ] Database port not exposed publicly
- [ ] Redis password set
- [ ] Strong passwords for all services
- [ ] SSH key-based authentication enabled
- [ ] Fail2ban installed and configured
- [ ] Security headers configured (CSP, HSTS, etc.)

### Infrastructure

- [ ] Production server provisioned
- [ ] Database server ready (or managed service)
- [ ] Redis server ready (or managed service)
- [ ] Backup strategy in place
- [ ] Monitoring tools configured
- [ ] CDN configured (optional but recommended)
- [ ] Email delivery service ready

---

## 💻 Server Requirements

### Minimum Specifications

**For Small Scale (< 10,000 messages/day):**
- CPU: 2 cores
- RAM: 4GB
- Storage: 50GB SSD
- Bandwidth: 100GB/month

**For Medium Scale (10,000 - 100,000 messages/day):**
- CPU: 4 cores
- RAM: 8GB
- Storage: 100GB SSD
- Bandwidth: 500GB/month

**For Large Scale (> 100,000 messages/day):**
- CPU: 8+ cores
- RAM: 16GB+
- Storage: 250GB+ SSD
- Bandwidth: 1TB+/month
- Consider horizontal scaling

### Software Requirements

- **OS**: Ubuntu 22.04 LTS (recommended) or similar Linux distribution
- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **Database**: PostgreSQL 14+ or MySQL 8+
- **Redis**: 6.x or higher
- **Nginx**: Latest stable or Apache 2.4+
- **Supervisor**: For queue workers

---

## 🚀 Deployment Options

### Option 1: Laravel Forge (Recommended for Quick Setup)

[Laravel Forge](https://forge.laravel.com) is a server management tool specifically for Laravel applications.

**Pros:**
- One-click server provisioning
- Automated deployments from Git
- SSL certificate installation (Let's Encrypt)
- Queue worker management
- Scheduled tasks (cron) setup
- Database backups
- Excellent for teams

**Cons:**
- Monthly cost ($15-19/month + server costs)
- Less control over server configuration

**Setup:**
1. Sign up at forge.laravel.com
2. Connect your VPS provider (DigitalOcean, AWS, Linode, etc.)
3. Provision a new server
4. Create a site pointing to your Git repository
5. Set environment variables
6. Enable queue workers and scheduler
7. Deploy!

---

### Option 2: Laravel Envoyer (For Zero-Downtime Deployments)

[Laravel Envoyer](https://envoyer.io) provides zero-downtime deployments.

**Best for:**
- Production apps that can't afford downtime
- Teams needing deployment rollbacks
- Apps with health checks

---

### Option 3: Manual Deployment (Full Control)

Deploy manually on your own VPS (DigitalOcean, Linode, AWS EC2, etc.)

**Pros:**
- Full control over configuration
- Lower cost (no management fees)
- Learn infrastructure deeply

**Cons:**
- More time-consuming
- Requires sysadmin knowledge
- Manual security updates

**Recommended for:** Developers comfortable with Linux server administration.

---

### Option 4: Platform as a Service (PaaS)

**Options:**
- **Vapor** (Laravel's serverless platform on AWS)
- **Heroku**
- **Platform.sh**
- **Railway**

**Pros:**
- Very easy setup
- Auto-scaling
- Managed infrastructure

**Cons:**
- Higher cost at scale
- Less flexibility

---

## 📝 Step-by-Step Deployment (Manual)

### 1. Server Setup

#### Update System

```bash
sudo apt update && sudo apt upgrade -y
```

#### Install Required Software

```bash
# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 and extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mbstring php8.2-xml php8.2-bcmath \
    php8.2-curl php8.2-pgsql php8.2-redis php8.2-zip php8.2-gd -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Redis
sudo apt install redis-server -y

# Install Supervisor (for queue workers)
sudo apt install supervisor -y
```

---

### 2. Database Setup

```bash
# Switch to postgres user
sudo -u postgres psql

# Create database and user
CREATE DATABASE bulk_sms_app;
CREATE USER bulk_sms_user WITH PASSWORD 'strong_password_here';
GRANT ALL PRIVILEGES ON DATABASE bulk_sms_app TO bulk_sms_user;
\q
```

---

### 3. Application Deployment

#### Create Application Directory

```bash
sudo mkdir -p /var/www/bulk-sms-app
sudo chown -R $USER:www-data /var/www/bulk-sms-app
```

#### Clone Repository

```bash
cd /var/www/bulk-sms-app
git clone <your-repository-url> .
```

#### Install Dependencies

```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# JavaScript dependencies and build
npm install
npm run build
```

#### Configure Environment

```bash
cp .env.example .env
nano .env
```

Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=bulk_sms_app
DB_USERNAME=bulk_sms_user
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password

# ... other settings
```

#### Generate Application Key

```bash
php artisan key:generate
```

#### Run Migrations

```bash
php artisan migrate --force
```

#### Seed Initial Data

```bash
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=PlanSeeder
```

#### Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/bulk-sms-app/storage
sudo chown -R www-data:www-data /var/www/bulk-sms-app/bootstrap/cache
sudo chmod -R 775 /var/www/bulk-sms-app/storage
sudo chmod -R 775 /var/www/bulk-sms-app/bootstrap/cache
```

#### Create Storage Link

```bash
php artisan storage:link
```

---

### 4. Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/bulk-sms-app
```

Add configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/bulk-sms-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/bulk-sms-app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

### 5. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
```

---

### 6. Queue Worker Setup (Supervisor)

```bash
sudo nano /etc/supervisor/conf.d/bulk-sms-worker.conf
```

Add configuration:

```ini
[program:bulk-sms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bulk-sms-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/bulk-sms-app/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bulk-sms-worker:*
```

---

### 7. Scheduler (Cron)

```bash
sudo crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/bulk-sms-app && php artisan schedule:run >> /dev/null 2>&1
```

---

### 8. Firewall Configuration

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH, HTTP, HTTPS
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443

# Enable firewall
sudo ufw enable
```

---

## 🔄 Post-Deployment

### 1. Test Application

- [ ] Visit your domain in browser
- [ ] Test super admin login
- [ ] Create a test tenant
- [ ] Send a test message
- [ ] Verify email delivery works
- [ ] Check payment gateway integration
- [ ] Test file uploads

### 2. Monitor Logs

```bash
# Application logs
tail -f /var/www/bulk-sms-app/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log

# Queue worker logs
tail -f /var/www/bulk-sms-app/storage/logs/worker.log
```

### 3. Performance Optimization

#### Enable OPcache

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Set:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

#### Configure Redis Persistence

```bash
sudo nano /etc/redis/redis.conf
```

Set:

```conf
save 900 1
save 300 10
save 60 10000
requirepass your_strong_redis_password
```

Restart Redis:

```bash
sudo systemctl restart redis
```

---

## 📊 Monitoring & Maintenance

### Error Tracking (Sentry)

Install Sentry:

```bash
composer require sentry/sentry-laravel
```

Configure `.env`:

```env
SENTRY_LARAVEL_DSN=your_sentry_dsn_here
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Uptime Monitoring

**Recommended Services:**
- [UptimeRobot](https://uptimerobot.com) (Free tier available)
- [Pingdom](https://pingdom.com)
- [Better Uptime](https://betteruptime.com)

### Database Backups

#### Automated Daily Backups

```bash
sudo nano /usr/local/bin/backup-database.sh
```

Add:

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/var/backups/postgresql"
mkdir -p $BACKUP_DIR

pg_dump -U bulk_sms_user -h localhost bulk_sms_app > $BACKUP_DIR/backup_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable:

```bash
sudo chmod +x /usr/local/bin/backup-database.sh
```

Add to cron:

```bash
sudo crontab -e
```

Add:

```cron
0 2 * * * /usr/local/bin/backup-database.sh
```

### Log Rotation

```bash
sudo nano /etc/logrotate.d/laravel
```

Add:

```
/var/www/bulk-sms-app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

**Check:**
- `storage/logs/laravel.log`
- Nginx error log: `/var/log/nginx/error.log`
- File permissions on `storage/` and `bootstrap/cache/`

#### 2. Queue Jobs Not Processing

**Check:**
```bash
sudo supervisorctl status
sudo supervisorctl restart bulk-sms-worker:*
```

#### 3. Database Connection Failed

**Verify:**
- Database credentials in `.env`
- PostgreSQL is running: `sudo systemctl status postgresql`
- User has correct permissions

#### 4. Redis Connection Error

**Check:**
```bash
redis-cli ping
# Should return PONG
```

If authentication required:
```bash
redis-cli -a your_redis_password ping
```

#### 5. High Memory Usage

**Solutions:**
- Increase server RAM
- Optimize database queries (add indexes)
- Reduce queue worker count
- Enable OPcache

---

## 🔄 Updating the Application

### Zero-Downtime Deployment

```bash
cd /var/www/bulk-sms-app

# Put app in maintenance mode (optional)
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
sudo supervisorctl restart bulk-sms-worker:*

# Bring app back up
php artisan up
```

---

## 📚 Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [DigitalOcean Laravel Deployment Guide](https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-22-04)
- [Forge Documentation](https://forge.laravel.com/docs)
- [Server Monitoring Best Practices](https://www.linode.com/docs/guides/server-monitoring/)

---

**Deployment Checklist Complete** ✅

Your application should now be live and serving traffic. Monitor logs closely for the first few hours after deployment!