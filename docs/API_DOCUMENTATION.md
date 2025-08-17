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

### 1.1 User Registration
**POST** `/auth/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "country_code": "+62",
    "phone": "81234567890",
    "email": "john@example.com",
    "gender": "male"
}
```

**Field Requirements:**
- `name` (required): Nama lengkap user (min 2 karakter, hanya huruf, spasi, tanda hubung, apostrof, dan titik)
- `country_code` (required): Kode negara (default: +62, support multiple countries)
- `phone` (required): Nomor telepon (min 8 digit, max 15 digit, validasi menggunakan Laravel-Phone)
- `email` (optional): Email user (harus valid dan unik)
- `gender` (optional): Jenis kelamin (male/female)

**Response Success (201):**
```json
{
    "success": true,
    "message": "Registration successful! OTP has been sent to your WhatsApp for verification.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "country_code": "+62",
            "phone": "81234567890",
            "full_phone": "+6281234567890",
            "email": "john@example.com",
            "gender": "male"
        },
        "otp_info": {
            "phone": "+6281234567890",
            "expires_in": 600,
            "expires_at": "2025-01-20T10:30:00.000000Z",
            "delivery_method": "whatsapp"
        },
        "next_step": "Verify OTP using /api/auth/verify-otp endpoint to complete authentication."
    }
}
```

**Response Error (409):**
```json
{
    "success": false,
    "message": "User with this phone number already exists."
}
```

**Response Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["Nama wajib diisi."],
        "country_code": ["Kode negara wajib diisi."],
        "phone": ["Format nomor telepon tidak valid untuk kode negara yang dipilih."],
        "email": ["Format email tidak valid."],
        "gender": ["Jenis kelamin harus male atau female."]
    }
}
```

### 1.2 Check User Status
**POST** `/auth/check-user-status`

**Request Body:**
```json
{
    "phone": "81234567890"
}
```

**Response Success (200) - Existing User:**
```json
{
    "success": true,
    "user_status": "existing",
    "data": {
        "user_id": 1,
        "name": "John Doe",
        "country_code": "+62",
        "phone": "81234567890",
        "full_phone": "+6281234567890",
        "email": "john@example.com",
        "gender": "male",
        "registered_at": "2025-01-15T10:00:00.000000Z"
    },
    "message": "User already registered",
    "next_step": "Request OTP for login using /api/auth/send-otp endpoint"
}
```

**Response Success (200) - New User:**
```json
{
    "success": true,
    "user_status": "new",
    "data": {
        "phone": "81234567890",
        "message": "Phone number not registered"
    },
    "message": "Phone number not registered",
    "next_step": "Register new account using /api/auth/register endpoint"
}
```

### 1.3 Send OTP
**POST** `/auth/send-otp`

**Request Body:**
```json
{
    "phone": "81234567890"
}
```

**Response Success (200) - For Existing User:**
```json
{
    "success": true,
    "message": "OTP sent successfully via WhatsApp for login",
    "data": {
        "phone": "+6281234567890",
        "expires_in": 600,
        "expires_at": "2025-01-20T10:30:00.000000Z",
        "delivery_method": "whatsapp"
    },
    "user_status": "existing",
    "next_step": "Verify OTP using /api/auth/verify-otp endpoint"
}
```

**Response Error (404) - For Unregistered User:**
```json
{
    "success": false,
    "message": "Phone number not registered. Please register first.",
    "code": 404,
    "user_status": "not_registered",
    "action_required": "redirect_to_registration",
    "data": {
        "phone": "81234567890",
        "message": "This phone number is not registered in our system"
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

**Response Error (429):**
```json
{
    "success": false,
    "message": "OTP already sent. Please wait before requesting another."
}
```

### 1.3 Verify OTP
**POST** `/auth/verify-otp`

**Request Body:**
```json
{
    "phone": "81234567890",
    "otp": "123456",
    "name": "John Doe",
    "email": "john@example.com",
    "gender": "male"
}
```

**Field Requirements:**
- `phone` (required): Nomor telepon yang digunakan untuk registrasi
- `otp` (required): Kode OTP 6 digit yang diterima via WhatsApp
- `name` (optional): Nama lengkap (jika belum terdaftar)
- `email` (optional): Email (jika belum terdaftar)
- `gender` (optional): Jenis kelamin (jika belum terdaftar)

**Response Success (200):**
```json
{
    "success": true,
    "message": "Authentication successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "country_code": "+62",
            "phone": "81234567890",
            "full_phone": "+6281234567890",
            "email": "john@example.com",
            "gender": "male"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

**Response Error (401):**
```json
{
    "success": false,
    "message": "Invalid or expired OTP code"
}
```

**Response Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "otp": ["The otp field is required."]
    }
}
```

### 1.4 Resend OTP
**POST** `/auth/resend-otp`

**Request Body:**
```json
{
    "phone": "81234567890"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "OTP sent successfully via WhatsApp",
    "data": {
        "phone": "+6281234567890",
        "expires_in": 600,
        "expires_at": "2025-01-20T10:30:00.000000Z",
        "delivery_method": "whatsapp"
    }
}
```

**Response Error (429):**
```json
{
    "success": false,
    "message": "Please wait 2 minutes before requesting another OTP"
}
```

### 1.5 Get Profile (Protected)
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
        "country_code": "+62",
        "phone": "81234567890",
        "full_phone": "+6281234567890",
        "email": "john@example.com",
        "gender": "male",
        "created_at": "2025-08-16T10:00:00Z"
    }
}
```

### 1.6 Update Profile (Protected)
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

### 1.7 Activity History (Protected)
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
            "contact_person": "Ustadz Ahmad - +628123456789",
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
        "contact_person": "Ustadz Ahmad - +628123456789",
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

**Common Validation Errors:**
- `country_code`: ["Kode negara wajib diisi.", "Kode negara tidak valid."]
- `phone`: ["Format nomor telepon tidak valid untuk kode negara yang dipilih."]
- `name`: ["Nama wajib diisi.", "Nama minimal 2 karakter."]
- `email`: ["Format email tidak valid."]
- `gender`: ["Jenis kelamin harus male atau female."]

### 6.2 Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### 6.3 User Not Registered Error (404)
```json
{
    "success": false,
    "message": "Phone number not registered. Please register first.",
    "code": 404,
    "user_status": "not_registered",
    "action_required": "redirect_to_registration",
    "data": {
        "phone": "81234567890",
        "message": "This phone number is not registered in our system"
    }
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

## 9. Phone Number Formatting & Laravel-Phone Integration

### 9.1 Phone Number Format
Sistem menggunakan package **Laravel-Phone** untuk validasi dan formatting nomor telepon internasional.

**Format Input:**
- **User Input**: `+62839999453` atau `0839999453`
- **Stored in Database**: 
  - `country_code`: `+62`
  - `phone`: `8399999453`
- **Display Format**: `+62839999453` (menggunakan `full_phone` attribute)
- **WhatsApp Service Format**: `62839999453` (tanpa `+`, menggunakan `whatsapp_phone` attribute)

### 9.2 Supported Country Codes
Sistem mendukung multiple country codes dengan default `+62` (Indonesia):

**Primary Countries:**
- `+62` - Indonesia (default)
- `+1` - United States/Canada
- `+44` - United Kingdom
- `+81` - Japan
- `+86` - China
- `+91` - India

**Additional Countries:**
- `+33` - France, `+49` - Germany, `+39` - Italy, `+34` - Spain
- `+61` - Australia, `+7` - Russia, `+55` - Brazil
- `+31` - Netherlands, `+32` - Belgium, `+46` - Sweden
- `+47` - Norway, `+48` - Poland, `+52` - Mexico
- Dan 50+ negara lainnya

### 9.3 Phone Validation Rules
**Validation menggunakan Laravel-Phone:**
- `phone:country_code` - Validasi format nomor sesuai kode negara
- `min:8` - Minimal 8 digit
- `max:15` - Maksimal 15 digit
- Auto-formatting dan cleaning input

**Contoh Validasi:**
```php
'phone' => [
    'required',
    'string',
    'phone:country_code', // Laravel-Phone validation
    'min:8',
    'max:15',
]
```

### 9.4 User Model Attributes
**Accessor Methods:**
- `$user->full_phone` → `+62839999453`
- `$user->whatsapp_phone` → `62839999453`

**Mutator Methods:**
- `setPhoneAttribute()` - Auto-clean phone number (remove non-digits)

### 9.5 WhatsApp Integration
**Format untuk Service WA:**
- **Input**: `+62839999453`
- **Process**: Remove `+` dari country_code + phone
- **Output**: `62839999453`

**Contoh Penggunaan:**
```php
$formattedPhone = $user->whatsapp_phone; // 62839999453
$sent = $this->waMasbro->sendTextMessage($formattedPhone, $message);
```

---

## 10. Implementation Notes

### 10.1 Authentication Flow
1. User input phone number → Call `/auth/send-otp`
2. **If user not registered**: Frontend receives 404 response with `action_required: "redirect_to_registration"` → Redirect user to registration page
3. **If user registered**: OTP sent via WhatsApp → User input OTP → Call `/auth/verify-otp`
4. Save token from response
5. Use token in Authorization header for protected endpoints

### 10.2 User Registration Flow
1. User input phone number → Call `/auth/check-user-status` (optional, for checking existing user)
2. User fills registration form → Call `/auth/register`
3. OTP automatically sent via WhatsApp after successful registration
4. User verifies OTP → Call `/auth/verify-otp`
5. User authenticated and redirected to main application

### 10.3 Event Registration Flow
1. User browse events → Call `/events`
2. User select event → Call `/events/{id}`
3. User register → Call `/event-registrations` (POST)
4. User check registration → Call `/event-registrations/user/{phone}`

### 10.4 E-book Reading Flow
1. User browse e-books → Call `/ebooks`
2. User select e-book → Call `/ebooks/{id}`
3. User read/download → Call `/ebooks/{id}/interact`

### 10.5 Donation Information
1. Get donation settings → Call `/donation-settings/active`
2. Display bank information and notes to user

### 10.6 Error Handling
- Always check `success` field in response
- Handle validation errors (422) by displaying field-specific messages
- Handle user not registered errors (404) by redirecting to registration page
- Handle authentication errors (401) by redirecting to login
- Handle server errors (500) with user-friendly messages

---

## 11. Rate Limiting

API memiliki rate limiting untuk mencegah abuse:
- **Public endpoints**: 60 requests per minute per IP
- **Authentication endpoints**: 5 requests per minute per IP
- **Protected endpoints**: 1000 requests per minute per authenticated user

---

## 12. Testing

### 12.1 Postman Collection
Import file `Sesuai_Sunnah_API.postman_collection.json` ke Postman untuk testing.

### 12.2 Environment Variables
Set environment variables:
- `base_url`: Base URL API
- `auth_token`: Token setelah login berhasil

### 12.3 Test Data
Gunakan data dari seeder yang sudah dibuat:
- Phone: `81234567890` (tanpa country code, default +62)
- OTP: `123456` (untuk development)

---

## 13. Support

Untuk pertanyaan atau bantuan implementasi:
- Email: support@sesuaisunnah.org
- Documentation: https://docs.sesuaisunnah.org
- GitHub Issues: https://github.com/sesuaisunnah/apps/issues
