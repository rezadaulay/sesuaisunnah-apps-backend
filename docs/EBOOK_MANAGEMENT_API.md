# E-book Management API Documentation

## Overview

The E-book Management API provides comprehensive functionality for managing digital books, audiobooks, and user interactions in the Sesuai Sunnah Apps platform. This API supports CRUD operations for e-books, audiobook file management, user interaction tracking, and advanced filtering and search capabilities.

## Base URL

```
http://localhost:8000/api/ebooks
```

## Authentication

Currently, the API endpoints are public. Authentication middleware will be added in future phases for admin-only operations.

## Endpoints

### 1. List E-books

**GET** `/api/ebooks`

Retrieves a paginated list of e-books with optional filtering and sorting.

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `price_type` | string | Filter by price: `free` or `paid` | all |
| `has_audiobook` | boolean | Filter by audiobook availability | all |
| `search` | string | Search in title and description | none |
| `sort_by` | string | Sort by: `title`, `price`, `popularity`, `created_at` | `created_at` |
| `sort_order` | string | Sort order: `asc` or `desc` | `desc` |
| `per_page` | integer | Items per page | `12` |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/ebooks?price_type=free&sort_by=title&sort_order=asc&per_page=10"
```

#### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Islamic Finance Guide",
      "description": "Comprehensive guide to Islamic financial principles",
      "cover_image": "ebooks/covers/1234567890_cover.jpg",
      "cover_image_url": "http://localhost:8000/storage/ebooks/covers/1234567890_cover.jpg",
      "price": 0,
      "price_formatted": "Free",
      "is_free": true,
      "file_url": "ebooks/files/1234567890_guide.pdf",
      "file_url_full": "http://localhost:8000/storage/ebooks/files/1234567890_guide.pdf",
      "file_extension": "pdf",
      "is_pdf": true,
      "is_epub": false,
      "human_file_size": "Unknown",
      "creator": {
        "id": 1,
        "name": "Admin User"
      },
      "has_audiobook": false,
      "audiobook_files_count": 0,
      "interactions_count": 5,
      "read_count": 3,
      "download_count": 2,
      "listen_count": 0,
      "total_interactions": 5,
      "created_at": "2025-08-16 10:00:00",
      "created_at_formatted": "16/08/2025 10:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 12,
    "total": 25
  }
}
```

### 2. Get Popular E-books

**GET** `/api/ebooks/popular`

Retrieves the most popular e-books based on user interactions.

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `limit` | integer | Maximum number of e-books to return | `10` |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/ebooks/popular?limit=5"
```

### 3. Get Free E-books

**GET** `/api/ebooks/free`

Retrieves only free e-books.

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `per_page` | integer | Items per page | `12` |

### 4. Get E-books with Audiobooks

**GET** `/api/ebooks/with-audiobook`

Retrieves e-books that have associated audiobook files.

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `per_page` | integer | Items per page | `12` |

### 5. Get E-book Details

**GET** `/api/ebooks/{id}`

Retrieves detailed information about a specific e-book.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/ebooks/1"
```

#### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Islamic Finance Guide",
    "description": "Comprehensive guide to Islamic financial principles",
    "cover_image": "ebooks/covers/1234567890_cover.jpg",
    "cover_image_url": "http://localhost:8000/storage/ebooks/covers/1234567890_cover.jpg",
    "price": 0,
    "price_formatted": "Free",
    "is_free": true,
    "file_url": "ebooks/files/1234567890_guide.pdf",
    "file_url_full": "http://localhost:8000/storage/ebooks/files/1234567890_guide.pdf",
    "file_extension": "pdf",
    "is_pdf": true,
    "is_epub": false,
    "human_file_size": "Unknown",
    "creator": {
      "id": 1,
      "name": "Admin User"
    },
    "has_audiobook": true,
    "audiobook_files_count": 3,
    "audiobook_files": [
      {
        "id": 1,
        "name": "Chapter 1 - Introduction",
        "display_name": "Chapter 1 - Introduction",
        "file_url": "ebooks/audiobooks/1/1234567890_1_chapter1.mp3",
        "file_url_full": "http://localhost:8000/storage/ebooks/audiobooks/1/1234567890_1_chapter1.mp3",
        "file_extension": "mp3",
        "order_number": 1,
        "is_mp3": true,
        "is_m4a": false,
        "is_aac": false,
        "human_duration": "Unknown"
      }
    ],
    "interactions_count": 5,
    "read_count": 3,
    "download_count": 2,
    "listen_count": 0,
    "total_interactions": 5,
    "recent_interactions": [
      {
        "id": 1,
        "action": "read",
        "action_text": "Read",
        "user": {
          "id": 2,
          "name": "John Doe"
        },
        "created_at": "2025-08-16 15:30:00",
        "created_at_formatted": "16/08/2025 15:30"
      }
    ],
    "created_at": "2025-08-16 10:00:00",
    "created_at_formatted": "16/08/2025 10:00",
    "updated_at": "2025-08-16 10:00:00",
    "updated_at_formatted": "16/08/2025 10:00"
  }
}
```

### 6. Create E-book

**POST** `/api/ebooks`

Creates a new e-book with optional cover image and audiobook files.

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | E-book title (max 200 characters) |
| `description` | string | No | E-book description |
| `cover_image` | file | No | Cover image (JPG, PNG, max 2MB) |
| `price` | numeric | Yes | Price in decimal (0 for free) |
| `ebook_file` | file | Yes | E-book file (PDF, EPUB, DOC, DOCX, max 50MB) |
| `audiobook_files.*` | file | No | Audio files (MP3, M4A, AAC, WAV, max 100MB each) |

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/ebooks" \
  -F "title=Islamic Finance Guide" \
  -F "description=Comprehensive guide to Islamic financial principles" \
  -F "price=0" \
  -F "ebook_file=@guide.pdf" \
  -F "cover_image=@cover.jpg" \
  -F "audiobook_files[]=@chapter1.mp3" \
  -F "audiobook_files[]=@chapter2.mp3"
```

#### Response

```json
{
  "success": true,
  "message": "Ebook created successfully",
  "data": {
    "id": 1,
    "title": "Islamic Finance Guide",
    "description": "Comprehensive guide to Islamic financial principles",
    "price": 0,
    "file_url": "ebooks/files/1234567890_guide.pdf",
    "creator": {
      "id": 1,
      "name": "Admin User"
    },
    "audiobook_files": [
      {
        "id": 1,
        "name": "chapter1",
        "file_url": "ebooks/audiobooks/1/1234567890_1_chapter1.mp3",
        "order_number": 1
      }
    ]
  }
}
```

### 7. Update E-book

**PUT** `/api/ebooks/{id}`

Updates an existing e-book.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | No | E-book title (max 200 characters) |
| `description` | string | No | E-book description |
| `cover_image` | file | No | New cover image (JPG, PNG, max 2MB) |
| `price` | numeric | No | New price in decimal |

#### Example Request

```bash
curl -X PUT "http://localhost:8000/api/ebooks/1" \
  -F "title=Updated Islamic Finance Guide" \
  -F "price=50000" \
  -F "_method=PUT"
```

### 8. Delete E-book

**DELETE** `/api/ebooks/{id}`

Deletes an e-book and all associated files.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Example Request

```bash
curl -X DELETE "http://localhost:8000/api/ebooks/1"
```

#### Response

```json
{
  "success": true,
  "message": "Ebook deleted successfully"
}
```

### 9. Record User Interaction

**POST** `/api/ebooks/{id}/interact`

Records a user interaction with an e-book (read, download, listen).

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `action` | string | Yes | Action type: `read`, `download`, or `listen` |
| `user_id` | integer | Yes | User ID |

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/ebooks/1/interact" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "read",
    "user_id": 2
  }'
```

#### Response

```json
{
  "success": true,
  "message": "Interaction recorded successfully"
}
```

### 10. Get E-book Statistics

**GET** `/api/ebooks/{id}/statistics`

Retrieves detailed statistics for an e-book.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/ebooks/1/statistics"
```

#### Response

```json
{
  "success": true,
  "data": {
    "total_interactions": 15,
    "read_count": 8,
    "download_count": 5,
    "listen_count": 2,
    "has_audiobook": true,
    "audiobook_files_count": 3,
    "recent_interactions": [
      {
        "user_name": "John Doe",
        "action": "read",
        "created_at": "2025-08-16 15:30:00"
      }
    ]
  }
}
```

### 11. Upload Audiobook Files

**POST** `/api/ebooks/{id}/audiobook/upload`

Uploads audiobook files to an existing e-book.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `audiobook_files.*` | file | Yes | Audio files (MP3, M4A, AAC, WAV, max 100MB each) |

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/ebooks/1/audiobook/upload" \
  -F "audiobook_files[]=@chapter3.mp3" \
  -F "audiobook_files[]=@chapter4.mp3"
```

#### Response

```json
{
  "success": true,
  "message": "2 audiobook file(s) uploaded successfully",
  "data": [
    {
      "id": 4,
      "name": "chapter3",
      "file_url": "ebooks/audiobooks/1/1234567890_3_chapter3.mp3",
      "order_number": 3
    }
  ]
}
```

### 12. Delete Audiobook File

**DELETE** `/api/ebooks/{id}/audiobook`

Deletes a specific audiobook file from an e-book.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | E-book ID |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `audiobook_file_id` | integer | Yes | Audiobook file ID to delete |

#### Example Request

```bash
curl -X DELETE "http://localhost:8000/api/ebooks/1/audiobook" \
  -H "Content-Type: application/json" \
  -d '{
    "audiobook_file_id": 4
  }'
```

## File Requirements

### E-book Files
- **Supported Formats**: PDF, EPUB, DOC, DOCX
- **Maximum Size**: 50MB
- **Storage Path**: `storage/app/public/ebooks/files/`

### Cover Images
- **Supported Formats**: JPG, JPEG, PNG
- **Maximum Size**: 2MB
- **Storage Path**: `storage/app/public/ebooks/covers/`

### Audiobook Files
- **Supported Formats**: MP3, M4A, AAC, WAV
- **Maximum Size**: 100MB per file
- **Storage Path**: `storage/app/public/ebooks/audiobooks/{ebook_id}/`

## Data Models

### Ebook Model
```php
{
  "id": integer,
  "title": string,
  "description": string,
  "cover_image": string|null,
  "price": decimal,
  "file_url": string,
  "created_by": integer,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### AudiobookFile Model
```php
{
  "id": integer,
  "ebook_id": integer,
  "name": string|null,
  "file_url": string,
  "order_number": integer,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### EbookInteraction Model
```php
{
  "id": integer,
  "ebook_id": integer,
  "user_id": integer,
  "action": enum('read', 'download', 'listen'),
  "created_at": timestamp,
  "updated_at": timestamp
}
```

## Error Handling

### Validation Errors (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "price": ["The price must be a number."],
    "ebook_file": ["The ebook file field is required."]
  }
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Ebook] 999"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error"
}
```

## Security Considerations

- File upload validation for type and size
- Path traversal protection
- Input sanitization
- Rate limiting (to be implemented)

## Future Enhancements

- Authentication and authorization middleware
- File compression and optimization
- CDN integration for file delivery
- Advanced search with full-text indexing
- User reading progress tracking
- Bookmarking and annotation features
- Social sharing and recommendations

## Testing

Run the e-book management tests:

```bash
php artisan test --filter=EbookManagementTest
```

**Results**: 22 tests passing ✅

## Support

For technical support or questions about the E-book Management API, please contact the development team.
