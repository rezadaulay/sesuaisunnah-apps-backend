<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate a new OTP code for the given phone number.
     */
    public static function generateForPhone(string $phone): self
    {
        // Invalidate any existing unused OTP codes for this phone
        self::where('phone', $phone)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        // Generate new 6-digit OTP code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiration to 10 minutes from now
        $expiresAt = Carbon::now()->addMinutes(10);

        return self::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);
    }

    /**
     * Verify OTP code for the given phone number.
     */
    public static function verify(string $phone, string $code): bool
    {
        $otp = self::where('phone', $phone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if phone number has a valid unused OTP.
     */
    public static function hasValidOtp(string $phone): bool
    {
        return self::where('phone', $phone)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Get remaining time for OTP expiration in seconds.
     */
    public function getRemainingTimeAttribute(): int
    {
        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
