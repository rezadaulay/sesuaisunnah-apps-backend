<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Models\EventRegistration;
use App\Models\EbookInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Send OTP code to phone number.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->phone;

        // Check if OTP was recently sent (rate limiting)
        if (OtpCode::hasValidOtp($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP already sent. Please wait before requesting another.',
            ], 429);
        }

        try {
            // Generate OTP code
            $otp = OtpCode::generateForPhone($phone);

            // TODO: Integrate with SMS service (Twilio, Vonage, etc.)
            // For now, we'll return the OTP in response for testing
            // In production, remove this and send via SMS

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'data' => [
                    'phone' => $phone,
                    'expires_in' => $otp->remaining_time,
                    // Remove this in production
                    'otp_code' => config('app.debug') ? $otp->code : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify OTP and authenticate user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->phone;
        $otp = $request->otp;

        // Verify OTP
        if (!OtpCode::verify($phone, $otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code',
            ], 401);
        }

        // Find or create user
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $request->name ?? 'User',
                'phone' => $phone,
                'email' => $request->email,
                'gender' => $request->gender,
                'password' => Hash::make(Str::random(16)), // Generate random password
            ]);
        }

        // Generate API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'gender' => $user->gender,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'gender' => $user->gender,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:male,female',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'gender']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'gender' => $user->gender,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Get user activity history.
     */
    public function activityHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get event registrations
        $eventRegistrations = EventRegistration::where('phone', $user->phone)
            ->with('event:id,title,start_date,end_date,location')
            ->latest('registered_at')
            ->get()
            ->map(function ($registration) {
                return [
                    'type' => 'event_registration',
                    'title' => $registration->event->title,
                    'date' => $registration->registered_at,
                    'details' => [
                        'event_date' => $registration->event->start_date,
                        'location' => $registration->event->location,
                        'status' => $registration->status ?? 'registered',
                    ],
                ];
            });

        // Get ebook interactions
        $ebookInteractions = EbookInteraction::where('user_phone', $user->phone)
            ->with('ebook:id,title,author')
            ->latest('interaction_date')
            ->get()
            ->map(function ($interaction) {
                return [
                    'type' => 'ebook_interaction',
                    'title' => $interaction->ebook->title,
                    'date' => $interaction->interaction_date,
                    'details' => [
                        'action' => $interaction->action,
                        'author' => $interaction->ebook->author,
                        'duration' => $interaction->duration ?? null,
                    ],
                ];
            });

        // Combine and sort all activities
        $activities = $eventRegistrations->concat($ebookInteractions)
            ->sortByDesc('date')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities,
                'total_activities' => $activities->count(),
            ],
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
