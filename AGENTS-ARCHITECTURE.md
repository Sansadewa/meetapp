# 🏗️ MeetApp Kalsel - System Architecture

## 📋 Overview

This document provides a comprehensive overview of the MeetApp Kalsel system architecture, including component relationships, data flow, and technical implementation details.

---

## 🎯 System Components

### Core Application Layer
```
┌─────────────────────────────────────────────────────────────┐
│                    Laravel 5.4 Framework                    │
├─────────────────────────────────────────────────────────────┤
│  Controllers  │  Models  │  Services  │  Jobs  │  Middleware │
└─────────────────────────────────────────────────────────────┘
```

### Key Components
- **Controllers**: Request handling and business logic
- **Models**: Data management and relationships
- **Services**: External API integrations
- **Jobs**: Asynchronous task processing
- **Middleware**: Request filtering and authentication

---

## 🔄 Data Flow Architecture

### Meeting Creation Flow
```
User Request → AuthMiddleware → meetController → RapatModel
     ↓                    ↓                ↓              ↓
Conflict Check → Database Save → Queue Job → WhatsApp API
     ↓                    ↓                ↓              ↓
Calendar Update → Attendee Notify → Zoom API → Public UID
```

### Zoom Integration Flow
```
OAuth Request → Zoom Service → Token Storage → Meeting Creation
       ↓              ↓              ↓              ↓
Callback URL → Token Refresh → Database Save → WhatsApp Notify
```

### WhatsApp Notification Flow
```
Event Trigger → Job Queue → NSSM Service → WaConnect API
       ↓              ↓              ↓              ↓
Database Log → Queue Worker → HTTP Request → Message Delivery
```

---

## 🧱 Component Architecture

### 1. Controllers Layer

#### meetController (Primary Controller)
```php
Location: app/Http/Controllers/meetController.php
Responsibilities:
- Meeting CRUD operations
- Conflict detection
- Attendee management
- Documentation handling
- Public access via UID
```

#### zoomController
```php
Location: app/Http/Controllers/zoomController.php
Responsibilities:
- OAuth token management
- Zoom meeting operations
- Callback handling
```

#### LoginController
```php
Location: app/Http/Controllers/LoginController.php
Responsibilities:
- User authentication
- Session management
- MD5 password validation
```

### 2. Models Layer

#### RapatModel (Central Model)
```php
Location: app/RapatModel.php
Table: rapat
Key Features:
- Polymorphic attendee relationships
- UID generation for public access
- Automatic audit logging
- Conflict detection methods
```

#### UserModel
```php
Location: app/UserModel.php
Table: users
Relationships:
- Many-to-many with UnitKerjaModel
- Polymorphic attendee for meetings
```

#### Supporting Models
```php
UnitKerjaModel    - Organizational units
ZoomModel         - Zoom meeting details
NotulensiModel    - Meeting documentation
RuangModel        - Physical meeting rooms
ScheduleLogModel  - Notification logging
```

### 3. Services Layer

#### Zoom Service
```php
Location: app/Services/Zoom.php
Responsibilities:
- OAuth 2.0 authentication flow
- Meeting CRUD operations
- Token management and refresh
- Registrant management
```

### 4. Jobs Layer

#### WhatsApp Notification Job
```php
Location: app/Jobs/NotifWa.php
Responsibilities:
- Asynchronous WhatsApp sending
- Third-party API integration
- Error handling and retry logic
```

### 5. Middleware Layer

#### Authentication Middleware
```php
Location: app/Http/Middleware/AuthMiddleware.php
Features:
- Custom session validation
- User role checking
- Maintenance mode bypass
```

#### Maintenance Middleware
```php
Location: app/Http/Middleware/CheckFormaintenanceMode.php
Features:
- IP-based bypass functionality
- Custom maintenance pages
```

---

## 🗄️ Database Architecture

### Core Tables Structure
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│      rapat      │    │   rapat_user    │    │      users      │
│-----------------│    │-----------------│    │-----------------│
│ id (PK)         │    │ rapat_id (FK)   │    │ id (PK)         │
│ uid (UNIQUE)    │◄───│ attendee_id     │    │ nama            │
│ nama            │    │ attendee_type   │    │ username        │
│ tanggal_*       │    │ created_at      │    │ password (MD5)  │
│ ruang_rapat     │    └─────────────────┘    │ unit_kerja      │
│ use_zoom        │                           │ level           │
│ created_by      │                           │ is_active       │
└─────────────────┘                           └─────────────────┘
         │                                            │
         │                                            │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│      zoom       │    │  unit_kerja_user│    │   unit_kerja    │
│-----------------│    │-----------------│    │-----------------│
│ id (PK)         │    │ user_model_id   │    │ id (PK)         │
│ rapat_id (FK)   │    │ unit_kerja_id   │    │ nama            │
│ zoom_id         │    │ tahun           │    │ singkatan       │
│ zoom_password   │    └─────────────────┘    │ class_bg        │
│ zoom_link       │                           │ tahun           │
│ host            │                           └─────────────────┘
│ tanggal_zoom    │                                    │
└─────────────────┘                                    │
                                                       │
┌─────────────────┐    ┌─────────────────┐            │
│   notulensi     │    │   schedule_log  │            │
│-----------------│    │-----------------│            │
│ id (PK)         │    │ id (PK)         │            │
│ rapat_id (FK)   │    │ rapat_id        │            │
│ filename        │    │ phone_number    │            │
│ original_name   │    │ message         │            │
│ file_path       │    │ status          │            │
│ uploaded_by     │    │ created_at      │            │
│ uploaded_at     │    └─────────────────┘            │
└─────────────────┘                                    │
                                                       │
┌─────────────────┐    ┌─────────────────┐            │
│      ruang      │    │    rapatlog     │            │
│-----------------│    │-----------------│            │
│ id_ruang (PK)   │    │ id (PK)         │            │
│ nama_ruang      │    │ id_rapat        │            │
│ kapasitas       │    │ nama_rapat      │            │
│ visible_ruang   │    │ deleted_by      │            │
│ deskripsi       │    │ deleted_at      │            │
└─────────────────┘    └─────────────────┘            │
                                                       │
                                                       ▼
                                            Polymorphic Relationship
```

### Relationship Types
- **One-to-Many**: Meeting → Zoom, Meeting → Documentation
- **Many-to-Many**: Users ↔ Units (with yearly pivot)
- **Polymorphic**: Meetings ↔ Attendees (Users or Units)
- **Foreign Key**: All relationships properly indexed

---

## 🔐 Security Architecture

### Authentication Flow
```
Login Request → LoginController → MD5 Validation → Session Creation
       ↓                ↓                ↓              ↓
Middleware Check → User Level → Unit Assignment → Route Access
```

### Access Control Levels
```
Level 2 (Admin)
├── All meetings access
├── User management
├── Zoom administration
└── System configuration

Level 1 (User)
├── Unit-restricted meetings
├── Room booking
├── Zoom requests
└── Documentation upload
```

### Public Access System
```
UID Generation → Public URL → No Authentication → Limited Access
       ↓              ↓              ↓              ↓
6-Character Code → Meeting Details → Document Download → Audit Log
```

---

## 🌐 External Integration Architecture

### Zoom Integration
```
OAuth Flow → Token Storage → API Calls → Meeting Management
    ↓            ↓            ↓            ↓
Authorization → JSON File → REST API → Database Sync
```

### WhatsApp Integration
```
Event Trigger → Job Queue → NSSM Service → WaConnect API
      ↓            ↓            ↓            ↓
Database Log → Async Worker → HTTP Request → Message Delivery
```

### PRTG Monitoring
```
Network Alert → Webhook → API Endpoint → WhatsApp Notification
      ↓            ↓            ↓            ↓
System Event → HTTP POST → Validation → Job Queue
```

---

## 🔄 Queue System Architecture

### NSSM Windows Service Integration
```
Laravel Queue → NSSM Service → Windows Service Manager → Background Worker
      ↓              ↓              ↓                    ↓
Database Jobs → PHP Process → Service Monitoring → Job Processing
```

### Queue Configuration
```php
// Queue Driver: Database
// Job Table: jobs
// Failed Jobs: failed_jobs
// Main Job: App\Jobs\NotifWa

// NSSM Service Setup
nssm install MeetAppQueue "C:\php\php.exe" "artisan queue:work"
nssm set MeetAppQueue AppDirectory "C:\xampp\htdocs\meetapp"
nssm set MeetAppQueue AppStdout "C:\xampp\htdocs\meetapp\storage\logs\queue.log"
```

### Job Processing Flow
```
Event Occurs → Job Dispatched → Database Queue → NSSM Worker → External API
     ↓              ↓              ↓              ↓              ↓
Application → jobs Table → Queue:work → PHP Process → HTTP Request
```

---

## 📁 File System Architecture

### Upload Structure
```
public/
├── notulensi/                    # Meeting documentation
│   ├── MeetingName_Date_Time_Random.pdf
│   ├── MeetingName_Date_Time_Random.docx
│   └── ...
├── login/                        # Login page assets
│   ├── main.css
│   ├── main.js
│   └── jquery-3.2.1.min.js
└── fonts/                        # Custom fonts
    └── poppins/
        ├── Poppins-Regular.ttf
        ├── Poppins-Medium.ttf
        └── Poppins-Bold.ttf
```

### Storage Structure
```
storage/
├── app/
│   ├── zoom_credentials1.json    # OAuth tokens
│   └── ...
├── logs/
│   ├── laravel.log
│   ├── queue.log                 # NSSM service logs
│   └── ...
└── framework/
    ├── cache/
    ├── sessions/
    └── views/
```

---

## 🔧 Configuration Architecture

### Environment Variables
```php
// Application Configuration
APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL

// Database Configuration
DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

// External Services
ZOOM_CLIENT_ID1, ZOOM_CLIENT_SECRET1, ZOOM_CREDENTIALS_PATH1
WA_CONNECT_TOKEN, WA_CONNECT_URL

// Queue System
QUEUE_DRIVER=database
```

### Custom Configuration
```php
// config/app.php
- Dynamic URL generation
- Custom maintenance mode
- Timezone setting (Asia/Singapore)

// Hardcoded Configurations (Security Issues)
- WhatsApp API token in NotifWa.php
- Zoom redirect URIs in zoomController.php
- Test phone numbers in multiple files
```

---

## 🚨 Architecture Security Issues

### Hardcoded Configurations
```
🚨 Critical Issues:
├── WhatsApp Token: app/Jobs/NotifWa.php:55
├── Zoom Redirect: app/Http/Controllers/zoomController.php
├── Test Numbers: Multiple controller files
└── Database Credentials: .env file

🔧 Recommended Fixes:
├── Move all secrets to .env
├── Use environment-specific URLs
├── Implement proper API authentication
└── Add input validation and sanitization
```

### Authentication Vulnerabilities
```
⚠️ Legacy Issues:
├── MD5 password hashing
├── Custom authentication middleware
├── Session-based authentication
└── Limited input validation

🔒 Security Enhancements:
├── Upgrade to bcrypt password hashing
├── Implement Laravel's built-in auth
├── Add CSRF protection
├── Enable request validation
```

---

## 📊 Performance Architecture

### Database Optimization
```
Indexing Strategy:
├── Primary keys on all tables
├── Foreign key constraints
├── Unique constraints on UID
└── Composite indexes for queries

Query Optimization:
├── Eager loading for relationships
├── Database query caching
├── Efficient conflict detection
└── Optimized attendee queries
```

### Caching Strategy
```
Cache Layers:
├── Application cache (file driver)
├── Session cache (file driver)
├── Query results caching
└── Static asset caching

Performance Considerations:
├── Database connection pooling
├── Queue-based notifications
├── Asynchronous job processing
└── Optimized file uploads
```

---

## 🔄 Scalability Architecture

### Horizontal Scaling Considerations
```
Database Scaling:
├── Read replicas for reporting
├── Database connection pooling
├── Query optimization
└── Index maintenance

Application Scaling:
├── Load balancer configuration
├── Session storage (Redis)
├── File storage (cloud)
└── Queue worker scaling
```

### Vertical Scaling Considerations
```
Resource Requirements:
├── PHP memory limits
├── Database connection limits
├── File upload size limits
└── Queue processing capacity

Monitoring Needs:
├── Application performance
├── Database query times
├── Queue processing rates
└── External API response times
```

---

## 📝 Development Architecture

### Code Organization
```
PSR-4 Autoloading:
├── App\ → app/
├── Controllers → app/Http/Controllers/
├── Models → app/
├── Jobs → app/Jobs/
└── Services → app/Services/

Coding Standards:
├── PSR-2 coding style
├── Laravel conventions
├── Model-View-Controller pattern
└── Service repository pattern
```

### Testing Architecture
```
Test Structure:
├── Unit tests (tests/Unit/)
├── Feature tests (tests/Feature/)
├── Integration tests
└── API endpoint tests

Testing Considerations:
├── Database transactions
├── Mock external services
├── Test data factories
└── Authentication testing
```

---

## 🔮 Future Architecture Considerations

### Modernization Opportunities
```
Framework Upgrade:
├── Laravel 5.4 → Latest Laravel
├── PHP 5.6 → PHP 8.x
├── MySQL → PostgreSQL (optional)
└── Bootstrap 3 → Bootstrap 5

Technology Improvements:
├── REST API → GraphQL
├── jQuery → Vue.js/React
├── Server rendering → SPA
└── File storage → Cloud storage
```

### Security Enhancements
```
Authentication Modernization:
├── Custom auth → Laravel Sanctum
├── Session-based → Token-based
├── MD5 → bcrypt/Argon2
└── Basic validation → Comprehensive validation

Infrastructure Security:
├── HTTPS enforcement
├── API rate limiting
├── Input sanitization
└── Security headers
```

---

**Last Updated**: 2026-01-21  
**Architecture Version**: Laravel 5.4  
**Document Maintainer**: BPS Kalsel IT Team