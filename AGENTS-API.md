# 🌐 MeetApp Kalsel - Complete API Reference

## 📋 Overview

This document provides a comprehensive reference for all API endpoints, routes, and controller methods in the MeetApp Kalsel application.

---

## 🛡️ Authentication & Access Control

### Authentication Middleware
```php
Middleware: is_authenticated
Location: app/Http/Middleware/AuthMiddleware.php

Protected Routes: All routes within the middleware group
Public Routes: UID-based meeting access endpoints
```

### User Levels
- **Level 2**: Administrators (full access)
- **Level 1**: Regular users (unit-restricted access)

---

## 🌉 Web Routes (Protected)

All routes in this section require authentication via the `is_authenticated` middleware.

### 🏠 Dashboard & Navigation

#### Home Dashboard
```http
GET /
Controller: meetController@getHomePage
Description: User's personal dashboard with today's meetings
Access: User-specific (unit-restricted for Level 1)
```

#### Today's Meetings
```http
GET /today
Controller: meetController@getTodayPage
Description: All meetings today organized by room
Access: All meetings visible to user level
```

#### Login Page (Within Protected Group)
```http
GET /login
Controller: Closure (returns login view)
Description: Login page (accessible within authenticated group)
```

### 📅 Meeting Management

#### Create Meeting Page
```http
GET /rapat
Controller: meetController@getBuatRapatPage
Description: Display meeting creation form
```

#### Meeting List
```http
GET /lists
Controller: meetController@getDaftarRapatPage
Description: Display list of all meetings
```

#### Upload Documentation
```http
GET /upload-notulensi
Controller: meetController@getUploadNotulensiPage
Description: Display documentation upload form
```

#### Zoom Request Page
```http
GET /request-zoom
Controller: meetController@getRequestZoomPage
Description: Display Zoom meeting request form
```

### 🔍 Data Retrieval Endpoints

#### Get Organizational Units
```http
GET /get-unit-kerja
Controller: meetController@getUnitKerja
Description: Retrieve list of organizational units
Response: JSON array of units
```

#### Get Meeting Rooms
```http
GET /get-ruang
Controller: meetController@getRuang
Description: Retrieve list of available meeting rooms
Response: JSON array of rooms
```

#### Search Attendees
```http
GET /search-attendees
Controller: meetController@searchAttendees
Description: Search for users and units as attendees
Parameters: q (search query)
Response: JSON array of matching attendees
```

#### Get All Meetings
```http
GET /get-rapat
Controller: meetController@getRapatAll
Description: Retrieve all meetings (filtered by user level)
Response: JSON array of meetings
```

#### Get Meeting Data
```http
GET /get-data-rapat
Controller: meetController@getRapat
Parameters: id (meeting ID)
Description: Retrieve specific meeting data
Response: JSON meeting object
```

### 📝 Meeting CRUD Operations

#### Create Meeting
```http
POST /tambah-rapat
Controller: meetController@tambahRapat
Description: Create new meeting with conflict detection
Parameters: 
- nama (meeting name)
- topik (topic)
- tanggal_rapat_start/end
- waktu_mulai/selesai_rapat
- ruang_rapat (room)
- use_zoom (boolean)
- attendees (array)
Response: JSON with success/error status
```

#### Get Edit Data
```http
POST /get-data-rapat-edit
Controller: meetController@getDataRapatEdit
Parameters: id (meeting ID)
Description: Retrieve meeting data for editing
Response: JSON meeting object with attendees
```

#### Update Meeting
```http
POST /edit-rapat
Controller: meetController@editRapat
Description: Update existing meeting
Parameters: Same as create + id
Response: JSON with success/error status
```

#### Drag & Drop Reschedule
```http
POST /edit-tangal-rapat-drag
Controller: meetController@editTanggalRapatDrag
Description: Reschedule meeting via drag-and-drop
Parameters: id, new_date, new_time
Response: JSON with success/error status
```

#### Resize Meeting Duration
```http
POST /edit-tangal-rapat-resize
Controller: meetController@editTanggalRapatResize
Description: Change meeting duration via resize
Parameters: id, new_start_time, new_end_time
Response: JSON with success/error status
```

#### Delete Meeting
```http
POST /hapus-rapat
Controller: meetController@hapusRapat
Description: Delete meeting with audit logging
Parameters: id (meeting ID)
Response: JSON with success/error status
```

### 🎥 Zoom Integration

#### Get Zoom Token
```http
GET /getzoomtoken
Controller: zoomController@getFirstToken
Description: Initiate Zoom OAuth flow
Redirect: To Zoom OAuth authorization
```

#### List Zoom Meetings
```http
GET /listmeetingZoom
Controller: zoomController@listmeetingZoom
Description: List Zoom meetings via API
Response: JSON array of Zoom meetings
```

#### Get Zoom Details
```http
POST /get-detail-zoom
Controller: meetController@getDetailZoom
Parameters: rapat_id (meeting ID)
Description: Retrieve Zoom meeting details
Response: JSON Zoom meeting object
```

#### Save Zoom Meeting
```http
POST /save-zoom-rapat
Controller: meetController@saveZoomRapat
Description: Create/update Zoom meeting
Parameters: 
- rapat_id
- tanggal_zoom
- zoom_host
Response: JSON with success/error status
```

#### Get Zoom Details for Edit
```http
POST /get-zoom-rinc
Controller: meetController@getZoomRinc
Parameters: rapat_id (meeting ID)
Description: Retrieve Zoom details for editing
Response: JSON Zoom meeting object
```

#### Save Edit Zoom
```http
POST /save-edit-zoom
Controller: meetController@saveEditZoom
Description: Update existing Zoom meeting
Parameters: zoom_id, zoom_data
Response: JSON with success/error status
```

#### Get Zoom Host
```http
POST /get-pj-zoom
Controller: meetController@getPJZoom
Parameters: rapat_id (meeting ID)
Description: Get Zoom meeting host information
Response: JSON host object
```

#### Get Potential Hosts
```http
GET /get-calon-host
Controller: meetController@getCalonHost
Description: Get list of potential Zoom hosts
Response: JSON array of users
```

#### Get Zoom Host Details
```http
POST /get-zoom-host
Controller: meetController@getZoomHost
Parameters: user_id
Description: Get specific user's Zoom hosting details
Response: JSON host information
```

#### Edit Zoom Host
```http
POST /save-edit-host
Controller: meetController@editHost
Description: Update Zoom meeting host
Parameters: zoom_id, new_host_id
Response: JSON with success/error status
```

### 📄 Documentation Management

#### Upload Documentation
```http
POST /upload-notulensi
Controller: meetController@uploadNotulensi
Description: Upload meeting minutes/documentation
Parameters: 
- file (multipart/form-data)
- rapat_id (meeting ID)
Response: JSON with upload status
```

#### Delete Documentation
```http
POST /hapus-notulensi
Controller: meetController@hapusNotulensi
Description: Delete meeting documentation
Parameters: id (documentation ID)
Response: JSON with success/error status
```

#### Download Documentation
```http
GET /download-notulensi
Controller: meetController@downloadNotulensi
Parameters: id (documentation ID)
Description: Download meeting documentation
Response: File download
```

### 📊 Statistics & Reporting

#### Get Chart Data
```http
POST /get-data-grafik
Controller: meetController@getDataGrafik
Description: Retrieve data for meeting statistics charts
Parameters: 
- date_range (start_date, end_date)
- chart_type
Response: JSON chart data
```

### 📱 Notifications

#### Send Zoom Notification
```http
POST /send-notif-zoom
Controller: meetController@sendNotifZoom
Description: Send WhatsApp notification for Zoom meeting
Parameters: 
- rapat_id (meeting ID)
- message_type
Response: JSON with notification status
```

### 🔍 Meeting Details

#### Get Meeting Details
```http
POST /detail-rapat
Controller: meetController@getDetailRapat
Parameters: id (meeting ID)
Description: Retrieve comprehensive meeting details
Response: JSON meeting object with all relations
```

### 🧪 Testing & Debug

#### Test Dispatch
```http
GET /tes-dispatch
Controller: meetController@tesDispatch
Description: Test WhatsApp job dispatch
Response: JSON test result
```

---

## 🌐 Public Routes (No Authentication)

### 📋 Public Meeting Access

#### Public Meeting Details
```http
GET /meeting/{uid}
Controller: meetController@showMeetingByUid
Parameters: uid (6-character unique identifier)
Description: Public access to meeting details via UID
Access: No authentication required
Response: Meeting details page
```

#### Public Document Download
```http
GET /meeting/{uid}/download
Controller: meetController@downloadNotulensiByUid
Parameters: uid (meeting UID)
Description: Download meeting documentation via UID
Access: No authentication required
Response: File download
```

#### Direct File Access
```http
GET /notulensi/download/{filename}
Controller: meetController@downloadNotulensiFile
Parameters: filename (document filename)
Description: Direct access to uploaded files
Access: No authentication required
Response: File download
```

---

## 🔧 System & Utility Routes

### 🛠️ Maintenance

#### Maintenance Page
```http
GET /maintenance
Controller: Closure (returns maintenance view)
Description: Custom maintenance page
Access: Bypass available for specific IPs
```

### 📱 Reminder System

#### Room Meeting Reminder
```http
GET /reminder-ruang-rapat
Controller: meetController@reminderRuangRapat
Description: Send reminders for physical room meetings
Access: System/automated use
Response: JSON reminder status
```

#### Zoom Host Reminder
```http
GET /reminder-host-zoom
Controller: meetController@reminderHostZoomAll
Description: Send reminders to Zoom meeting hosts
Access: System/automated use
Response: JSON reminder status
```

### 🔐 Authentication

#### OAuth Callback
```http
GET /callback-zoom
Controller: zoomController@callbackZoom
Description: Zoom OAuth callback handler
Parameters: code, state
Response: Token storage and redirect
```

#### Logout
```http
GET /logout
Controller: Closure (session cleanup)
Description: User logout and session cleanup
Response: Redirect to login page
```

#### Login Processing
```http
POST /login
Controller: LoginController@login
Description: Process user login
Parameters: username, password
Response: Authentication result and redirect
```

---

## 📡 API Routes (RESTful)

### 📨 External Webhooks

#### PRTG Notification Webhook
```http
POST /api/notif-prtg
Controller: meetController@notifPRTG (if exists)
Description: PRTG monitoring system webhook
Parameters: PRTG alert data
Response: JSON acknowledgment
Access: External system (should be secured)
```

#### Quick Notification
```http
POST /api/notif-quick
Controller: meetController@notifQuick (if exists)
Description: Quick notification endpoint
Parameters: message, recipients
Response: JSON status
Access: External system (should be secured)
```

#### Standard API User
```http
GET /api/user
Controller: Standard Laravel API
Description: Get authenticated user information
Response: JSON user object
Access: API token authentication
```

---

## 📋 Request/Response Formats

### Standard JSON Response Format
```json
{
    "result": "success|error",
    "message": "Human readable message",
    "data": {
        // Response data
    },
    "errors": {
        // Validation errors (if any)
    }
}
```

### Meeting Object Structure
```json
{
    "id": 123,
    "uid": "ABC123",
    "nama": "Meeting Name",
    "topik": "Meeting Topic",
    "tanggal_rapat_start": "2024-01-15",
    "tanggal_rapat_end": "2024-01-15",
    "waktu_mulai_rapat": "09:00:00",
    "waktu_selesai_rapat": "10:00:00",
    "ruang_rapat": "Meeting Room 1",
    "jumlah_peserta": 10,
    "use_zoom": true,
    "created_by": 456,
    "created_at": "2024-01-10 08:00:00",
    "attendees": [
        {
            "id": 789,
            "name": "John Doe",
            "type": "user"
        }
    ],
    "zoom_meetings": [
        {
            "id": 101,
            "zoom_id": "123456789",
            "zoom_password": "abc123",
            "zoom_link": "https://zoom.us/j/123456789",
            "host": "Jane Smith",
            "tanggal_zoom": "2024-01-15"
        }
    ]
}
```

### User Object Structure
```json
{
    "id": 456,
    "nama": "John Doe",
    "username": "johndoe",
    "nip": "123456789",
    "no_hp": "08123456789",
    "unit_kerja": "IT Division",
    "level": 1,
    "is_active": true,
    "unit_kerjas": [
        {
            "id": 1,
            "nama": "IT Division",
            "singkatan": "IT",
            "tahun": 2024
        }
    ]
}
```

### Room Object Structure
```json
{
    "id_ruang": 1,
    "nama_ruang": "Meeting Room 1",
    "kapasitas": 20,
    "visible_ruang": true,
    "deskripsi": "Main meeting room with projector"
}
```

---

## ⚠️ Error Handling

### Common Error Responses

#### Authentication Error
```json
{
    "result": "error",
    "message": "Authentication required",
    "redirect": "/login"
}
```

#### Validation Error
```json
{
    "result": "error",
    "message": "Validation failed",
    "errors": {
        "nama": ["Meeting name is required"],
        "tanggal_rapat_start": ["Invalid date format"]
    }
}
```

#### Conflict Error
```json
{
    "result": "error",
    "message": "Room booking conflict detected",
    "conflict": {
        "meeting": "Existing Meeting Name",
        "time": "09:00-10:00"
    }
}
```

#### Authorization Error
```json
{
    "result": "error",
    "message": "Insufficient permissions",
    "required_level": 2,
    "current_level": 1
}
```

---

## 🔒 Security Considerations

### API Security Issues

#### Unprotected Endpoints
```php
// These endpoints need authentication:
/api/notif-prtg     // PRTG webhook
/api/notif-quick    // Quick notifications
/tes-dispatch       // Testing endpoint
```

#### Hardcoded Configurations
```php
// Security risks found:
app/Jobs/NotifWa.php:55        // Hardcoded WhatsApp token
app/Http/Controllers/zoomController.php  // Hardcoded redirect URIs
Multiple controllers           // Hardcoded test phone numbers
```

### Recommended Security Enhancements

#### API Authentication
```php
// Add API token authentication:
Route::middleware('auth:api')->group(function () {
    Route::post('/api/notif-prtg', 'PRTGController@notify');
    Route::post('/api/notif-quick', 'NotificationController@quick');
});
```

#### Input Validation
```php
// Strengthen request validation:
$request->validate([
    'nama' => 'required|string|max:255',
    'tanggal_rapat_start' => 'required|date',
    'waktu_mulai_rapat' => 'required|date_format:H:i',
]);
```

#### Rate Limiting
```php
// Add rate limiting to external endpoints:
Route::middleware('throttle:60,1')->group(function () {
    // External webhook routes
});
```

---

## 📊 API Usage Examples

### Creating a Meeting
```javascript
// POST /tambah-rapat
const meetingData = {
    nama: "Weekly Team Meeting",
    topik: "Project Status Update",
    tanggal_rapat_start: "2024-01-15",
    tanggal_rapat_end: "2024-01-15",
    waktu_mulai_rapat: "09:00",
    waktu_selesai_rapat: "10:00",
    ruang_rapat: "Meeting Room 1",
    use_zoom: true,
    attendees: [
        { id: 123, type: "user" },
        { id: 1, type: "unit" }
    ]
};

fetch('/tambah-rapat', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(meetingData)
})
.then(response => response.json())
.then(data => console.log(data));
```

### Getting Meeting Details
```javascript
// POST /detail-rapat
fetch('/detail-rapat', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ id: 123 })
})
.then(response => response.json())
.then(meeting => {
    console.log('Meeting:', meeting);
    console.log('Attendees:', meeting.attendees);
    console.log('Zoom Meetings:', meeting.zoom_meetings);
});
```

### Public Access via UID
```javascript
// GET /meeting/ABC123
fetch('/meeting/ABC123')
.then(response => response.text())
.then(html => {
    // Returns HTML page for public viewing
    document.body.innerHTML = html;
});
```

---

## 🔄 API Versioning & Future Considerations

### Current API Status
- **Version**: 1.0 (implicit)
- **Format**: Mixed (web routes + API routes)
- **Authentication**: Session-based + UID-based
- **Documentation**: This document

### Future API Improvements
```php
// Recommended API structure:
Route::prefix('api/v1')->group(function () {
    Route::apiResource('meetings', 'MeetingApiController');
    Route::apiResource('rooms', 'RoomApiController');
    Route::apiResource('users', 'UserApiController');
    Route::post('notifications/whatsapp', 'WhatsAppNotificationController');
});
```

### Modernization Considerations
- **REST API**: Convert all operations to proper REST endpoints
- **API Tokens**: Implement Laravel Sanctum or Passport
- **GraphQL**: Consider for complex data requirements
- **API Documentation**: Use OpenAPI/Swagger specification

---

**Last Updated**: 2026-01-21  
**API Version**: 1.0 (Laravel 5.4)  
**Document Maintainer**: BPS Kalsel IT Team