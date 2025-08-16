<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'donor_name',
        'donor_phone',
        'donor_email',
        'message',
        'amount',
        'payment_method',
        'status',
        'donation_date',
        'bank_name',
        'account_number',
        'account_name',
        'is_anonymous',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'donation_date' => 'date',
        'is_anonymous' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $dates = [
        'donation_date',
        'verified_at',
    ];

    /**
     * Get the user who verified this donation.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope for pending donations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for confirmed donations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for completed donations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for verified donations.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for anonymous donations.
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    /**
     * Get the total amount of donations.
     */
    public static function getTotalAmount()
    {
        return self::where('status', 'completed')->sum('amount');
    }

    /**
     * Get the total amount of donations for a specific period.
     */
    public static function getTotalAmountForPeriod($startDate, $endDate)
    {
        return self::where('status', 'completed')
            ->whereBetween('donation_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get the donor display name (anonymous or actual name).
     */
    public function getDonorDisplayNameAttribute()
    {
        return $this->is_anonymous ? 'Anonymous Donor' : $this->donor_name;
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }
}
