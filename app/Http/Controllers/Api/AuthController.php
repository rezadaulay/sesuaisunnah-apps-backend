<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\OtpCode;
use App\Models\User;
use App\Models\EventRegistration;
use App\Models\EbookInteraction;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP code to phone number via WhatsApp.
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

        // Use OTP service to send OTP via WhatsApp
        $result = $this->otpService->sendOtp($phone);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, $result['code'] ?? 500);
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

        // Use OTP service to verify OTP
        $verificationResult = $this->otpService->verifyOtp($phone, $otp);

        if (!$verificationResult['success']) {
            return response()->json($verificationResult, $verificationResult['code'] ?? 401);
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
     * Resend OTP code to phone number via WhatsApp.
     */
    public function resendOtp(Request $request): JsonResponse
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

        // Use OTP service to resend OTP via WhatsApp
        $result = $this->otpService->resendOtp($phone);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, $result['code'] ?? 500);
        }
    }

    /**
     * Check user status by phone number.
     */
    public function checkUserStatus(Request $request): JsonResponse
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

        // Use OTP service to check user status
        $result = $this->otpService->checkUserStatus($phone);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, $result['code'] ?? 500);
        }
    }

    /**
     * Register new user/member without password.
     * After successful registration, OTP will be sent via WhatsApp.
     */
    public function register(RegistrationRequest $request): JsonResponse
    {

        try {
            // Check if user already exists with same phone
            $existingUser = User::where('phone', $request->phone)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this phone number already exists.',
                ], 409);
            }

            // Check if user already exists with same email (if email provided)
            if ($request->email) {
                $existingUserWithEmail = User::where('email', $request->email)->first();
                if ($existingUserWithEmail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User with this email already exists.',
                        'errors' => [
                            'email' => ['Email sudah terdaftar.']
                        ]
                    ], 422);
                }
            }

            // Create new user without password
            $user = User::create([
                'name' => $request->name,
                'country_code' => $request->country_code,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->gender,
                'password' => Hash::make(Str::random(16)), // Generate random password for security
            ]);

            // Send OTP via WhatsApp for verification
            $otpResult = $this->otpService->sendOtp($request->phone);
            Log::info($otpResult);

            if ($otpResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! OTP has been sent to your WhatsApp for verification.',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'country_code' => $user->country_code,
                            'phone' => $user->phone,
                            'full_phone' => $user->full_phone,
                            'email' => $user->email,
                            'gender' => $user->gender,
                        ],
                        'otp_info' => [
                            'phone' => $user->full_phone,
                            'expires_in' => $otpResult['data']['expires_in'],
                            'expires_at' => $otpResult['data']['expires_at'],
                            'delivery_method' => $otpResult['data']['delivery_method'],
                        ],
                        'next_step' => 'Verify OTP using /api/auth/verify-otp endpoint to complete authentication.',
                    ],
                ], 201);
            } else {
                // If OTP sending fails, still create user but inform about OTP issue
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! However, there was an issue sending OTP. Please try requesting OTP again.',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'country_code' => $user->country_code,
                            'phone' => $user->phone,
                            'full_phone' => $user->full_phone,
                            'email' => $user->email,
                            'gender' => $user->gender,
                        ],
                        'otp_status' => 'failed',
                        'otp_error' => $otpResult['message'],
                        'next_step' => 'Try requesting OTP again using /api/auth/send-otp endpoint.',
                    ],
                ], 201);
            }

        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage(), [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
                'country_code' => $user->country_code,
                'phone' => $user->phone,
                'full_phone' => $user->full_phone,
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
