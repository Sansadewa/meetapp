# 📋 MeetApp Kalsel - AI Assistant Guide

## 🎯 Application Overview

**MeetApp Kalsel** is a comprehensive meeting management system designed for BPS South Kalimantan (Badan Pusat Statistik Kalimantan Selatan). It manages both physical meeting room bookings and Zoom virtual meetings with integrated WhatsApp notifications and documentation management.

### 🏗️ Technology Stack
- **Framework**: Laravel 5.4
- **PHP Version**: >=5.6.4
- **Database**: MySQL
- **Frontend**: Bootstrap 3.x, jQuery 3.2.1
- **JavaScript Libraries**: FullCalendar, Select2, Popper.js
- **Queue System**: Laravel Queues with Database Driver
- **Windows Service**: NSSM for queue job management

### 🌟 Core Features
- **Physical Meeting Room Booking** with conflict detection
- **Zoom Virtual Meeting Integration** via OAuth 2.0
- **WhatsApp Notifications** for meeting alerts and reminders
- **Documentation Management** (PDF/DOC/DOCX upload/download)
- **Public Access** via 6-character UID codes
- **Audit Logging** for all changes
- **Role-based Access Control** (Admin vs User)

---

## 🚀 Quick Reference

### 📁 Most Important Files
```
app/Http/Controllers/meetController.php    # Main application logic
app/Models/RapatModel.php                  # Central meeting model
app/Jobs/NotifWa.php                       # WhatsApp notification job
app/Services/Zoom.php                      # Zoom API integration
routes/web.php                             # All application routes
.env                                       # Environment configuration
```

### 🔑 Key Methods (meetController.php)
- `getHomePage()` - User dashboard with today's meetings
- `getTodayPage()` - All meetings today by room
- `tambahRapat()` - Create new meeting with conflict detection
- `editRapat()` - Update existing meeting
- `cekOverlap()` - Room booking conflict validation
- `saveZoomRapat()` - Zoom meeting integration
- `sendNotifZoom()` - WhatsApp notification dispatch
- `showMeetingByUid()` - Public meeting access

### 🗄️ Database Tables
- `rapat` - Main meetings table
- `users` - User management
- `unit_kerja` - Organizational units
- `rapat_user` - Polymorphic attendee relationships
- `zoom` - Zoom meeting details
- `notulensi` - Meeting documentation

---

## 👥 User Roles & Permissions

### Level 2 - Administrators
- **Full Access**: All meetings, rooms, and units
- **Zoom Management**: Create and manage Zoom meetings
- **User Management**: View and manage all users
- **System Administration**: Full administrative privileges

### Level 1 - Regular Users
- **Unit-Restricted**: Only meetings within assigned units
- **Room Booking**: Can book physical meeting rooms
- **Zoom Requests**: Can request Zoom meetings (requires admin approval)
- **Documentation**: Upload/download meeting minutes

---

## 🔐 Authentication System

### Custom Authentication Flow
- **Middleware**: `AuthMiddleware` in `app/Http/Middleware/`
- **Password Hashing**: MD5 (legacy - should be updated)
- **Session Management**: Laravel session-based
- **Login Controller**: `app/Http/Controllers/LoginController.php`

### Access Control
```php
// Route protection middleware
Route::group(["middleware" => "is_authenticated"], function() {
    // All protected routes here
});
```

---

## 📅 Meeting Workflow

### 1. Meeting Creation
- User selects room and time
- **Conflict Detection**: Automatic overlap checking
- Attendee selection (users and/or units)
- Optional Zoom meeting request

### 2. Approval Process
- **Physical Rooms**: Automatic approval
- **Zoom Meetings**: Admin approval required
- **WhatsApp Notifications**: Sent to relevant parties

### 3. Meeting Management
- **Drag-and-Drop**: Calendar-based rescheduling
- **Real-time Updates**: Conflict detection on changes
- **Documentation**: Upload meeting minutes
- **Public Sharing**: UID-based external access

---

## 🔌 External Integrations

### Zoom Integration
- **OAuth 2.0 Flow**: Complete authentication cycle
- **Meeting Management**: Create, list, delete meetings
- **Token Storage**: `storage/app/zoom_credentials1.json`
- **Service Class**: `app/Services/Zoom.php`

### WhatsApp Integration
- **Service Provider**: WaConnect (app.waconnect.id)
- **Queue-Based**: Asynchronous notification sending
- **Job Class**: `app/Jobs/NotifWa.php`
- **Message Types**: Meeting alerts, reminders, Zoom requests

### PRTG Monitoring
- **Webhook Integration**: Network monitoring alerts
- **API Endpoint**: `/api/notif-prtg`
- **Legacy Support**: Commented backup endpoints

---

## 🚨 Critical Security Issues

### ⚠️ Hardcoded Configurations (Immediate Action Required)

#### Zoom Redirect URIs
```php
// File: app/Http/Controllers/zoomController.php
$redirectUri = 'https://bpskalsel.com/meetapp/callback-zoom';
```
**🔧 Fix**: Move to `.env` as `ZOOM_REDIRECT_URI`

#### Test Phone Numbers
```php
// Multiple files contain hardcoded numbers
'082113767398', '085791927509'
```
**🔧 Fix**: Move to `.env` as `TEST_PHONE_NUMBER`

### 🔒 Security Recommendations
1. **Update Password Hashing**: Replace MD5 with bcrypt
2. **Environment Variables**: Move all hardcoded values to `.env`
3. **API Authentication**: Add authentication to undocumented endpoints
4. **Input Validation**: Strengthen request validation
5. **Audit Logging**: Enhance change tracking

---

## 🔄 Queue System (NSSM)

### Windows Service Configuration
The Laravel queue system is managed using **NSSM (Non-Sucking Service Manager)** on Windows:

```batch
# Install queue worker as Windows service
nssm install MeetAppQueue "C:\php\php.exe" "artisan queue:work"
nssm set MeetAppQueue AppDirectory "C:\xampp\htdocs\meetapp"
nssm set MeetAppQueue AppStdout "C:\xampp\htdocs\meetapp\storage\logs\queue.log"
nssm start MeetAppQueue
```

### Queue Configuration
- **Driver**: Database (`QUEUE_DRIVER=database`)
- **Job Table**: `jobs`
- **Failed Jobs**: `failed_jobs`
- **Main Job**: `App\Jobs\NotifWa` (WhatsApp notifications)

### Queue Management Commands
```bash
# Start queue worker
php artisan queue:work

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear queue
php artisan queue:clear
```

---

## 🌐 Public Access System

### UID-Based Sharing
- **6-Character Codes**: Alphanumeric unique identifiers
- **Public Endpoints**: No authentication required
- **Access Control**: Meeting details and document downloads

### Public Routes
```php
// Public meeting access
GET /meeting/{uid}                    # Meeting details
GET /meeting/{uid}/download           # Document download
GET /notulensi/download/{filename}     # Direct file access
```

---

## 📊 Key Business Logic

### Conflict Detection Algorithm
```php
// File: app/Http/Controllers/meetController.php:cekOverlap()
public function cekOverlap($tanggal, $waktuMulai, $waktuSelesai, $ruang, $id = null)
{
    // Check for overlapping meetings in same room
    // Exclude current meeting when editing
    // Return boolean conflict status
}
```

### UID Generation
```php
// File: app/Models/RapatModel.php
public static function generateUniqueUid()
{
    // Generate 6-character alphanumeric code
    // Check for collisions
    // Retry if necessary
}
```

### Polymorphic Attendees
```php
// Support both users and units as attendees
// Table: rapat_user
// Fields: attendee_id, attendee_type ('App\UserModel' or 'App\UnitKerjaModel')
```

---

## 🛠️ Common Development Tasks

### Adding New Meeting Features
1. Update `RapatModel` with new fields
2. Modify `meetController@tambahRapat()` and `editRapat()`
3. Update database migration
4. Add validation rules
5. Update frontend forms

### Adding New Notification Types
1. Create new job class in `app/Jobs/`
2. Update `NotifAdmin` and `NotifUmum` models
3. Add routes for new notification endpoints
4. Configure queue processing

### Modifying Zoom Integration
1. Update `app/Services/Zoom.php`
2. Refresh OAuth tokens if needed
3. Update `.env` configuration
4. Test with Zoom API endpoints

---

## 📝 File Naming Conventions

### Documentation Files
- **Upload Path**: `public/notulensi/`
- **Naming Format**: `{MeetingName}_{Date}_{Timestamp}_{Random}.{ext}`
- **Example**: `Rapat_Bulanan_2024-01-15_1705312345_r1234.pdf`

### Database Logs
- **Deletion Log**: `rapatlog` table
- **Edit Log**: `editlog` table
- **Schedule Log**: `schedule_log` table

---

## 🔍 Debugging & Troubleshooting

### Common Issues
1. **Queue Not Processing**: Check NSSM service status
2. **WhatsApp Not Sending**: Verify token and API URL
3. **Zoom Token Expired**: Re-authenticate OAuth flow
4. **Meeting Conflicts**: Check time zone settings
5. **File Upload Issues**: Verify directory permissions

### Debug Tools
```bash
# Check queue status
php artisan queue:failed

# Test WhatsApp job
php artisan tinker
>>> dispatch(new App\Jobs\NotifWa($phone, $message));

# Check Zoom tokens
cat storage/app/zoom_credentials1.json
```

---

## 📚 Additional Documentation

- **[AGENTS-ARCHITECTURE.md](AGENTS-ARCHITECTURE.md)** - Detailed system architecture
- **[AGENTS-API.md](AGENTS-API.md)** - Complete API reference
- **[AGENTS-MODELS.md](AGENTS-MODELS.md)** - Database models and relationships
- **[AGENTS-DEPLOYMENT.md](AGENTS-DEPLOYMENT.md)** - Setup and deployment guide

---

## 🎯 Quick Start for AI Assistants

When working with this application:

1. **Always check for conflicts** when creating/editing meetings
2. **Use UID system** for public access instead of authentication
3. **Queue jobs** are handled by NSSM Windows service
4. **WhatsApp notifications** are asynchronous via database queue
5. **Zoom integration** requires OAuth token management
6. **Security**: Be aware of hardcoded credentials that need moving to `.env`

---

**Last Updated**: 2026-01-21  
**Application Version**: Laravel 5.4  
**Maintainer**: BPS Kalsel IT Team