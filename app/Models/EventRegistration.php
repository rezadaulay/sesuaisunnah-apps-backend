<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'referral_source',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /**
     * Get the event for this registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user for this registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for registrations from specific referral source.
     */
    public function scopeFromReferral($query, $source)
    {
        return $query->where('referral_source', $source);
    }

    /**
     * Scope for registrations on specific date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('registered_at', $date);
    }
}
