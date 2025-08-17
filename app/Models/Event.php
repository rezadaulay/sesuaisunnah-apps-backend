<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'featured_image',
        'event_date',
        'start_date',
        'end_date',
        'location',
        'documentation_desc',
        'created_by',
        'registration_opens_at',
        'registration_closes_at',
        'max_participants',
        'current_participants',
        'status',
        'event_closed_at',
        'requires_registration',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_opens_at' => 'datetime',
        'registration_closes_at' => 'datetime',
        'event_closed_at' => 'datetime',
        'requires_registration' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-sync status with registration dates
        static::saving(function ($event) {
            if ($event->requires_registration) {
                $now = now();

                // Auto-update status based on registration dates
                if ($event->registration_opens_at && $event->registration_closes_at) {
                    if ($now < $event->registration_opens_at) {
                        $event->status = 'published';
                    } elseif ($now >= $event->registration_opens_at && $now <= $event->registration_closes_at) {
                        $event->status = 'registration_open';
                    } elseif ($now > $event->registration_closes_at) {
                        $event->status = 'registration_closed';
                    }
                }
            }
        });
    }

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

    /**
     * Check if registration is open.
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        if ($this->status === 'event_closed') {
            return false;
        }

        // If event doesn't require registration, always return false
        if (!$this->requires_registration) {
            return false;
        }

        $now = now();

        // Check if registration period is set
        if ($this->registration_opens_at && $this->registration_closes_at) {
            return $now->between($this->registration_opens_at, $this->registration_closes_at);
        }

        // If no specific registration period, check status
        return $this->status === 'registration_open';
    }

    /**
     * Check if event can accept registrations.
     */
    public function getCanAcceptRegistrationsAttribute(): bool
    {
        if (!$this->requires_registration) {
            return false;
        }

        if (!$this->is_registration_open) {
            return false;
        }

        // Check quota if set
        if ($this->max_participants && $this->current_participants >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Check if event requires registration.
     */
    public function getRequiresRegistrationAttribute(): bool
    {
        return $this->attributes['requires_registration'] ?? true;
    }

    /**
     * Check if event is closed.
     */
    public function getIsEventClosedAttribute(): bool
    {
        return $this->status === 'event_closed';
    }

    /**
     * Check if gallery can be updated after event closure.
     */
    public function getCanUpdateGalleryAttribute(): bool
    {
        return $this->is_event_closed;
    }

    /**
     * Get available spots for registration.
     */
    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->max_participants) {
            return null; // Unlimited
        }

        return max(0, $this->max_participants - $this->current_participants);
    }

    /**
     * Get registration status text.
     */
    public function getRegistrationStatusTextAttribute(): string
    {
        if ($this->is_event_closed) {
            return 'Event Closed';
        }

        if (!$this->is_registration_open) {
            if ($this->registration_opens_at && now() < $this->registration_opens_at) {
                return 'Registration Not Yet Open';
            }
            if ($this->registration_closes_at && now() > $this->registration_closes_at) {
                return 'Registration Closed';
            }
            return 'Registration Closed';
        }

        if ($this->max_participants) {
            if ($this->available_spots === 0) {
                return 'Fully Booked';
            }
            return "Available: {$this->available_spots} spots";
        }

        return 'Open for Registration';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'published' => 'info',
            'registration_open' => 'success',
            'registration_closed' => 'warning',
            'event_closed' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Scope for events with open registration.
     */
    public function scopeRegistrationOpen($query)
    {
        return $query->where('status', 'registration_open')
            ->where(function ($q) {
                $q->whereNull('max_participants')
                  ->orWhere('current_participants', '<', 'max_participants');
            });
    }

    /**
     * Scope for events that can accept registrations.
     */
    public function scopeCanAcceptRegistrations($query)
    {
        return $query->where('status', '!=', 'event_closed')
            ->where(function ($q) {
                $q->whereNull('max_participants')
                  ->orWhere('current_participants', '<', 'max_participants');
            });
    }

    /**
     * Scope for closed events.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'event_closed');
    }

    /**
     * Increment participant count.
     */
    public function incrementParticipants(): void
    {
        $this->increment('current_participants');
    }

    /**
     * Decrement participant count.
     */
    public function decrementParticipants(): void
    {
        $this->decrement('current_participants');
    }

    /**
     * Close event.
     */
    public function closeEvent(): void
    {
        $this->update([
            'status' => 'event_closed',
            'event_closed_at' => now(),
        ]);
    }
}
