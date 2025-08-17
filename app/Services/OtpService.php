<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class OtpService
{
    protected $waMasbro;

    public function __construct(WAMasbro $waMasbro)
    {
        $this->waMasbro = $waMasbro;
    }

        /**
     * Send OTP code via WhatsApp using WAMasbro service
     */
    public function sendOtp(string $phone): array
    {
        Log::info('sendOtp');
        Log::info($phone);
        try {
            // Check if user exists first
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                // User not registered - return response for frontend to redirect to registration
                return [
                    'success' => false,
                    'message' => 'Phone number not registered. Please register first.',
                    'code' => 404,
                    'user_status' => 'not_registered',
                    'action_required' => 'redirect_to_registration',
                    'data' => [
                        'phone' => $phone,
                        'message' => 'This phone number is not registered in our system'
                    ]
                ];
            }

            // Check if OTP was recently sent (rate limiting)
            if (OtpCode::hasValidOtp($phone)) {
                return [
                    'success' => false,
                    'message' => 'OTP already sent. Please wait before requesting another.',
                    'code' => 429
                ];
            }

            // Check daily attempt limit
            if ($this->hasExceededDailyLimit($phone)) {
                return [
                    'success' => false,
                    'message' => 'Maximum OTP attempts reached for today. Please try again tomorrow.',
                    'code' => 429
                ];
            }

            // Generate OTP code
            $otp = OtpCode::generateForPhone($phone);

            // Check if WhatsApp OTP is enabled
            if (!config('otp.enable_whatsapp')) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp OTP is currently disabled.',
                    'code' => 503
                ];
            }

            // Format phone number for WhatsApp using user's WhatsApp phone format
            $formattedPhone = $user->whatsapp_phone;

            // Prepare WhatsApp message
            $message = $this->prepareOtpMessage($otp->code);

            // Send via WAMasbro
            $sent = $this->waMasbro->sendTextMessage($formattedPhone, $message);

            if ($sent) {
                // Log successful OTP send
                if (config('otp.log_activities')) {
                    Log::info("OTP sent successfully to {$phone} via WhatsApp for existing user");
                }

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully via WhatsApp for login',
                    'data' => [
                        'phone' => $phone,
                        'expires_in' => $otp->remaining_time,
                        'expires_at' => $otp->expires_at->toISOString(),
                        'delivery_method' => 'whatsapp',
                    ],
                    'user_status' => 'existing',
                    'next_step' => 'Verify OTP using /api/auth/verify-otp endpoint'
                ];
            } else {
                // If WhatsApp fails, mark OTP as used and return error
                $otp->update(['is_used' => true]);

                Log::error("Failed to send OTP via WhatsApp to {$phone}");

                return [
                    'success' => false,
                    'message' => 'Failed to send OTP via WhatsApp. Please try again.',
                    'code' => 500
                ];
            }

        } catch (Exception $e) {
            Log::error("Error sending OTP to {$phone}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'code' => 500
            ];
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $phone, string $code): array
    {
        try {
            if (OtpCode::verify($phone, $code)) {
                return [
                    'success' => true,
                    'message' => 'OTP verified successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid or expired OTP code',
                'code' => 401
            ];

        } catch (Exception $e) {
            Log::error("Error verifying OTP for {$phone}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'code' => 500
            ];
        }
    }

    /**
     * Check if phone has valid OTP
     */
    public function hasValidOtp(string $phone): bool
    {
        return OtpCode::hasValidOtp($phone);
    }

    /**
     * Get remaining time for OTP
     */
    public function getOtpRemainingTime(string $phone): ?int
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        return $otp ? $otp->remaining_time : null;
    }

    /**
     * Check if phone number has exceeded daily OTP attempt limit
     */
    protected function hasExceededDailyLimit(string $phone): bool
    {
        $maxAttempts = config('otp.max_daily_attempts', 10);
        $todayAttempts = OtpCode::where('phone', $phone)
            ->whereDate('created_at', today())
            ->count();

        return $todayAttempts >= $maxAttempts;
    }

    /**
     * Format phone number for WhatsApp (Indonesia format)
     */
    protected function formatPhoneForWhatsApp(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If starts with +62, remove the +
        if (substr($phone, 0, 3) === '+62') {
            $phone = substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Prepare OTP message for WhatsApp
     */
    protected function prepareOtpMessage(string $code): string
    {
        $template = config('otp.whatsapp_message_template');
        $expiration = config('otp.expiration_minutes', 10);

        return str_replace(
            ['{code}', '{expiration}'],
            [$code, $expiration],
            $template
        );
    }

    /**
     * Resend OTP (with additional rate limiting)
     */
    public function resendOtp(string $phone): array
    {
        // Check if we can resend (more strict rate limiting for resend)
        $lastOtp = OtpCode::where('phone', $phone)
            ->latest()
            ->first();

        $resendRateLimit = config('otp.resend_rate_limit_minutes', 2);

        if ($lastOtp && $lastOtp->created_at->addMinutes($resendRateLimit)->isFuture()) {
            return [
                'success' => false,
                'message' => "Please wait {$resendRateLimit} minutes before requesting another OTP",
                'code' => 429
            ];
        }

        return $this->sendOtp($phone);
    }

    /**
     * Check user status by phone number
     */
    public function checkUserStatus(string $phone): array
    {
        try {
            $user = User::where('phone', $phone)->first();

            if ($user) {
                return [
                    'success' => true,
                    'user_status' => 'existing',
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'gender' => $user->gender,
                        'registered_at' => $user->created_at->toISOString(),
                    ],
                    'message' => 'User already registered',
                    'next_step' => 'Request OTP for login using /api/auth/send-otp endpoint'
                ];
            } else {
                return [
                    'success' => true,
                    'user_status' => 'new',
                    'data' => [
                        'phone' => $phone,
                        'message' => 'Phone number not registered'
                    ],
                    'message' => 'Phone number not registered',
                    'next_step' => 'Register new account using /api/auth/register endpoint'
                ];
            }
        } catch (Exception $e) {
            Log::error("Error checking user status for {$phone}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to check user status. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'code' => 500
            ];
        }
    }
}
