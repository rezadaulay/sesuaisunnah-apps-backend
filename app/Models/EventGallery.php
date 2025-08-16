<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'photo_url',
        'type',
        'description',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the event for this gallery photo.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the full URL for the photo.
     */
    public function getFullPhotoUrlAttribute(): string
    {
        if (filter_var($this->photo_url, FILTER_VALIDATE_URL)) {
            return $this->photo_url;
        }

        return asset('storage/' . $this->photo_url);
    }

    /**
     * Get human readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
    }

    /**
     * Check if file is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return in_array($this->mime_type, ['video/mp4', 'video/mov', 'video/avi']);
    }

    /**
     * Check if file is a document.
     */
    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->mime_type, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }
}
