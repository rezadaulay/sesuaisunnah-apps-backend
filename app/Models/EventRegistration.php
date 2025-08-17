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
        'phone',
        'name',
        'gender',
        'email',
        'referral_source',
        'registered_at',
        'status',
        'occupation',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /**
     * Get the event that the user registered for.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who registered.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for registrations by referral source.
     */
    public function scopeByReferralSource($query, string $source)
    {
        return $query->where('referral_source', $source);
    }

    /**
     * Scope for registrations by gender.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope for registrations by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('registered_at', [$startDate, $endDate]);
    }

    /**
     * Get referral source statistics.
     */
    public static function getReferralSourceStats()
    {
        return self::selectRaw('referral_source, COUNT(*) as count')
            ->groupBy('referral_source')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->referral_source => $item->count];
            });
    }

    /**
     * Get gender distribution statistics.
     */
    public static function getGenderStats()
    {
        return self::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->gender => $item->count];
            });
    }

    /**
     * Get registration trends by date.
     */
    public static function getRegistrationTrends($days = 30)
    {
        return self::selectRaw('DATE(registered_at) as date, COUNT(*) as count')
            ->where('registered_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });
    }

    /**
     * Get top events by registration count.
     */
    public static function getTopEvents($limit = 10)
    {
        return self::selectRaw('event_id, COUNT(*) as registration_count')
            ->with('event:id,title')
            ->groupBy('event_id')
            ->orderBy('registration_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get referral source label.
     */
    public function getReferralSourceLabelAttribute(): string
    {
        return match($this->referral_source) {
            'website' => 'Website',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'whatsapp' => 'WhatsApp',
            'friend' => 'Teman/Keluarga',
            'email' => 'Email',
            'other' => 'Lainnya',
            default => ucfirst($this->referral_source),
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status ?? 'registered') {
            'registered' => 'success',
            'confirmed' => 'info',
            'cancelled' => 'danger',
            'attended' => 'success',
            'no_show' => 'warning',
            default => 'gray',
        };
    }
}
