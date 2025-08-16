<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'featured_image',
        'event_date',
        'documentation_desc',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    /**
     * Get the user that created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the registrations for the event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the gallery photos for the event.
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(EventGallery::class);
    }

    /**
     * Get the participants count for the event.
     */
    public function getParticipantsCountAttribute(): int
    {
        return $this->registrations()->count();
    }

    /**
     * Check if event is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->event_date->isFuture();
    }

    /**
     * Check if event is past.
     */
    public function getIsPastAttribute(): bool
    {
        return $this->event_date->isPast();
    }

    /**
     * Check if event is today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->event_date->isToday();
    }
}
