<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['creator:id,name', 'registrations:id,event_id,user_id'])
            ->withCount('registrations');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('event_date', '>', now());
                    break;
                case 'today':
                    $query->whereDate('event_date', now());
                    break;
                case 'past':
                    $query->where('event_date', '<', now());
                    break;
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderBy('event_date', 'asc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): JsonResponse
    {
        $event->load([
            'creator:id,name',
            'registrations.user:id,name,phone,gender',
            'gallery'
        ]);
        $event->loadCount('registrations');

        return response()->json([
            'success' => true,
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Get upcoming events (next 30 days).
     */
    public function upcoming(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('event_date', '>', now())
            ->where('event_date', '<=', now()->addDays(30))
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventResource::collection($events),
        ]);
    }

    /**
     * Get featured events.
     */
    public function featured(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('event_date', '>=', now())
            ->orderBy('registrations_count', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventResource::collection($events),
        ]);
    }
}
