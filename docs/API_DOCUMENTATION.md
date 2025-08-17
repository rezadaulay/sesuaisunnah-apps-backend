# API Documentation - Sesuai Sunnah Apps

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
API menggunakan Laravel Sanctum untuk autentikasi. Setelah login berhasil, gunakan token Bearer di header Authorization.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 1. Authentication Endpoints

### 1.1 Send OTP
**POST** `/auth/send-otp`

**Request Body:**
```json
{
    "phone": "08123456789"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "OTP sent successfully",
    "data": {
        "phone": "08123456789",
        "expires_at": "2025-08-17T10:30:00Z"
    }
}
```

**Response Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "phone": ["The phone field is required."]
    }
}
```

### 1.2 Verify OTP
**POST** `/auth/verify-otp`

**Request Body:**
```json
{
    "phone": "08123456789",
    "otp_code": "123456"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "OTP verified successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "08123456789",
            "email": "john@example.com",
            "gender": "male"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### 1.3 Get Profile (Protected)
**GET** `/auth/profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "phone": "08123456789",
        "email": "john@example.com",
        "gender": "male",
        "created_at": "2025-08-16T10:00:00Z"
    }
}
```

### 1.4 Update Profile (Protected)
**PUT** `/auth/profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com"
}
```

### 1.5 Activity History (Protected)
**GET** `/auth/activity-history`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "action": "event_registration",
            "description": "Registered for Event: Kajian Rutin",
            "created_at": "2025-08-16T10:00:00Z"
        }
    ]
}
```

### 1.6 Logout (Protected)
**POST** `/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

---

## 2. Events Endpoints

### 2.1 Get All Events
**GET** `/events`

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status` (optional): Filter by status (draft, published, registration_open, registration_closed, event_closed)
- `requires_registration` (optional): Filter by registration requirement (true/false)
- `search` (optional): Search by title or description

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Kajian Rutin",
                "description": "Kajian rutin setiap minggu",
                "featured_image": "events/images/kajian-rutin.jpg",
                "event_date": "2025-08-20",
                "start_date": "2025-08-20T19:00:00Z",
                "end_date": "2025-08-20T21:00:00Z",
                "location": "Masjid Al-Ikhlas",
                "status": "registration_open",
                "requires_registration": true,
                "registration_opens_at": "2025-08-10T00:00:00Z",
                "registration_closes_at": "2025-08-19T23:59:59Z",
                "max_participants": 100,
                "current_participants": 45,
                "is_active": true,
                "is_featured": false,
                "created_at": "2025-08-16T10:00:00Z",
                "updated_at": "2025-08-16T10:00:00Z"
            }
        ],
        "total": 25,
        "per_page": 15,
        "last_page": 2
    }
}
```

### 2.2 Get Upcoming Events
**GET** `/events/upcoming`

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `limit` (optional): Limit results (default: 10)

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Kajian Rutin",
            "event_date": "2025-08-20",
            "start_date": "2025-08-20T19:00:00Z",
            "end_date": "2025-08-20T21:00:00Z",
            "location": "Masjid Al-Ikhlas",
            "status": "registration_open",
            "requires_registration": true,
            "featured_image": "events/images/kajian-rutin.jpg"
        }
    ]
}
```

### 2.3 Get Featured Events
**GET** `/events/featured`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "title": "Event Unggulan",
            "event_date": "2025-08-25",
            "featured_image": "events/images/event-unggulan.jpg",
            "is_featured": true
        }
    ]
}
```

### 2.4 Get Events with Documentation
**GET** `/events/with-documentation`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 3,
            "title": "Event dengan Dokumentasi",
            "event_date": "2025-08-15",
            "documentation_desc": "Event ini memiliki dokumentasi lengkap",
            "gallery_count": 15
        }
    ]
}
```

### 2.5 Get Single Event
**GET** `/events/{id}`

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Kajian Rutin",
        "description": "Kajian rutin setiap minggu",
        "featured_image": "events/images/kajian-rutin.jpg",
        "event_date": "2025-08-20",
        "start_date": "2025-08-20T19:00:00Z",
        "end_date": "2025-08-20T21:00:00Z",
        "location": "Masjid Al-Ikhlas",
        "status": "registration_open",
        "requires_registration": true,
        "registration_opens_at": "2025-08-10T00:00:00Z",
        "registration_closes_at": "2025-08-19T23:59:59Z",
        "max_participants": 100,
        "current_participants": 45,
        "is_active": true,
        "is_featured": false,
        "creator": {
            "id": 1,
            "name": "Admin"
        },
        "gallery": [
            {
                "id": 1,
                "photo_url": "event-gallery/photos/photo1.jpg",
                "description": "Foto kegiatan",
                "created_at": "2025-08-16T10:00:00Z"
            }
        ],
        "registrations_count": 45,
        "created_at": "2025-08-16T10:00:00Z",
        "updated_at": "2025-08-16T10:00:00Z"
    }
}
```

### 2.6 Get Event Documentation
**GET** `/events/{id}/documentation`

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "event": {
            "id": 1,
            "title": "Kajian Rutin"
        },
        "documentation_desc": "Event ini memiliki dokumentasi lengkap",
        "gallery": [
            {
                "id": 1,
                "photo_url": "event-gallery/photos/photo1.jpg",
                "description": "Foto kegiatan",
                "created_at": "2025-08-16T10:00:00Z"
            }
        ]
    }
}
```

---

## 3. Event Registration Endpoints

### 3.1 Register for Event
**POST** `/event-registrations`

**Request Body:**
```json
{
    "event_id": 1,
    "name": "John Doe",
    "phone": "08123456789",
    "gender": "male",
    "email": "john@example.com",
    "occupation": "Mahasiswa",
    "referral_source": "website"
}
```

**Response Success (201):**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "id": 1,
        "event_id": 1,
        "name": "John Doe",
        "phone": "08123456789",
        "gender": "male",
        "email": "john@example.com",
        "occupation": "Mahasiswa",
        "referral_source": "website",
        "status": "registered",
        "registered_at": "2025-08-16T10:00:00Z"
    }
}
```

### 3.2 Get User Registrations
**GET** `/event-registrations/user/{phone}`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "event": {
                "id": 1,
                "title": "Kajian Rutin",
                "event_date": "2025-08-20"
            },
            "status": "registered",
            "registered_at": "2025-08-16T10:00:00Z"
        }
    ]
}
```

### 3.3 Cancel Registration
**DELETE** `/event-registrations/cancel/{phone}/{eventId}`

**Response Success (200):**
```json
{
    "success": true,
    "message": "Registration cancelled successfully"
}
```

---

## 4. E-books Endpoints

### 4.1 Get All E-books
**GET** `/ebooks`

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `search` (optional): Search by title or description
- `price_type` (optional): Filter by price type (free/paid)

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Panduan Lengkap Shalat Fardhu",
                "description": "Buku panduan lengkap untuk mempelajari tata cara shalat fardhu",
                "cover_image": "ebooks/covers/shalat-fardhu.jpg",
                "price": "0.00",
                "file_url": "ebooks/shalat-fardhu.pdf",
                "is_free": true,
                "has_audiobook": false,
                "read_count": 150,
                "download_count": 75,
                "created_at": "2025-08-16T10:00:00Z"
            }
        ],
        "total": 10,
        "per_page": 15
    }
}
```

### 4.2 Get Popular E-books
**GET** `/ebooks/popular`

**Query Parameters:**
- `limit` (optional): Limit results (default: 10)

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Panduan Lengkap Shalat Fardhu",
            "cover_image": "ebooks/covers/shalat-fardhu.jpg",
            "total_interactions": 225
        }
    ]
}
```

### 4.3 Get Free E-books
**GET** `/ebooks/free`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Panduan Lengkap Shalat Fardhu",
            "cover_image": "ebooks/covers/shalat-fardhu.jpg",
            "price": "0.00"
        }
    ]
}
```

### 4.4 Get E-books with Audiobook
**GET** `/ebooks/with-audiobook`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "title": "Kumpulan Doa Harian Muslim",
            "cover_image": "ebooks/covers/doa-harian.jpg",
            "audiobook_files_count": 5
        }
    ]
}
```

### 4.5 Get Single E-book
**GET** `/ebooks/{id}`

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Panduan Lengkap Shalat Fardhu",
        "description": "Buku panduan lengkap untuk mempelajari tata cara shalat fardhu",
        "cover_image": "ebooks/covers/shalat-fardhu.jpg",
        "price": "0.00",
        "file_url": "ebooks/shalat-fardhu.pdf",
        "is_free": true,
        "has_audiobook": false,
        "creator": {
            "id": 1,
            "name": "Admin"
        },
        "audiobook_files": [],
        "read_count": 150,
        "download_count": 75,
        "listen_count": 0,
        "created_at": "2025-08-16T10:00:00Z"
    }
}
```

### 4.6 Get E-book Statistics
**GET** `/ebooks/{id}/statistics`

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "ebook_id": 1,
        "read_count": 150,
        "download_count": 75,
        "listen_count": 0,
        "total_interactions": 225,
        "interaction_trends": [
            {
                "date": "2025-08-16",
                "count": 25
            }
        ]
    }
}
```

### 4.7 Record E-book Interaction
**POST** `/ebooks/{id}/interact`

**Request Body:**
```json
{
    "action": "read",
    "user_id": 1
}
```

**Actions Available:**
- `read`: User membaca e-book
- `download`: User download e-book
- `listen`: User mendengarkan audiobook

---

## 5. Donation Settings Endpoints

### 5.1 Get All Donation Settings
**GET** `/donation-settings`

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "bank_name": "Bank Syariah Indonesia",
            "account_number": "1234567890",
            "account_name": "Yayasan Sesuai Sunnah",
            "swift_code": "BSINIDJA",
            "branch_name": "Cabang Jakarta Pusat",
            "donation_note": "Terima kasih atas donasi Anda...",
            "bank_transfer_note": "Mohon cantumkan nama donatur...",
            "minimum_donation": "10000.00",
            "is_active": true,
            "contact_person": "Ustadz Ahmad - 08123456789",
            "contact_email": "donasi@sesuaisunnah.org",
            "created_at": "2025-08-16T10:00:00Z"
        }
    ]
}
```

### 5.2 Get Active Donation Settings
**GET** `/donation-settings/active`

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "bank_name": "Bank Syariah Indonesia",
        "account_number": "1234567890",
        "account_name": "Yayasan Sesuai Sunnah",
        "swift_code": "BSINIDJA",
        "branch_name": "Cabang Jakarta Pusat",
        "donation_note": "Terima kasih atas donasi Anda...",
        "bank_transfer_note": "Mohon cantumkan nama donatur...",
        "minimum_donation": "10000.00",
        "is_active": true,
        "contact_person": "Ustadz Ahmad - 08123456789",
        "contact_email": "donasi@sesuaisunnah.org"
    }
}
```

---

## 6. Error Responses

### 6.1 Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### 6.2 Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### 6.3 Unauthorized Error (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### 6.4 Forbidden Error (403)
```json
{
    "success": false,
    "message": "Access denied"
}
```

### 6.5 Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error"
}
```

---

## 7. Pagination

Semua endpoint yang mendukung pagination menggunakan format response yang sama:

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "total": 100,
        "per_page": 15,
        "last_page": 7,
        "from": 1,
        "to": 15
    }
}
```

---

## 8. File URLs

Semua file (gambar, dokumen) menggunakan format URL:
```
https://your-domain.com/storage/{file_path}
```

**Contoh:**
- Event image: `https://your-domain.com/storage/events/images/kajian-rutin.jpg`
- E-book cover: `https://your-domain.com/storage/ebooks/covers/shalat-fardhu.jpg`
- Gallery photo: `https://your-domain.com/storage/event-gallery/photos/photo1.jpg`

---

## 9. Implementation Notes

### 9.1 Authentication Flow
1. User input phone number → Call `/auth/send-otp`
2. User input OTP → Call `/auth/verify-otp`
3. Save token from response
4. Use token in Authorization header for protected endpoints

### 9.2 Event Registration Flow
1. User browse events → Call `/events`
2. User select event → Call `/events/{id}`
3. User register → Call `/event-registrations` (POST)
4. User check registration → Call `/event-registrations/user/{phone}`

### 9.3 E-book Reading Flow
1. User browse e-books → Call `/ebooks`
2. User select e-book → Call `/ebooks/{id}`
3. User read/download → Call `/ebooks/{id}/interact`

### 9.4 Donation Information
1. Get donation settings → Call `/donation-settings/active`
2. Display bank information and notes to user

### 9.5 Error Handling
- Always check `success` field in response
- Handle validation errors (422) by displaying field-specific messages
- Handle authentication errors (401) by redirecting to login
- Handle server errors (500) with user-friendly messages

---

## 10. Rate Limiting

API memiliki rate limiting untuk mencegah abuse:
- **Public endpoints**: 60 requests per minute per IP
- **Authentication endpoints**: 5 requests per minute per IP
- **Protected endpoints**: 1000 requests per minute per authenticated user

---

## 11. Testing

### 11.1 Postman Collection
Import file `Sesuai_Sunnah_API.postman_collection.json` ke Postman untuk testing.

### 11.2 Environment Variables
Set environment variables:
- `base_url`: Base URL API
- `auth_token`: Token setelah login berhasil

### 11.3 Test Data
Gunakan data dari seeder yang sudah dibuat:
- Phone: `08123456789`
- OTP: `123456` (untuk development)

---

## 12. Support

Untuk pertanyaan atau bantuan implementasi:
- Email: support@sesuaisunnah.org
- Documentation: https://docs.sesuaisunnah.org
- GitHub Issues: https://github.com/sesuaisunnah/apps/issues
