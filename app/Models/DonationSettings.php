<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_name',
        'swift_code',
        'branch_name',
        'donation_note',
        'bank_transfer_note',
        'minimum_donation',
        'is_active',
        'show_donor_list',
        'contact_person',
        'contact_email',
    ];

    protected $casts = [
        'minimum_donation' => 'decimal:2',
        'is_active' => 'boolean',
        'show_donor_list' => 'boolean',
    ];

    /**
     * Get the formatted minimum donation amount.
     */
    public function getFormattedMinimumDonationAttribute(): string
    {
        return 'Rp ' . number_format($this->minimum_donation, 0, ',', '.');
    }

    /**
     * Check if donation is active.
     */
    public function getIsDonationActiveAttribute(): bool
    {
        return $this->is_active;
    }

    /**
     * Get active donation settings.
     */
    public static function getActiveSettings()
    {
        return static::where('is_active', true)->first();
    }
}
