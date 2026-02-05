# 🚀 MeetApp Deployment Guide

## Critical Changes in This Update

### 1. Login Assets Reorganization
**Directory moved:** `public/login/` → `public/assets/login/`

**Why:** Fixed routing conflict where `/login` route was showing directory listing instead of routing through Laravel.

### 2. HTTPS Detection for Cloudflare Tunnel
**Environment-based URL configuration** instead of hardcoded hacks.

---

## 📦 Deployment Steps

### **On Development Machine (Already Done)**

✅ Directory moved from `public/login/` to `public/assets/login/`
✅ View file updated with new asset paths
✅ Environment variables configured
✅ All URL hacks removed from code

### **On Production Server (Server A - 10.63.0.234)**

#### **Step 1: Pull Latest Code from Git**
```bash
cd E:\temphtdocs\meetapp
git pull origin main
```

#### **Step 2: Move Login Assets Directory** ⚠️ **CRITICAL**
```cmd
# On the server, manually move the directory
move E:\temphtdocs\meetapp\public\login E:\temphtdocs\meetapp\public\assets\login
```

**Why this is manual:** Git doesn't track the `public/login/` directory move on the server.

#### **Step 3: Copy Environment Files** ⚠️ **CRITICAL**

These files are NOT in git and must be copied manually:

**A. Copy .env file:**
```cmd
# Copy from your backup or add these new variables to existing .env:
```

Add to server's `.env`:
```env
APP_URL=https://meetapp.statkalsel.com
FORCE_HTTPS=true
INTERNAL_IP=10.63.0.234
ZOOM_REDIRECT_URI=https://meetapp.statkalsel.com/callback-zoom
```

**B. Verify Zoom credentials exist:**
```cmd
dir E:\temphtdocs\meetapp\storage\app\zoom_credentials1.json
```

If missing, copy from development machine.

#### **Step 4: Update Apache VirtualHost**

Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@statkalsel.com
    ServerName meetapp.statkalsel.com
    DocumentRoot "E:/temphtdocs/meetapp/public"
    
    <Directory "E:/temphtdocs/meetapp/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/meetapp.statkalsel.com-error.log"
    CustomLog "logs/meetapp.statkalsel.com-access.log" common
</VirtualHost>
```

**Key changes:**
- DocumentRoot points to `/public`
- Added `<Directory>` block with `AllowOverride All`
- Added `-Indexes` to prevent directory listing

#### **Step 5: Clear All Caches**
```cmd
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### **Step 6: Restart Services**
```cmd
# Restart Apache
net stop Apache2.4
net start Apache2.4

# Restart Queue Worker
nssm restart MeetAppQueue
```

#### **Step 7: Verify Directory Structure**
```cmd
# Old directory should NOT exist
dir E:\temphtdocs\meetapp\public\login
# Should return: "File Not Found"

# New directory should exist
dir E:\temphtdocs\meetapp\public\assets\login
# Should show: bootstrap.min.css, main.js, etc.
```

---

## ☁️ Cloudflare Configuration

### **Step 1: Add DNS Record**

**Cloudflare Dashboard** → **DNS** → **Records**

```
Type: CNAME
Name: meetapp
Target: <your-tunnel-id>.cfargotunnel.com
Proxy: ✅ Proxied (orange cloud)
TTL: Auto
```

### **Step 2: Configure Tunnel Public Hostname**

**Cloudflare Zero Trust** → **Networks** → **Tunnels** → **[Your Tunnel]** → **Public Hostnames**

```
Subdomain: meetapp
Domain: statkalsel.com
Path: [leave empty]
Service Type: HTTP
Service URL: 10.63.0.234:80
HTTP Host Header: meetapp.statkalsel.com  ✅ IMPORTANT
No TLS Verify: ✅ Enabled
```

### **Step 3: Update Zoom OAuth Settings**

**Zoom Marketplace** → **Your App** → **OAuth Redirect URL**

Add:
```
https://meetapp.statkalsel.com/callback-zoom
```

---

## 🧪 Testing Checklist

### **Test 1: Login Route (Most Important)**
```
URL: https://meetapp.statkalsel.com/login
Expected: Login page loads (not directory listing)
Check: No 404 errors in browser console
```

### **Test 2: Asset Loading**
```
URL: https://meetapp.statkalsel.com/login
Open Browser DevTools → Network tab
Expected: All CSS/JS files load from public/assets/login/
Check: No 404 errors on bootstrap.min.css, main.js, etc.
```

### **Test 3: Internal Access**
```
URL: http://10.63.0.234/meetapp
Expected: Works with HTTP (for maintenance)
Check: Login page loads
```

### **Test 4: Authentication**
```
Action: Login with valid credentials
Expected: Redirects to dashboard
Check: Session works correctly
```

### **Test 5: Zoom OAuth**
```
Action: Create Zoom meeting
Expected: Redirects to Zoom
Expected: Returns to https://meetapp.statkalsel.com/callback-zoom
Check: Meeting created successfully
```

---

## 🔧 Troubleshooting

### **Issue: Directory listing shown on /login**
**Cause:** Assets directory not moved on server
**Fix:**
```cmd
move E:\temphtdocs\meetapp\public\login E:\temphtdocs\meetapp\public\assets\login
php artisan view:clear
```

### **Issue: 404 errors on CSS/JS files**
**Cause:** Assets still looking for old path
**Fix:**
```cmd
# Verify login.blade.php has new paths
grep "public/assets/login" E:\temphtdocs\meetapp\resources\views\login\login.blade.php
# Should return 11 matches

# Clear cache
php artisan view:clear
```

### **Issue: Blank white page**
**Cause:** PHP/Laravel error
**Fix:**
```cmd
# Check Laravel logs
type E:\temphtdocs\meetapp\storage\logs\laravel.log | more

# Check Apache error logs
type C:\xampp\apache\logs\meetapp.statkalsel.com-error.log | more
```

### **Issue: Mixed content warnings**
**Cause:** HTTPS not being forced
**Fix:**
```cmd
# Verify .env has FORCE_HTTPS=true
type E:\temphtdocs\meetapp\.env | findstr FORCE_HTTPS

# Clear config cache
php artisan config:clear
```

---

## 📋 Pre-Deployment Checklist

**Before pushing to git:**
- [x] Login assets directory moved locally
- [x] View file updated with new asset paths
- [x] All URL hacks removed from code
- [x] .env updated with new variables
- [x] View cache cleared locally
- [x] Changes tested on development machine

**After pulling on server:**
- [ ] Login assets directory moved on server
- [ ] .env file updated with new variables
- [ ] Apache VirtualHost updated
- [ ] All caches cleared
- [ ] Apache restarted
- [ ] Queue worker restarted
- [ ] Cloudflare DNS configured
- [ ] Cloudflare Tunnel configured
- [ ] Zoom OAuth updated
- [ ] All tests passed

---

## 🔄 Rollback Plan

If issues occur:

### **Quick Rollback (Reverse Asset Move)**
```cmd
# On server, move directory back
move E:\temphtdocs\meetapp\public\assets\login E:\temphtdocs\meetapp\public\login

# Clear caches
php artisan view:clear

# Revert to previous git commit
git log --oneline
git checkout <previous-commit-hash>
```

### **Environment Variable Rollback**
```env
# In .env, revert to simple URL
APP_URL=http://10.63.0.234/meetapp
FORCE_HTTPS=false
```

---

## 📞 Support

**Check logs if issues occur:**
```cmd
# Laravel application logs
type E:\temphtdocs\meetapp\storage\logs\laravel.log

# Apache error logs
type C:\xampp\apache\logs\meetapp.statkalsel.com-error.log

# Apache access logs
type C:\xampp\apache\logs\meetapp.statkalsel.com-access.log
```

---

**Last Updated**: 2026-02-05  
**Deployment Version**: 1.1.0
