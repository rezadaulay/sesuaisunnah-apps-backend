# Event Documentation API - Usage Examples

## 🚀 Quick Start

This document provides practical examples of how to use the Event Documentation API endpoints.

## 📤 Upload Event Documentation

### Upload Single Photo
```bash
curl -X POST "http://localhost:8000/api/events/1/documentation/upload" \
  -F "files[]=@photo1.jpg" \
  -F "type=photo" \
  -F "description=Event opening ceremony photo"
```

### Upload Multiple Photos
```bash
curl -X POST "http://localhost:8000/api/events/1/documentation/upload" \
  -F "files[]=@photo1.jpg" \
  -F "files[]=@photo2.jpg" \
  -F "files[]=@photo3.jpg" \
  -F "type=photo" \
  -F "description=Event photo collection"
```

### Upload Video
```bash
curl -X POST "http://localhost:8000/api/events/1/documentation/upload" \
  -F "files[]=@event_video.mp4" \
  -F "type=video" \
  -F "description=Event highlights video"
```

### Upload Document
```bash
curl -X POST "http://localhost:8000/api/events/1/documentation/upload" \
  -F "files[]=@event_report.pdf" \
  -F "type=document" \
  -F "description=Event summary report"
```

## 📥 Retrieve Event Documentation

### Get All Documentation for an Event
```bash
curl "http://localhost:8000/api/events/1/documentation"
```

### Get Only Photos
```bash
curl "http://localhost:8000/api/events/1/documentation?type=photo"
```

### Get Only Videos
```bash
curl "http://localhost:8000/api/events/1/documentation?type=video"
```

### Get Only Documents
```bash
curl "http://localhost:8000/api/events/1/documentation?type=document"
```

## ✏️ Update Documentation

### Update Description
```bash
curl -X PUT "http://localhost:8000/api/events/1/documentation/description" \
  -H "Content-Type: application/json" \
  -d '{
    "documentation_id": 1,
    "description": "Updated description for the photo"
  }'
```

## 🗑️ Delete Documentation

### Delete Specific Documentation
```bash
curl -X DELETE "http://localhost:8000/api/events/1/documentation" \
  -H "Content-Type: application/json" \
  -d '{
    "documentation_id": 1
  }'
```

## 📊 Get Documentation Summary

### Get Events with Documentation
```bash
curl "http://localhost:8000/api/events/with-documentation"
```

### Get Events with Specific Documentation Type
```bash
curl "http://localhost:8000/api/events/with-documentation?doc_type=photo"
```

### Paginated Results
```bash
curl "http://localhost:8000/api/events/with-documentation?per_page=5"
```

## 🔍 Get Event Information

### Get Event Details
```bash
curl "http://localhost:8000/api/events/1"
```

### Get Upcoming Events
```bash
curl "http://localhost:8000/api/events/upcoming"
```

### Get Featured Events
```bash
curl "http://localhost:8000/api/events/featured"
```

## 📱 JavaScript/Fetch Examples

### Upload Documentation
```javascript
const formData = new FormData();
formData.append('files[]', fileInput.files[0]);
formData.append('type', 'photo');
formData.append('description', 'Event photo');

fetch('/api/events/1/documentation/upload', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log('Upload successful:', data));
```

### Get Documentation
```javascript
fetch('/api/events/1/documentation?type=photo')
.then(response => response.json())
.then(data => {
  console.log('Documentation:', data.data.documentation);
  console.log('Summary:', data.data.summary);
});
```

### Update Description
```javascript
fetch('/api/events/1/documentation/description', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    documentation_id: 1,
    description: 'New description'
  })
})
.then(response => response.json())
.then(data => console.log('Update successful:', data));
```

## 🐍 Python Examples

### Upload Documentation
```python
import requests

files = {'files[]': open('photo.jpg', 'rb')}
data = {
    'type': 'photo',
    'description': 'Event photo'
}

response = requests.post(
    'http://localhost:8000/api/events/1/documentation/upload',
    files=files,
    data=data
)

print(response.json())
```

### Get Documentation
```python
import requests

response = requests.get(
    'http://localhost:8000/api/events/1/documentation',
    params={'type': 'photo'}
)

data = response.json()
print(f"Found {data['data']['summary']['photos_count']} photos")
```

## 📋 Response Examples

### Successful Upload Response
```json
{
  "success": true,
  "message": "1 file(s) uploaded successfully",
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "photo_url": "events/1/documentation/1234567890_photo.jpg",
      "type": "photo",
      "description": "Event opening ceremony photo",
      "file_size": 2048576,
      "mime_type": "image/jpeg",
      "created_at": "2025-08-16T15:44:38.000000Z",
      "updated_at": "2025-08-16T15:44:38.000000Z"
    }
  ]
}
```

### Documentation List Response
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
        "file_url": "http://localhost:8000/storage/events/1/documentation/photo.jpg",
        "description": "Event opening ceremony photo",
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

## ⚠️ Error Handling

### Validation Error Response
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

### Not Found Error Response
```json
{
  "success": false,
  "message": "Documentation not found for this event"
}
```

## 🔒 File Requirements

### Supported File Types
- **Photos**: JPG, JPEG, PNG, GIF
- **Videos**: MP4, MOV, AVI
- **Documents**: PDF, DOC, DOCX

### File Size Limits
- **Maximum size**: 10MB per file
- **Recommended**: Under 5MB for optimal performance

### File Naming
- Use descriptive names for better organization
- Avoid special characters in filenames
- Include date or event identifier in filename

## 🧪 Testing

### Test with Sample Files
1. Create a test event first
2. Upload sample photos/videos/documents
3. Test retrieval and filtering
4. Test update and delete operations
5. Verify file cleanup after deletion

### Common Test Scenarios
- Upload multiple files of different types
- Test file size validation
- Test file type validation
- Test error handling for invalid requests
- Test pagination for large datasets

## 📚 Additional Resources

- **API Documentation**: `EVENT_DOCUMENTATION_API.md`
- **Phase Summary**: `PHASE_2_3_SUMMARY.md`
- **Test Files**: `tests/Feature/EventDocumentationTest.php`
- **Model Files**: `app/Models/EventGallery.php`
- **Controller**: `app/Http/Controllers/Api/EventController.php`
