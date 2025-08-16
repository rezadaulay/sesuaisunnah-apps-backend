# Event Documentation API Documentation

## Overview

The Event Documentation API provides comprehensive functionality for managing event documentation including photos, videos, and documents. This API allows users to upload, retrieve, update, and delete event documentation with proper validation and file management.

## Base URL

```
http://localhost:8000/api
```

## Authentication

Currently, the API endpoints are public. In future versions, authentication will be required for upload and management operations.

## Endpoints

### 1. Upload Event Documentation

Upload documentation files (photos, videos, documents) for a specific event.

**Endpoint:** `POST /events/{event}/documentation/upload`

**Parameters:**
- `event` (path): Event ID

**Request Body (multipart/form-data):**
- `files[]` (required): Array of files to upload
- `type` (required): Type of documentation - `photo`, `video`, or `document`
- `description` (optional): Description of the documentation

**File Requirements:**
- **Photos**: JPG, JPEG, PNG, GIF (max 10MB)
- **Videos**: MP4, MOV, AVI (max 10MB)
- **Documents**: PDF, DOC, DOCX (max 10MB)

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/events/1/documentation/upload" \
  -F "files[]=@photo1.jpg" \
  -F "files[]=@photo2.jpg" \
  -F "type=photo" \
  -F "description=Event opening ceremony photos"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "2 file(s) uploaded successfully",
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "photo_url": "events/1/documentation/1234567890_photo1.jpg",
      "type": "photo",
      "description": "Event opening ceremony photos",
      "file_size": 2048576,
      "mime_type": "image/jpeg",
      "created_at": "2025-08-16T15:44:38.000000Z",
      "updated_at": "2025-08-16T15:44:38.000000Z"
    }
  ]
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "files.0": ["The files.0 field is required."],
    "type": ["The type field is required."]
  }
}
```

### 2. Get Event Documentation

Retrieve documentation for a specific event with optional filtering by type.

**Endpoint:** `GET /events/{event}/documentation`

**Parameters:**
- `event` (path): Event ID
- `type` (query, optional): Filter by documentation type - `photo`, `video`, `document`, or `all` (default: `all`)

**Example Request:**
```bash
curl "http://localhost:8000/api/events/1/documentation?type=photo"
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "event": {
      "id": 1,
      "title": "Islamic Community Gathering",
      "event_date": "2025-08-20"
    },
    "documentation": [
      {
        "id": 1,
        "type": "photo",
        "file_url": "http://localhost:8000/storage/events/1/documentation/1234567890_photo1.jpg",
        "description": "Event opening ceremony photos",
        "file_size": 2048576,
        "mime_type": "image/jpeg",
        "uploaded_at": "2025-08-16 15:44:38"
      }
    ],
    "summary": {
      "total_files": 1,
      "photos_count": 1,
      "videos_count": 0,
      "documents_count": 0
    }
  }
}
```

### 3. Update Documentation Description

Update the description of a specific documentation item.

**Endpoint:** `PUT /events/{event}/documentation/description`

**Parameters:**
- `event` (path): Event ID

**Request Body:**
- `documentation_id` (required): ID of the documentation item
- `description` (required): New description (max 500 characters)

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/events/1/documentation/description" \
  -H "Content-Type: application/json" \
  -d '{
    "documentation_id": 1,
    "description": "Updated description for the photo"
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Description updated successfully",
  "data": {
    "id": 1,
    "description": "Updated description for the photo",
    "updated_at": "2025-08-16 15:44:38"
  }
}
```

### 4. Delete Event Documentation

Delete a specific documentation item from an event.

**Endpoint:** `DELETE /events/{event}/documentation`

**Parameters:**
- `event` (path): Event ID

**Request Body:**
- `documentation_id` (required): ID of the documentation item to delete

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/events/1/documentation" \
  -H "Content-Type: application/json" \
  -d '{
    "documentation_id": 1
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Documentation deleted successfully"
}
```

### 5. Get Events with Documentation Summary

Retrieve a list of events that have documentation with summary information.

**Endpoint:** `GET /events/with-documentation`

**Query Parameters:**
- `doc_type` (optional): Filter by documentation type - `photo`, `video`, `document`
- `per_page` (optional): Number of items per page (default: 12)

**Example Request:**
```bash
curl "http://localhost:8000/api/events/with-documentation?doc_type=photo&per_page=5"
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Islamic Community Gathering",
      "event_date": "2025-08-20",
      "creator": {
        "id": 1,
        "name": "Admin User"
      },
      "participants_count": 25,
      "documentation_summary": {
        "total_files": 5,
        "photos": 3,
        "videos": 1,
        "documents": 1
      },
      "has_documentation": true
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 12,
    "total": 1
  }
}
```

## Data Models

### EventGallery Model

```php
{
  "id": 1,
  "event_id": 1,
  "photo_url": "events/1/documentation/filename.jpg",
  "type": "photo", // photo, video, document
  "description": "Description of the file",
  "file_size": 2048576,
  "mime_type": "image/jpeg",
  "created_at": "2025-08-16T15:44:38.000000Z",
  "updated_at": "2025-08-16T15:44:38.000000Z"
}
```

### Computed Attributes

The EventGallery model provides several computed attributes:

- `full_photo_url`: Complete URL to the file
- `human_file_size`: Human-readable file size (e.g., "2.0 MB")
- `is_image`: Boolean indicating if file is an image
- `is_video`: Boolean indicating if file is a video
- `is_document`: Boolean indicating if file is a document

## File Storage

Files are stored in the `storage/app/public/events/{event_id}/documentation/` directory and are accessible via the `/storage/` URL path.

## Error Handling

The API returns consistent error responses with appropriate HTTP status codes:

- **400 Bad Request**: Invalid request data
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server errors

## Rate Limiting

Currently, no rate limiting is implemented. This will be added in future versions for production use.

## Security Considerations

1. **File Validation**: All uploaded files are validated for type and size
2. **Path Traversal Protection**: File paths are sanitized to prevent directory traversal attacks
3. **MIME Type Validation**: Files are validated against allowed MIME types
4. **File Size Limits**: Maximum file size is enforced (10MB)

## Future Enhancements

1. **Authentication & Authorization**: Require authentication for upload/delete operations
2. **File Compression**: Automatic image compression for photos
3. **Thumbnail Generation**: Automatic thumbnail generation for images and videos
4. **CDN Integration**: Support for CDN storage
5. **Batch Operations**: Support for bulk upload and delete operations
6. **File Versioning**: Support for file versioning and rollback
7. **Audit Logging**: Track all documentation changes

## Testing

You can test the API endpoints using tools like:
- Postman
- Insomnia
- cURL
- Thunder Client (VS Code extension)

## Support

For technical support or questions about the Event Documentation API, please contact the development team.
