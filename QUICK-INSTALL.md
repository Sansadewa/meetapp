# 🚀 MeetApp Kalsel - Quick Installation Guide

## ⚡ Quick Start Installation

This guide provides the fastest way to get MeetApp Kalsel running on your system.

---

## 🪟 Windows Installation (XAMPP)

### 1. Install XAMPP
```batch
# Download and install XAMPP from: https://www.apachefriends.org/download.html
# Choose the version with PHP 7.4+ if available
```

### 2. Install Composer
```batch
# Download and install Composer from: https://getcomposer.org/download/
# During installation, select your XAMPP PHP installation
```

### 3. Setup Application
```batch
# Navigate to XAMPP htdocs
cd C:\xampp\htdocs

# Download or clone MeetApp Kalsel
# If you have the files, extract to: C:\xampp\htdocs\meetapp

# Install dependencies
cd meetapp
composer install

# Create environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```batch
# Start MySQL from XAMPP Control Panel
# Go to: http://localhost/phpmyadmin

# Create database
CREATE DATABASE meetappdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional but recommended)
CREATE USER 'meetapp_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON meetappdb.* TO 'meetapp_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Configure Environment
```batch
# Edit .env file with notepad or VS Code
notepad .env

# Update these values:
DB_DATABASE=meetappdb
DB_USERNAME=meetapp_user  # or root
DB_PASSWORD=your_password
APP_URL=http://localhost/meetapp
```

### 6. Run Migrations
```batch
php artisan migrate
```

### 7. Setup Queue Worker (NSSM)
```batch
# Download NSSM from: https://nssm.cc/download
# Extract to C:\nssm\

# Install queue service
C:\nssm\nssm.exe install MeetAppQueue "C:\php\php.exe" "C:\xampp\htdocs\meetapp\artisan queue:work"
C:\nssm\nssm.exe set MeetAppQueue AppDirectory "C:\xampp\htdocs\meetapp"
C:\nssm\nssm.exe start MeetAppQueue
```

### 8. Access Application
```batch
# Start Apache from XAMPP Control Panel
# Open browser and go to: http://localhost/meetapp
```

---

## 🐧 Linux Installation (Ubuntu/Debian)

### 1. Install Dependencies
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-cli php-mbstring php-xml php-curl php-zip php-gd php-mysql php-pdo-mysql libapache2-mod-php -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2. Setup Application
```bash
# Navigate to web directory
cd /var/www/html

# Download or clone application
# git clone https://github.com/your-org/meetapp-kalsel.git meetapp
# Or extract files to /var/www/html/meetapp

# Install dependencies
cd meetapp
composer install --no-dev --optimize-autoloader

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE meetappdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'meetapp_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON meetappdb.* TO 'meetapp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Configure Environment
```bash
# Edit environment file
sudo nano .env

# Update these values:
DB_DATABASE=meetappdb
DB_USERNAME=meetapp_user
DB_PASSWORD=strong_password
APP_URL=http://your-domain.com/meetapp
```

### 5. Set Permissions
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/meetapp
sudo chmod -R 755 /var/www/html/meetapp
sudo chmod -R 777 /var/www/html/meetapp/storage
sudo chmod -R 777 /var/www/html/meetapp/bootstrap/cache
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Configure Apache
```bash
# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers

# Create virtual host
sudo nano /etc/apache2/sites-available/meetapp.conf
```

Add this content:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/meetapp/public
    
    <Directory /var/www/html/meetapp/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/meetapp_error.log
    CustomLog ${APACHE_LOG_DIR}/meetapp_access.log combined
</VirtualHost>
```

```bash
# Enable site and restart Apache
sudo a2ensite meetapp.conf
sudo systemctl restart apache2
```

### 8. Setup Queue Worker
```bash
# Create systemd service
sudo nano /etc/systemd/system/meetapp-queue.service
```

Add this content:
```ini
[Unit]
Description=MeetApp Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/html/meetapp/artisan queue:work
WorkingDirectory=/var/www/html/meetapp

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl daemon-reload
sudo systemctl enable meetapp-queue
sudo systemctl start meetapp-queue
```

---

## 🔧 Post-Installation Configuration

### 1. Configure External Services

#### Zoom Integration
```bash
# 1. Go to: https://marketplace.zoom.us/
# 2. Create Server-to-Server OAuth app
# 3. Get Client ID and Client Secret
# 4. Add redirect URI: https://your-domain.com/meetapp/callback-zoom

# Update .env file:
ZOOM_CLIENT_ID1=your_client_id
ZOOM_CLIENT_SECRET1=your_client_secret
ZOOM_REDIRECT_URI=https://your-domain.com/meetapp/callback-zoom
```

#### WhatsApp Integration
```bash
# 1. Go to: https://app.waconnect.id/
# 2. Register and get API token
# 3. Update .env file:
WA_CONNECT_TOKEN=your_api_token_here
```

### 2. Test Installation
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test queue worker
php artisan queue:failed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 3. Access Application
- **Windows**: http://localhost/meetapp
- **Linux**: http://your-domain.com/meetapp

### 4. Default Login
- Check your database for default users
- Or create a new user via database/registration

---

## ⚠️ Troubleshooting

### Common Issues

#### "Whoops, looks like something went wrong."
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/

# Clear caches
php artisan cache:clear
php artisan config:clear
```

#### Database Connection Failed
```bash
# Test database connection
mysql -u username -p -h localhost database_name

# Check .env configuration
cat .env | grep DB_
```

#### Queue Not Working
```bash
# Check queue service status
# Windows: C:\nssm\nssm.exe status MeetAppQueue
# Linux: sudo systemctl status meetapp-queue

# Check queue table
mysql -u username -p -e "SELECT * FROM jobs;"
```

#### Mod Rewrite Not Working
```bash
# Enable Apache rewrite module
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check .htaccess file exists in public/
ls -la public/.htaccess
```

---

## 📚 Next Steps

1. **Read the full documentation**: AGENTS-DEPLOYMENT.md
2. **Configure external services**: Zoom and WhatsApp
3. **Set up SSL/HTTPS**: For production use
4. **Configure backups**: Automated database backups
5. **Monitor logs**: Regular log checking
6. **Security hardening**: Follow security checklist

---

## 🆘 Need Help?

- **Full Documentation**: See AGENTS-DEPLOYMENT.md
- **API Reference**: See AGENTS-API.md
- **Database Schema**: See AGENTS-MODELS.md
- **System Architecture**: See AGENTS-ARCHITECTURE.md

---

**Last Updated**: 2026-01-21  
**Quick Install Version**: 1.0