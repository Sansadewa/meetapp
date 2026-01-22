# 🗄️ MeetApp Kalsel - Database Models & Relationships

## 📋 Overview

This document provides comprehensive information about the database structure, models, relationships, and business logic in the MeetApp Kalsel application.

---

## 🗃️ Database Schema Overview

### Core Tables Structure
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│      rapat      │    │   rapat_user    │    │      users      │
│-----------------│    │-----------------│    │-----------------│
│ id (PK)         │    │ rapat_id (FK)   │    │ id (PK)         │
│ uid (UNIQUE)    │◄───│ attendee_id     │    │ nama            │
│ nama            │    │ attendee_type   │    │ username        │
│ topik           │    │ created_at      │    │ password (MD5)  │
│ tanggal_*       │    └─────────────────┘    │ nip             │
│ waktu_*         │                           │ no_hp           │
│ ruang_rapat     │    ┌─────────────────┐    │ unit_kerja      │
│ jumlah_peserta  │    │  unit_kerja_user│    │ level           │
│ use_zoom        │    │-----------------│    │ is_active       │
│ nohp_pj         │    │ user_model_id   │    │ created_at      │
│ created_by      │    │ unit_kerja_id   │    │ updated_at      │
│ created_at      │    │ tahun           │    └─────────────────┘
│ updated_at      │    └─────────────────┘            │
└─────────────────┘                                    │
         │                                            │
         │                                            │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│      zoom       │    │   unit_kerja    │    │   notulensi     │
│-----------------│    │-----------------│    │-----------------│
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ rapat_id (FK)   │    │ nama            │    │ rapat_id (FK)   │
│ zoom_id         │    │ singkatan       │    │ filename        │
│ zoom_password   │    │ class_bg        │    │ original_name   │
│ zoom_link       │    │ tahun           │    │ file_path       │
│ host            │    │ created_at      │    │ uploaded_by     │
│ tanggal_zoom    │    │ updated_at      │    │ uploaded_at     │
│ created_at      │    └─────────────────┘    └─────────────────┘
│ updated_at      │                                    │
└─────────────────┘                                    │
                                                       │
┌─────────────────┐    ┌─────────────────┐            │
│      ruang      │    │   schedule_log  │            │
│-----------------│    │-----------------│            │
│ id_ruang (PK)   │    │ id (PK)         │            │
│ nama_ruang      │    │ rapat_id        │            │
│ kapasitas       │    │ phone_number    │            │
│ visible_ruang   │    │ message         │            │
│ deskripsi       │    │ status          │            │
│ created_at      │    │ created_at      │            │
│ updated_at      │    └─────────────────┘            │
└─────────────────┘                                    │
                                                       │
┌─────────────────┐    ┌─────────────────┐            │
│    rapatlog     │    │    editlog      │            │
│-----------------│    │-----------------│            │
│ id (PK)         │    │ id (PK)         │            │
│ id_rapat        │    │ id_rapat        │            │
│ nama_rapat      │    │ field_name      │            │
│ deleted_by      │    │ old_value       │            │
│ deleted_at      │    │ new_value       │            │
└─────────────────┘    │ edited_by       │            │
                       │ edited_at       │            │
                       └─────────────────┘            │
                                                       ▼
                                            Polymorphic Relationships
```

---

## 📊 Model Details

### 1. RapatModel (Central Meeting Model)

#### File Location
```
app/RapatModel.php
```

#### Table Structure
```sql
CREATE TABLE `rapat` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `topik` text COLLATE utf8mb4_unicode_ci,
    `unit_kerja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `tanggal_rapat_start` date NOT NULL,
    `tanggal_rapat_end` date NOT NULL,
    `waktu_mulai_rapat` time NOT NULL,
    `waktu_selesai_rapat` time NOT NULL,
    `ruang_rapat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `jumlah_peserta` int(11) DEFAULT NULL,
    `use_zoom` tinyint(1) NOT NULL DEFAULT '0',
    `nohp_pj` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rapat_uid_unique` (`uid`)
);
```

#### Key Features
- **UID Generation**: 6-character unique identifier for public access
- **Polymorphic Relationships**: Supports both users and units as attendees
- **Audit Logging**: Automatic deletion and edit logging
- **Conflict Detection**: Built-in room booking overlap checking

#### Important Methods
```php
// UID Generation
public static function generateUniqueUid()
{
    do {
        $uid = Str::random(6);
    } while (self::where('uid', $uid)->exists());
    return $uid;
}

// Polymorphic Attendee Relationship
public function attendees()
{
    return $this->morphToMany(
        'App\Attendee',
        'attendee',
        'rapat_user',
        'rapat_id',
        'attendee_id'
    );
}

// Zoom Meetings Relationship
public function zoomMeetings()
{
    return $this->hasMany('App\ZoomModel', 'rapat', 'id');
}

// Documentation Relationship
public function documentation()
{
    return $this->hasMany('App\NotulensiModel', 'rapat_id', 'id');
}

// Conflict Detection
public static function checkConflict($tanggal, $waktuMulai, $waktuSelesai, $ruang, $excludeId = null)
{
    $query = self::where('ruang_rapat', $ruang)
               ->where('tanggal_rapat_start', '<=', $tanggal)
               ->where('tanggal_rapat_end', '>=', $tanggal)
               ->where(function($q) use ($waktuMulai, $waktuSelesai) {
                   $q->whereBetween('waktu_mulai_rapat', [$waktuMulai, $waktuSelesai])
                     ->orWhereBetween('waktu_selesai_rapat', [$waktuMulai, $waktuSelesai])
                     ->orWhere(function($sub) use ($waktuMulai, $waktuSelesai) {
                         $sub->where('waktu_mulai_rapat', '<=', $waktuMulai)
                             ->where('waktu_selesai_rapat', '>=', $waktuSelesai);
                     });
               });
    
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }
    
    return $query->exists();
}
```

#### Business Logic
- **Meeting Creation**: Automatic UID generation and conflict checking
- **Attendee Management**: Polymorphic support for users and organizational units
- **Deletion Logging**: Automatic logging to `rapatlog` table
- **Edit Tracking**: Changes logged to `editlog` table

---

### 2. UserModel (User Management)

#### File Location
```
app/UserModel.php
```

#### Table Structure
```sql
CREATE TABLE `users` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nip` varchar(255) COLLATE utf8mb_unicode_ci DEFAULT NULL,
    `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `unit_kerja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `level` int(11) NOT NULL DEFAULT '1',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_username_unique` (`username`)
);
```

#### Key Features
- **Authentication**: MD5 password hashing (legacy - should be updated)
- **Role Management**: Level-based permissions (1 = User, 2 = Admin)
- **Unit Assignment**: Many-to-many relationship with organizational units
- **Status Management**: Active/inactive user status

#### Important Relationships
```php
// Unit Kerja Assignment (Many-to-Many)
public function unitKerja()
{
    return $this->belongsToMany(
        'App\UnitKerjaModel',
        'unit_kerja_user',
        'user_model_id',
        'unit_kerja_model_id'
    )->withPivot('tahun');
}

// Meetings as Attendee (Polymorphic)
public function meetings()
{
    return $this->morphToMany(
        'App\RapatModel',
        'attendee',
        'rapat_user',
        'attendee_id',
        'rapat_id'
    );
}

// Created Meetings
public function createdMeetings()
{
    return $this->hasMany('App\RapatModel', 'created_by', 'id');
}

// Uploaded Documentation
public function uploadedDocumentation()
{
    return $this->hasMany('App\NotulensiModel', 'uploaded_by', 'id');
}
```

#### Business Logic
- **Unit Assignment**: Users can be assigned to multiple units per year
- **Meeting Access**: Level-based and unit-based access control
- **Authentication**: Custom MD5-based authentication system

---

### 3. UnitKerjaModel (Organizational Units)

#### File Location
```
app/UnitKerjaModel.php
```

#### Table Structure
```sql
CREATE TABLE `unit_kerja` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `singkatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `class_bg` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `tahun` int(11) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

#### Key Features
- **Year-based Data**: Units can have different configurations per year
- **Color Coding**: `class_bg` for UI styling
- **Abbreviation**: `singkatan` for short display names

#### Important Relationships
```php
// User Assignment (Many-to-Many)
public function users()
{
    return $this->belongsToMany(
        'App\UserModel',
        'unit_kerja_user',
        'unit_kerja_model_id',
        'user_model_id'
    )->withPivot('tahun');
}

// Meetings as Attendee (Polymorphic)
public function meetings()
{
    return $this->morphToMany(
        'App\RapatModel',
        'attendee',
        'rapat_user',
        'attendee_id',
        'rapat_id'
    );
}
```

---

### 4. ZoomModel (Zoom Meeting Integration)

#### File Location
```
app/ZoomModel.php
```

#### Table Structure
```sql
CREATE TABLE `zoom` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `rapat` int(11) NOT NULL,
    `tanggal_zoom` date NOT NULL,
    `zoom_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `zoom_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `zoom_link` text COLLATE utf8mb4_unicode_ci,
    `host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

#### Key Features
- **Meeting Management**: Complete Zoom meeting lifecycle
- **Host Assignment**: Dedicated host for each Zoom meeting
- **Link Generation**: Automatic meeting link creation
- **Password Management**: Secure password handling

#### Important Relationships
```php
// Parent Meeting
public function meeting()
{
    return $this->belongsTo('App\RapatModel', 'rapat', 'id');
}

// Host User (if host is a user ID)
public function hostUser()
{
    return $this->belongsTo('App\UserModel', 'host', 'id');
}
```

---

### 5. NotulensiModel (Meeting Documentation)

#### File Location
```
app/NotulensiModel.php
```

#### Table Structure
```sql
CREATE TABLE `notulensi` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `rapat_id` int(11) NOT NULL,
    `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `uploaded_by` int(11) NOT NULL,
    `uploaded_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

#### Key Features
- **File Management**: Upload and organization of meeting documents
- **Access Control**: Integration with meeting permissions
- **File Naming**: Automatic naming conventions
- **Download Tracking**: Access logging capabilities

#### Important Relationships
```php
// Parent Meeting
public function meeting()
{
    return $this->belongsTo('App\RapatModel', 'rapat_id', 'id');
}

// Uploader
public function uploader()
{
    return $this->belongsTo('App\UserModel', 'uploaded_by', 'id');
}
```

#### File Naming Convention
```php
// Format: {MeetingName}_{Date}_{Timestamp}_{Random}.{ext}
// Example: "Rapat_Bulanan_2024-01-15_1705312345_r1234.pdf"

public static function generateFileName($meetingName, $originalName)
{
    $timestamp = time();
    $random = rand(1000, 9999);
    $date = date('Y-m-d');
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    
    $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', $meetingName);
    
    return "{$cleanName}_{$date}_{$timestamp}_r{$random}.{$extension}";
}
```

---

### 6. RuangModel (Meeting Rooms)

#### File Location
```
app/RuangModel.php
```

#### Table Structure
```sql
CREATE TABLE `ruang` (
    `id_ruang` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nama_ruang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `kapasitas` int(11) DEFAULT NULL,
    `visible_ruang` tinyint(1) NOT NULL DEFAULT '1',
    `deskripsi` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id_ruang`)
);
```

#### Key Features
- **Capacity Management**: Room capacity tracking
- **Visibility Control**: Show/hide rooms from booking
- **Description Support**: Detailed room information

#### Important Relationships
```php
// Meetings in this Room
public function meetings()
{
    return $this->hasMany('App\RapatModel', 'ruang_rapat', 'nama_ruang');
}
```

---

### 7. ScheduleLogModel (Notification Logging)

#### File Location
```
app/ScheduleLogModel.php
```

#### Table Structure
```sql
CREATE TABLE `schedule_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `rapat_id` int(11) DEFAULT NULL,
    `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

#### Key Features
- **Notification Tracking**: Log all WhatsApp notifications
- **Status Monitoring**: Track delivery status
- **Error Handling**: Log failed notifications

#### Important Relationships
```php
// Related Meeting
public function meeting()
{
    return $this->belongsTo('App\RapatModel', 'rapat_id', 'id');
}
```

---

## 🔄 Polymorphic Relationships

### Attendee System

#### Polymorphic Table Structure
```sql
CREATE TABLE `rapat_user` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `rapat_id` int(11) NOT NULL,
    `attendee_id` int(11) NOT NULL,
    `attendee_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

#### Relationship Implementation
```php
// In RapatModel
public function attendees()
{
    return $this->morphToMany(
        'App\Attendee',
        'attendee',
        'rapat_user',
        'rapat_id',
        'attendee_id'
    );
}

// In UserModel
public function meetings()
{
    return $this->morphToMany(
        'App\RapatModel',
        'attendee',
        'rapat_user',
        'attendee_id',
        'rapat_id'
    );
}

// In UnitKerjaModel
public function meetings()
{
    return $this->morphToMany(
        'App\RapatModel',
        'attendee',
        'rapat_user',
        'attendee_id',
        'rapat_id'
    );
}
```

#### Usage Examples
```php
// Get all attendees for a meeting
$meeting = RapatModel::find(1);
$attendees = $meeting->attendees;

// Add user as attendee
$meeting->attendees()->attach($userId, [
    'attendee_type' => 'App\UserModel'
]);

// Add unit as attendee
$meeting->attendees()->attach($unitId, [
    'attendee_type' => 'App\UnitKerjaModel'
]);

// Get meetings for a user
$user = UserModel::find(1);
$meetings = $user->meetings;

// Get meetings for a unit
$unit = UnitKerjaModel::find(1);
$meetings = $unit->meetings;
```

---

## 🔍 Business Logic & Validation

### Conflict Detection Algorithm

#### Room Booking Conflict
```php
public static function checkRoomConflict(
    $tanggal, 
    $waktuMulai, 
    $waktuSelesai, 
    $ruang, 
    $excludeId = null
) {
    $query = self::where('ruang_rapat', $ruang)
        ->whereDate('tanggal_rapat_start', '<=', $tanggal)
        ->whereDate('tanggal_rapat_end', '>=', $tanggal)
        ->where(function($q) use ($waktuMulai, $waktuSelesai) {
            // Check for time overlap
            $q->where(function($sub) use ($waktuMulai, $waktuSelesai) {
                // Meeting starts during existing meeting
                $sub->where('waktu_mulai_rapat', '<=', $waktuMulai)
                    ->where('waktu_selesai_rapat', '>', $waktuMulai);
            })->orWhere(function($sub) use ($waktuMulai, $waktuSelesai) {
                // Meeting ends during existing meeting
                $sub->where('waktu_mulai_rapat', '<', $waktuSelesai)
                    ->where('waktu_selesai_rapat', '>=', $waktuSelesai);
            })->orWhere(function($sub) use ($waktuMulai, $waktuSelesai) {
                // Meeting completely contains existing meeting
                $sub->where('waktu_mulai_rapat', '>=', $waktuMulai)
                    ->where('waktu_selesai_rapat', '<=', $waktuSelesai);
            });
        });
    
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }
    
    return $query->exists();
}
```

### UID Generation System

#### Collision Detection
```php
public static function generateUniqueUid()
{
    $maxAttempts = 100;
    $attempts = 0;
    
    do {
        $uid = Str::upper(Str::random(6));
        $exists = self::where('uid', $uid)->exists();
        $attempts++;
        
        if ($attempts >= $maxAttempts) {
            throw new Exception('Unable to generate unique UID after ' . $maxAttempts . ' attempts');
        }
    } while ($exists);
    
    return $uid;
}
```

### Audit Logging System

#### Deletion Logging
```php
// In RapatModel boot() method
protected static function boot()
{
    parent::boot();
    
    static::deleting(function ($meeting) {
        // Log to rapatlog table
        DB::table('rapatlog')->insert([
            'id_rapat' => $meeting->id,
            'nama_rapat' => $meeting->nama,
            'deleted_by' => session('user_id'),
            'deleted_at' => now()
        ]);
    });
}
```

#### Edit Logging
```php
public static function logEdit($meetingId, $fieldName, $oldValue, $newValue)
{
    DB::table('editlog')->insert([
        'id_rapat' => $meetingId,
        'field_name' => $fieldName,
        'old_value' => $oldValue,
        'new_value' => $newValue,
        'edited_by' => session('user_id'),
        'edited_at' => now()
    ]);
}
```

---

## 📊 Database Queries & Scopes

### Common Query Patterns

#### User-Specific Meetings
```php
// Get meetings for current user (including unit assignments)
public function getUserMeetings($userId)
{
    $user = UserModel::find($userId);
    $unitIds = $user->unitKerja()->wherePivot('tahun', date('Y'))->pluck('id')->toArray();
    
    return RapatModel::select('rapat.*')
        ->join('rapat_user', 'rapat_user.rapat_id', '=', 'rapat.id')
        ->where(function($query) use ($userId, $unitIds) {
            $query->where(function($q) use ($userId) {
                $q->where('rapat_user.attendee_type', 'App\UserModel')
                  ->where('rapat_user.attendee_id', $userId);
            });
            
            if (!empty($unitIds)) {
                $query->orWhere(function($q) use ($unitIds) {
                    $q->where('rapat_user.attendee_type', 'App\UnitKerjaModel')
                      ->whereIn('rapat_user.attendee_id', $unitIds);
                });
            }
        })
        ->distinct()
        ->get();
}
```

#### Today's Meetings by Room
```php
public function getTodayMeetingsByRoom()
{
    $today = date('Y-m-d');
    $rooms = RuangModel::where('visible_ruang', 1)->get();
    
    $meetingsByRoom = [];
    
    foreach ($rooms as $room) {
        $meetings = RapatModel::where('ruang_rapat', $room->nama_ruang)
            ->whereDate('tanggal_rapat_start', '<=', $today)
            ->whereDate('tanggal_rapat_end', '>=', $today)
            ->orderBy('waktu_mulai_rapat')
            ->get();
            
        $meetingsByRoom[$room->id_ruang] = $meetings;
    }
    
    return $meetingsByRoom;
}
```

#### Meeting Statistics
```php
public function getMeetingStatistics($startDate, $endDate)
{
    return [
        'total_meetings' => RapatModel::whereBetween('tanggal_rapat_start', [$startDate, $endDate])->count(),
        'zoom_meetings' => RapatModel::where('use_zoom', 1)->whereBetween('tanggal_rapat_start', [$startDate, $endDate])->count(),
        'physical_meetings' => RapatModel::where('use_zoom', 0)->whereBetween('tanggal_rapat_start', [$startDate, $endDate])->count(),
        'by_room' => RapatModel::select('ruang_rapat', DB::raw('count(*) as count'))
            ->whereBetween('tanggal_rapat_start', [$startDate, $endDate])
            ->groupBy('ruang_rapat')
            ->get(),
        'by_unit' => RapatModel::select('unit_kerja', DB::raw('count(*) as count'))
            ->whereBetween('tanggal_rapat_start', [$startDate, $endDate])
            ->groupBy('unit_kerja')
            ->get()
    ];
}
```

---

## 🔧 Model Events & Observers

### Automated Processes

#### Meeting Creation Events
```php
// In RapatModel boot() method
static::created(function ($meeting) {
    // Generate UID if not exists
    if (!$meeting->uid) {
        $meeting->uid = self::generateUniqueUid();
        $meeting->save();
    }
    
    // Send notification if Zoom requested
    if ($meeting->use_zoom) {
        dispatch(new App\Jobs\NotifWa(
            $meeting->nohp_pj,
            "Zoom meeting requested for: {$meeting->nama}"
        ));
    }
});
```

#### Meeting Update Events
```php
static::updated(function ($meeting) {
    // Log changes
    $changes = $meeting->getDirty();
    
    foreach ($changes as $field => $newValue) {
        $oldValue = $meeting->getOriginal($field);
        
        if ($oldValue !== $newValue) {
            self::logEdit($meeting->id, $field, $oldValue, $newValue);
        }
    }
    
    // Check for room change and notify if needed
    if (isset($changes['ruang_rapat'])) {
        // Notify attendees about room change
        dispatch(new App\Jobs\NotifWa(
            $meeting->nohp_pj,
            "Room changed for {$meeting->nama}: {$meeting->ruang_rapat}"
        ));
    }
});
```

---

## 🚀 Performance Optimization

### Database Indexing

#### Recommended Indexes
```sql
-- Meetings table
ALTER TABLE `rapat` ADD INDEX `idx_date_range` (`tanggal_rapat_start`, `tanggal_rapat_end`);
ALTER TABLE `rapat` ADD INDEX `idx_room_date` (`ruang_rapat`, `tanggal_rapat_start`);
ALTER TABLE `rapat` ADD INDEX `idx_created_by` (`created_by`);
ALTER TABLE `rapat` ADD INDEX `idx_use_zoom` (`use_zoom`);

-- Rapat user polymorphic table
ALTER TABLE `rapat_user` ADD INDEX `idx_rapat_id` (`rapat_id`);
ALTER TABLE `rapat_user` ADD INDEX `idx_attendee` (`attendee_id`, `attendee_type`);

-- Users table
ALTER TABLE `users` ADD INDEX `idx_level_active` (`level`, `is_active`);
ALTER TABLE `users` ADD INDEX `idx_unit_kerja` (`unit_kerja`);

-- Unit kerja user pivot
ALTER TABLE `unit_kerja_user` ADD INDEX `idx_user_year` (`user_model_id`, `tahun`);
ALTER TABLE `unit_kerja_user` ADD INDEX `idx_unit_year` (`unit_kerja_model_id`, `tahun`);

-- Zoom meetings
ALTER TABLE `zoom` ADD INDEX `idx_rapat` (`rapat`);
ALTER TABLE `zoom` ADD INDEX `idx_tanggal_zoom` (`tanggal_zoom`);

-- Documentation
ALTER TABLE `notulensi` ADD INDEX `idx_rapat_id` (`rapat_id`);
ALTER TABLE `notulensi` ADD INDEX `idx_uploaded_by` (`uploaded_by`);
```

### Query Optimization

#### Eager Loading
```php
// Instead of N+1 queries
$meetings = RapatModel::all();
foreach ($meetings as $meeting) {
    $meeting->attendees; // This creates N+1 problem
}

// Use eager loading
$meetings = RapatModel::with('attendees', 'zoomMeetings', 'documentation')->get();
```

#### Efficient Attendee Queries
```php
// Get meetings with attendee count
$meetings = RapatModel::select('rapat.*', DB::raw('COUNT(rapat_user.attendee_id) as attendee_count'))
    ->leftJoin('rapat_user', 'rapat_user.rapat_id', '=', 'rapat.id')
    ->groupBy('rapat.id')
    ->get();
```

---

## 🔒 Security Considerations

### Data Validation

#### Model-Level Validation
```php
// In RapatModel
public static $rules = [
    'nama' => 'required|string|max:255',
    'topik' => 'nullable|string',
    'tanggal_rapat_start' => 'required|date|after_or_equal:today',
    'tanggal_rapat_end' => 'required|date|after_or_equal:tanggal_rapat_start',
    'waktu_mulai_rapat' => 'required|date_format:H:i',
    'waktu_selesai_rapat' => 'required|date_format:H:i|after:waktu_mulai_rapat',
    'ruang_rapat' => 'required|string',
    'jumlah_peserta' => 'nullable|integer|min:1',
    'use_zoom' => 'boolean',
    'nohp_pj' => 'nullable|string|max:20'
];

public static function validateRules($data)
{
    return Validator::make($data, self::$rules);
}
```

### SQL Injection Prevention

#### Parameter Binding
```php
// Safe parameter binding
$conflicts = DB::select('
    SELECT COUNT(*) as count 
    FROM rapat 
    WHERE ruang_rapat = ? 
    AND tanggal_rapat_start <= ? 
    AND tanggal_rapat_end >= ? 
    AND (
        (waktu_mulai_rapat <= ? AND waktu_selesai_rapat > ?) OR
        (waktu_mulai_rapat < ? AND waktu_selesai_rapat >= ?) OR
        (waktu_mulai_rapat >= ? AND waktu_selesai_rapat <= ?)
    )
    AND id != ?
', [
    $room, 
    $date, 
    $date, 
    $startTime, $startTime,
    $endTime, $endTime,
    $startTime, $endTime,
    $excludeId
]);
```

---

## 📝 Migration Files

### Key Migrations

#### Create Rapat Table
```php
// database/migrations/create_rapat_table.php
Schema::create('rapat', function (Blueprint $table) {
    $table->increments('id');
    $table->string('uid', 6)->unique();
    $table->string('nama');
    $table->text('topik')->nullable();
    $table->string('unit_kerja')->nullable();
    $table->date('tanggal_rapat_start');
    $table->date('tanggal_rapat_end');
    $table->time('waktu_mulai_rapat');
    $table->time('waktu_selesai_rapat');
    $table->string('ruang_rapat');
    $table->integer('jumlah_peserta')->nullable();
    $table->boolean('use_zoom')->default(false);
    $table->string('nohp_pj', 20)->nullable();
    $table->integer('created_by');
    $table->timestamps();
});
```

#### Create Polymorphic Rapat User Table
```php
// database/migrations/create_rapat_user_table.php
Schema::create('rapat_user', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('rapat_id');
    $table->integer('attendee_id');
    $table->string('attendee_type');
    $table->timestamps();
    
    $table->index(['rapat_id']);
    $table->index(['attendee_id', 'attendee_type']);
});
```

#### Add UID to Rapat Table
```php
// database/migrations/add_uid_to_rapat_table.php
Schema::table('rapat', function (Blueprint $table) {
    $table->string('uid', 6)->unique()->after('id');
});
```

---

**Last Updated**: 2026-01-21  
**Database Version**: MySQL with Laravel 5.4  
**Document Maintainer**: BPS Kalsel IT Team