# 🚀 MeetApp Kalsel - Deployment Guide

## 📋 Overview

This comprehensive guide covers the setup, deployment, and maintenance of the MeetApp Kalsel meeting management system for both developers and system administrators.

---

## 🎯 System Requirements

### Server Requirements

#### Minimum Requirements
- **PHP Version**: >= 5.6.4 (PHP 7.x recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.10+
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Memory**: 512MB RAM (1GB+ recommended)
- **Storage**: 5GB available space
- **OS**: Windows Server 2012+ (for NSSM) or Linux (Ubuntu 18.04+)

#### Recommended Production Requirements
- **PHP Version**: 7.4+ (PHP 8.x preferred)
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: 2GB+ RAM
- **Storage**: 20GB+ SSD storage
- **OS**: Windows Server 2019+ or Ubuntu 20.04+

### PHP Extensions Required
```bash
# Core Extensions
php-cli
php-mbstring
php-xml
php-curl
php-zip
php-gd
php-json

# Database Extensions
php-mysql          # For MySQL/MariaDB
php-pdo_mysql

# Optional Extensions
php-redis          # For Redis caching
php-opcache        # For performance
```

### Windows-Specific Requirements
- **NSSM (Non-Sucking Service Manager)**: For queue management
- **PHP**: Windows version with appropriate extensions
- **Task Scheduler**: For automated reminders

---

## 🛠️ Installation Guide

### Step 1: Environment Setup

#### For Windows Development
```batch
# Install XAMPP (recommended)
# Download: https://www.apachefriends.org/download.html

# Install Composer
# Download: https://getcomposer.org/download/

# Install Git (optional)
# Download: https://git-scm.com/download/win
```

#### For Linux Development
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-curl php-zip php-gd php-mysql php-pdo_mysql composer git apache2 mysql-server

# CentOS/RHEL
sudo yum install php php-cli php-mbstring php-xml php-curl php-zip php-gd php-mysql php-pdo_mysql composer git httpd mariadb-server
```

### Step 2: Application Setup

#### Clone or Download Application
```bash
# Option 1: Git Clone (if repository available)
git clone https://github.com/your-org/meetapp-kalsel.git
cd meetapp-kalsel

# Option 2: Download and Extract
# Download the application files and extract to web directory
```

#### Install Dependencies
```bash
# Install Laravel dependencies
composer install --no-dev --optimize-autoloader

# For development
composer install
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
# See .env.example section below
```

### Step 3: Database Setup

#### Create Database
```sql
-- MySQL/MariaDB
CREATE DATABASE meetappdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'meetapp_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON meetappdb.* TO 'meetapp_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Run Migrations
```bash
# Run database migrations
php artisan migrate

# If you have seeders
php artisan db:seed
```

#### Manual Database Setup (Alternative)
```bash
# Import database if you have SQL file
mysql -u meetapp_user -p meetappdb < database.sql
```

### Step 4: Directory Permissions

#### Linux/Unix Permissions
```bash
# Set proper permissions
sudo chown -R www-data:www-data /path/to/meetapp
sudo chmod -R 755 /path/to/meetapp
sudo chmod -R 777 /path/to/meetapp/storage
sudo chmod -R 777 /path/to/meetapp/bootstrap/cache
```

#### Windows Permissions
```batch
# Ensure IIS/IUSR or Apache user has write access to:
# - storage/
# - bootstrap/cache/
# - public/notulensi/
```

### Step 5: Web Server Configuration

#### Apache Configuration
```apache
# /etc/apache2/sites-available/meetapp.conf (Linux)
# or httpd-vhosts.conf (Windows)

<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot "C:/xampp/htdocs/meetapp/public"
    
    <Directory "C:/xampp/htdocs/meetapp/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/meetapp_error.log
    CustomLog ${APACHE_LOG_DIR}/meetapp_access.log combined
</VirtualHost>

# Enable site (Linux)
sudo a2ensite meetapp.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/meetapp
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/meetapp/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

---

## 🔄 Queue System Setup (NSSM)

### Windows Queue Management

#### Install NSSM
```batch
# Download NSSM
# https://nssm.cc/download

# Extract to C:\nssm\
# Add to PATH or use full path
```

#### Configure Queue Worker Service
```batch
# Install queue worker as Windows service
C:\nssm\nssm.exe install MeetAppQueue "C:\php\php.exe" "C:\xampp\htdocs\meetapp\artisan queue:work"

# Set service properties
C:\nssm\nssm.exe set MeetAppQueue AppDirectory "C:\xampp\htdocs\meetapp"
C:\nssm\nssm.exe set MeetAppQueue AppStdout "C:\xampp\htdocs\meetapp\storage\logs\queue.log"
C:\nssm\nssm.exe set MeetAppQueue AppStderr "C:\xampp\htdocs\meetapp\storage\logs\queue_error.log"
C:\nssm\nssm.exe set MeetAppQueue DisplayName "MeetApp Queue Worker"
C:\nssm\nssm.exe set MeetAppQueue Description "Laravel queue worker for WhatsApp notifications"

# Start the service
C:\nssm\nssm.exe start MeetAppQueue

# Check service status
C:\nssm\nssm.exe status MeetAppQueue
```

#### Alternative: Manual Queue Worker
```batch
# For development or testing
cd C:\xampp\htdocs\meetapp
php artisan queue:work
```

### Linux Queue Management

#### Systemd Service
```bash
# Create service file
sudo nano /etc/systemd/system/meetapp-queue.service
```

```ini
[Unit]
Description=MeetApp Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/meetapp/artisan queue:work
WorkingDirectory=/var/www/meetapp

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl daemon-reload
sudo systemctl enable meetapp-queue
sudo systemctl start meetapp-queue
sudo systemctl status meetapp-queue
```

---

## 🔧 Configuration Management

### Environment Variables

#### Production .env Configuration
```bash
# Copy and customize this template
cp .env.example .env.production
```

#### Key Configuration Areas
- **Database Connection**: MySQL credentials
- **Application URL**: Production domain
- **External Services**: Zoom and WhatsApp API settings
- **Queue Configuration**: Database driver for queues
- **Security Settings**: App key and debug mode

### External Service Configuration

#### Zoom Integration Setup
```bash
# 1. Create Zoom App
# Visit: https://marketplace.zoom.us/
# Create Server-to-Server OAuth app

# 2. Get Credentials
# - Account ID
# - Client ID
# - Client Secret

# 3. Configure Redirect URI
# Add: https://yourdomain.com/meetapp/callback-zoom

# 4. Update .env
ZOOM_CLIENT_ID1=your_client_id
ZOOM_CLIENT_SECRET1=your_client_secret
ZOOM_REDIRECT_URI=https://yourdomain.com/meetapp/callback-zoom
```

#### WhatsApp Integration Setup
```bash
# 1. Register with WaConnect
# Visit: https://app.waconnect.id/

# 2. Get API Token
# Copy token from dashboard

# 3. Update .env
WA_CONNECT_TOKEN=your_api_token_here
WA_CONNECT_URL=https://app.waconnect.id/api/send_express
```

---

## 🔒 Security Hardening

### Production Security Checklist

#### Application Security
```bash
# 1. Set production environment
APP_ENV=production
APP_DEBUG=false

# 2. Secure file permissions
chmod 600 .env
chmod 755 public/
chmod 755 storage/
chmod 755 bootstrap/cache/

# 3. Protect sensitive directories
# Add .htaccess to storage/ and bootstrap/cache/
<IfModule mod_authz_core>
    Require all denied
</IfModule>
```

#### Web Server Security
```apache
# Apache security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
</IfModule>

# Hide server signature
ServerTokens Prod
ServerSignature Off
```

#### Database Security
```sql
-- Create dedicated database user
CREATE USER 'meetapp_prod'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON meetappdb.* TO 'meetapp_prod'@'localhost';
FLUSH PRIVILEGES;

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
```

### SSL/HTTPS Configuration

#### Let's Encrypt (Linux)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### Windows SSL Configuration
```batch
# Use IIS or Apache with SSL certificates
# Consider Cloudflare for free SSL
```

---

## 📊 Monitoring & Maintenance

### Application Monitoring

#### Log Management
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue logs
tail -f storage/logs/queue.log

# Apache/Nginx logs
tail -f /var/log/apache2/access.log
tail -f /var/log/apache2/error.log
```

#### Health Checks
```bash
# Create health check endpoint
# Add to routes/web.php:
Route::get('/health', function() {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'queue' => Queue::size() . ' jobs pending'
    ]);
});
```

### Database Maintenance

#### Backup Strategy
```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u meetapp_prod -p meetappdb > /backups/meetappdb_$DATE.sql
find /backups -name "meetappdb_*.sql" -mtime +30 -delete
```

#### Database Optimization
```sql
-- Optimize tables monthly
OPTIMIZE TABLE rapat;
OPTIMIZE TABLE users;
OPTIMIZE TABLE rapat_user;
OPTIMIZE TABLE zoom;
OPTIMIZE TABLE notulensi;

-- Check table status
SHOW TABLE STATUS;
```

### Queue Maintenance

#### Queue Monitoring
```bash
# Check queue status
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear queue (careful!)
php artisan queue:clear

# Restart queue worker
php artisan queue:restart
```

#### NSSM Service Management
```batch
# Check service status
C:\nssm\nssm.exe status MeetAppQueue

# Restart service
C:\nssm\nssm.exe restart MeetAppQueue

# View logs
type "C:\xampp\htdocs\meetapp\storage\logs\queue.log"
```

---

## 🚀 Performance Optimization

### Application Optimization

#### Laravel Optimization
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize --force
```

#### PHP Configuration
```ini
; php.ini recommendations for production
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M
max_input_vars = 3000

; OPcache settings
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Database Optimization

#### Indexing Strategy
```sql
-- Add performance indexes
ALTER TABLE rapat ADD INDEX idx_date_room (tanggal_rapat_start, ruang_rapat);
ALTER TABLE rapat_user ADD INDEX idx_attendee_lookup (attendee_type, attendee_id);
ALTER TABLE users ADD INDEX idx_unit_level (unit_kerja, level);
```

#### Query Optimization
```php
// Use eager loading to prevent N+1 problems
$meetings = RapatModel::with('attendees', 'zoomMeetings')->get();

// Use database query caching
$users = Cache::remember('users.active', 3600, function () {
    return UserModel::where('is_active', 1)->get();
});
```

### Caching Strategy

#### Redis Configuration (Optional)
```bash
# Install Redis
sudo apt install redis-server

# Configure Laravel
# Update .env:
CACHE_DRIVER=redis
QUEUE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🔄 Backup & Recovery

### Automated Backup Setup

#### Linux Backup Script
```bash
#!/bin/bash
# /home/user/scripts/backup_meetapp.sh

BACKUP_DIR="/backups/meetapp"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/meetapp"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u meetapp_prod -p meetappdb > $BACKUP_DIR/db_$DATE.sql

# Application files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $APP_DIR storage/app public/notulensi

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

#### Windows Backup Script
```batch
@echo off
REM backup_meetapp.bat

SET BACKUP_DIR=C:\backups\meetapp
SET DATE=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%
SET APP_DIR=C:\xampp\htdocs\meetapp

REM Create backup directory
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Database backup
mysqldump -u meetapp_prod -p meetappdb > "%BACKUP_DIR%\db_%DATE%.sql"

REM Application files backup
tar -czf "%BACKUP_DIR%\files_%DATE%.tar.gz" -C "%APP_DIR%" storage/app public/notulensi

echo Backup completed: %DATE%
```

### Recovery Procedures

#### Database Recovery
```bash
# Restore database from backup
mysql -u meetapp_prod -p meetappdb < /backups/meetapp/db_20240115_120000.sql

# Verify restore
php artisan tinker
>>> DB::table('rapat')->count();
```

#### File Recovery
```bash
# Restore application files
tar -xzf /backups/meetapp/files_20240115_120000.tar.gz -C /var/www/meetapp/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/meetapp/storage
sudo chmod -R 755 /var/www/meetapp/storage
```

---

## 🐛 Troubleshooting Guide

### Common Issues & Solutions

#### Application Not Loading
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/apache2/error.log

# Verify permissions
ls -la storage/
ls -la bootstrap/cache/

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Queue Not Processing
```bash
# Check queue service status
sudo systemctl status meetapp-queue
# or Windows:
C:\nssm\nssm.exe status MeetAppQueue

# Check queue table
mysql -u meetapp_prod -p -e "SELECT * FROM jobs LIMIT 5;"

# Restart queue worker
php artisan queue:restart
```

#### WhatsApp Notifications Not Sending
```bash
# Check WhatsApp token
grep WA_CONNECT_TOKEN .env

# Test WhatsApp job
php artisan tinker
>>> dispatch(new App\Jobs\NotifWa('08123456789', 'Test message'));

# Check schedule_log table
mysql -u meetapp_prod -p -e "SELECT * FROM schedule_log ORDER BY created_at DESC LIMIT 5;"
```

#### Zoom Integration Issues
```bash
# Check Zoom credentials
grep ZOOM_CLIENT_ID1 .env
grep ZOOM_CLIENT_SECRET1 .env

# Test OAuth flow
curl -X POST "https://zoom.us/oauth/token" \
  -d "grant_type=client_credentials" \
  -u "CLIENT_ID:CLIENT_SECRET"

# Check stored tokens
cat storage/app/zoom_credentials1.json
```

#### Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database credentials
mysql -u meetapp_prod -p meetappdb -e "SELECT 1;"

# Verify database exists
mysql -u meetapp_prod -p -e "SHOW DATABASES;"
```

### Performance Issues

#### Slow Page Loads
```bash
# Enable query logging
# Add to .env: DB_LOG_LEVEL=debug

# Check slow queries
mysql -u meetapp_prod -p -e "SHOW VARIABLES LIKE 'slow_query_log';"

# Profile queries
php artisan tinker
>>> DB::enableQueryLog();
>>> RapatModel::with('attendees')->get();
>>> dd(DB::getQueryLog());
```

#### High Memory Usage
```bash
# Check PHP memory limit
php -i | grep memory_limit

# Monitor process memory
top -p $(pgrep -f "php artisan queue:work")
```

---

## 📋 Deployment Checklist

### Pre-Deployment Checklist
- [ ] **Environment Setup**: PHP, web server, database configured
- [ ] **Dependencies Installed**: Composer packages installed
- [ ] **Database Created**: Database and user configured
- [ ] **Migrations Run**: Database schema up to date
- [ ] **Environment Configured**: .env file properly set
- [ ] **File Permissions Set**: Proper directory permissions
- [ ] **Queue Service Running**: NSSM/systemd service configured
- [ ] **External Services**: Zoom and WhatsApp APIs configured
- [ ] **Security Hardened**: HTTPS, headers, permissions secured
- [ ] **Backup Strategy**: Automated backups configured
- [ ] **Monitoring Setup**: Logs and health checks configured

### Post-Deployment Verification
- [ ] **Application Loads**: Main page accessible
- [ ] **Login Works**: Authentication functional
- [ ] **Database Connected**: Data retrieval working
- [ ] **Queue Processing**: WhatsApp notifications sending
- [ ] **Zoom Integration**: OAuth flow working
- [ ] **File Uploads**: Documentation upload working
- [ ] **Public Access**: UID-based access functional
- [ ] **Error Handling**: Proper error pages displayed
- [ ] **Performance**: Acceptable response times
- [ ] **Security**: No sensitive information exposed

### Ongoing Maintenance Tasks
- [ ] **Daily**: Check logs and queue status
- [ ] **Weekly**: Review failed jobs and errors
- [ ] **Monthly**: Database optimization and cleanup
- [ ] **Quarterly**: Security updates and patches
- [ ] **Annually**: Performance review and optimization

---

## 📞 Support & Resources

### Documentation References
- **[AGENTS.md](AGENTS.md)** - Primary application overview
- **[AGENTS-ARCHITECTURE.md](AGENTS-ARCHITECTURE.md)** - System architecture
- **[AGENTS-API.md](AGENTS-API.md)** - Complete API reference
- **[AGENTS-MODELS.md](AGENTS-MODELS.md)** - Database models and relationships

### External Resources
- **Laravel Documentation**: https://laravel.com/docs/5.4
- **NSSM Documentation**: https://nssm.cc/
- **Zoom API Documentation**: https://marketplace.zoom.us/docs/api-reference
- **WaConnect Documentation**: https://app.waconnect.id/docs

### Emergency Contacts
- **System Administrator**: [Contact Information]
- **Database Administrator**: [Contact Information]
- **Network Administrator**: [Contact Information]
- **Application Developer**: [Contact Information]

---

**Last Updated**: 2026-01-21  
**Deployment Version**: Production Ready  
**Document Maintainer**: BPS Kalsel IT Team