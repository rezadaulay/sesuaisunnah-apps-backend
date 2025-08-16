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
}
