<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    /**
     * Register for an event.
     */
    public function store(EventRegistrationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if event exists and can accept registrations
            $event = Event::findOrFail($request->event_id);

            if (!$event->can_accept_registrations) {
                return response()->json([
                    'success' => false,
                    'message' => $event->registration_status_text,
                ], 400);
            }

            // Check if user already registered for this event
            $existingRegistration = EventRegistration::where('event_id', $request->event_id)
                ->where('phone', $request->phone)
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah terdaftar untuk event ini.',
                ], 400);
            }

            // Find or create user
            $user = User::firstOrCreate(
                ['phone' => $request->phone],
                [
                    'name' => $request->name,
                    'gender' => $request->gender,
                    'email' => $request->email,
                    'password' => bcrypt(Str::random(10)), // Temporary password
                ]
            );

            // Update user info if they exist but with different name/email
            if ($user->wasRecentlyCreated === false) {
                $user->update([
                    'name' => $request->name,
                    'gender' => $request->gender,
                    'email' => $request->email ?: $user->email,
                ]);
            }

            // Create event registration
            $registration = EventRegistration::create([
                'event_id' => $request->event_id,
                'user_id' => $user->id,
                'referral_source' => $request->referral_source,
                'registered_at' => now(),
            ]);

            // Increment participant count
            $event->incrementParticipants();

            // Assign member role if user doesn't have it
            if (!$user->hasRole('member')) {
                $user->assignRole('member');
            }

            DB::commit();

            // Log the registration
            Log::info('Event registration created', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'user_id' => $user->id,
                'user_phone' => $user->phone,
                'referral_source' => $request->referral_source,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil! Anda telah terdaftar untuk event "' . $event->title . '".',
                'data' => [
                    'registration_id' => $registration->id,
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_date' => $event->start_date->format('d/m/Y H:i'),
                        'end_date' => $event->end_date->format('d/m/Y H:i'),
                        'description' => $event->description,
                        'current_participants' => $event->current_participants,
                        'max_participants' => $event->max_participants,
                        'available_spots' => $event->available_spots,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'gender' => $user->gender,
                    ],
                    'registration_date' => $registration->registered_at->format('d/m/Y H:i'),
                    'referral_source' => $registration->referral_source,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event registration failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Get user's event registrations.
     */
    public function userRegistrations(string $phone): JsonResponse
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $registrations = EventRegistration::with(['event:id,title,start_date,end_date,description,featured_image,status,current_participants,max_participants'])
            ->where('user_id', $user->id)
            ->orderBy('registered_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                ],
                'registrations' => $registrations->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'event' => [
                            'id' => $registration->event->id,
                            'title' => $registration->event->title,
                            'start_date' => $registration->event->start_date->format('d/m/Y H:i'),
                            'end_date' => $registration->event->end_date->format('d/m/Y H:i'),
                            'description' => $registration->event->description,
                            'featured_image' => $registration->event->featured_image,
                            'status' => $registration->event->status,
                            'current_participants' => $registration->event->current_participants,
                            'max_participants' => $registration->event->max_participants,
                        ],
                        'registered_at' => $registration->registered_at->format('d/m/Y H:i'),
                        'referral_source' => $registration->referral_source,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Cancel event registration.
     */
    public function cancel(string $phone, int $eventId): JsonResponse
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $registration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        // Check if event is not today or past
        $event = Event::find($eventId);
        if ($event->start_date <= now()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan pendaftaran untuk event yang sudah berlangsung atau berakhir.',
            ], 400);
        }

        // Decrement participant count
        $event->decrementParticipants();
        
        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil dibatalkan.',
        ]);
    }
}
